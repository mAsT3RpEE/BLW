<?php
/**
 * Element.php | Nov 29, 2013
 *
 * Copyright (c) mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 *
 * @filesource
 * @copyright mAsT3RpEE's Zone
 * @license MIT
 */

/**
 * @package BLW\Core
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

use Symfony\Component\CssSelector\CssSelector;

require_once __DIR__ . '/DOMElement.php';

/**
 * Core BLW DOM Element object.
 *
 * <h3>About</h3>
 *
 * <p>This is the core BLW DOMobject. All other Objects must extent this class
 * or implement it's interface.</p>
 *
 * <hr>
 * @package BLW\Core
 * @api BLW
 * @version 1.0.0
 * @since 0.1.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @link http://mast3rpee.tk/projects/BLW/ mAsT3RpEE's Zone > Projects > BLW
 */
abstract class Element extends \BLW\Type\Iterator implements \BLW\Interfaces\Element
{
    /**
     * @var array $DefaultOptions Default options used by class if not set in constructor.
     * @see \BLW\Type\Object::__construct() Object::__construct()
     */
    public static $DefaultOptions = array(
            'HTML'                => '<span></span>'
            ,'DocumentVersion'    => '1.0'
    );

    /**
     * @property \DOMDocument $Document Pointer to current element document if created from string.
     * @link http://php.net/manual/en/class.domdocument.php
     */

    /**
     * Overloads parent function
     * @see \BLW\Type\Object::ValidateOptions() Object::ValidateOptions()
     * @param mixed $Options Options to validate
     * @return bool Return <code>true</code> if options are valid <code>false</code> otherwise.
     */
    public static function ValidateOptions($Options)
    {
        return is_array($Options) || $Options instanceof \BLW\Interfaces\Object || $Options instanceof \DOMNode || $Options instanceof \DOMNodeList;
    }

    /**
     * Builds Options for current object.
     * @see \BLW\Type\Object::__construct() Objecct::__contruct()
     * @param mixed $Options Options to build
     * @return \stdClass Returns built options.
     */
    public static function BuildOptions($Options)
    {
        if($Options instanceof \DOMNode) {
            $return          = (object)(static::$DefaultOptions);
            $return->DOMNode = $Options;

            unset($Options->HTML);

            return $return;
        }

        elseif($Options instanceof \DOMNodeList || is_array($Options)? end($Options) instanceof \DOMNode : false) {
            $return              = (object)(static::$DefaultOptions);
            $return->DOMNodeList = $Options;

            unset($Options->HTML);

            return $return;
        }

        elseif(is_array($Options)) {
            return (object)(array_replace(static::$DefaultOptions, $Options));
        }

        return new \stdClass;
    }

    /**
     * Returns options used by class.
     * @internal Can be overloaded to add more options, etc
     * @return \stdClass Returns Options used by the object.
     */
    public function GetOptions()
    {
        $Options = parent::GetOptions();

        // Get Node HTML
        if (!\SplDoublyLinkedList::isEmpty()) {
            if(($Node = \SplDoublyLinkedList::bottom()) instanceof \BLW\Type\DOMElement) {
                $Options->HTML = $Node->outerHTML();
            }
        }

        return $Options;
    }

    /**
     * Hook that is called when a new instance is created.
     * @return \BLW\Interfaces\Object $this
     */
    public static function doCreate()
    {
        parent::doCreate();

        if(isset(\BLW::$Self->Options->HTML)) {
            \BLW::$Self->LoadHTML(\BLW::$Self->Options->HTML);
            unset(\BLW::$Self->Options->HTML);
        }

        elseif(isset(\BLW::$Self->Options->DOMNode)) {
            \BLW::$Self->Document = \BLW::$Self->Options->DOMNode->ownerDocument;
            \BLW::$Self->AddNode(\BLW::$Self->Options->DOMNode);
            unset(\BLW::$Self->Options->DOMNode);
        }

        elseif(isset(\BLW::$Self->Options->DOMNodeList)) {

            foreach (\BLW::$Self->Options->DOMNodeList as $Node) {
                \BLW::$Self->Document = $Node->ownerDocument;
                \BLW::$Self->AddNode($Node);
            }

            unset(\BLW::$Self->Options->DOMNodeList);
        }

        return \BLW::$Self;
    }

    /**
     * Hook that is called just before an object is serialized.
     * @return \BLW\Interfaces\Object $this
     */
    public function doSerialize()
    {
        parent::doSerialize();

        // Save Document
        if ($this->Document instanceof \DOMDocument) {

            foreach ($this as $k => $Node) if ($Node instanceof \DOMNode) {

                if ($Node->ownerDocument != $this->Document) {
                    $Node = $this->Document->importNode($Node, true);
                }

                if ($Node) {
                    \SplDoublyLinkedList::offsetSet($k, $Node->getNodePath());
                }
            }

            $this->Document = $this->Document->saveHTML();
        }

        return $this;
    }

    /**
     * Hook that is called just after an object is unserialized.
     * @return \BLW\Interfaces\Object $this
     */
    public function doUnSerialize()
    {
        parent::doUnSerialize();

        // Load Document
        if (is_string($this->Document)) {
            $HTML = $this->Document;
            $this->Document()->loadHTML($HTML);
            $XPath = new \DOMXPath($this->Document());

            foreach ($this as $k => $Node) if (is_string($Node)) {
                $List = $XPath->query($Node);

                if ($List->length) {
                    \SplDoublyLinkedList::offsetSet($k, $List->item(0));
                }
            }
        }

        unset($this->Options->HTML);
        return $this;
    }

    /**
     * Returns the current elements document or creates one if it doesnt exist.
     * @api BLW
     * @since 1.0.0
     * @return \DOMDocument Current Object's <code>DOMDocument</code>.
     */
    public function & Document()
    {
        if(!$this->Document instanceof \DOMDocument) {
            if(!isset($this->Options->DocumentVersion)) {
                throw new \BLW\Model\InvalidClassException($this->_Status, '%header% Options->DocumentVersion does not exits.');
                return NULL;
            }

            $this->Document = new \DOMDocument($this->Options->DocumentVersion, 'UTF-8');
            $this->Document->registerNodeClass('DOMElement', '\\BLW\\Type\\DOMElement');
        }

        return $this->Document;
    }

    /**
     * Converts HTML string into DOMNodes and ataches them to the object.
     * @api BLW
     * @since 1.0.0
     * @param string $HTML HTML string to load.
     * @return \BLW\Interfaces\Element $this
     */
    public function LoadHTML($HTML)
    {
        // Empty current Element
        for($i=0,$k=true;$i<count($this);$i++,$k=true) {

            foreach ($this as $i => $o) {
                if ($o instanceof \DOMNode || $o instanceof \BLW\Interfaces\Element) {
                    unset($this[$i]);
                    $k = false;
                    break;
                }
            }

            if($k) break;
        }

        unset($this->Document);

        // Parse HTML
        $Nodes = \BLW\Type\DOMElement::ParseHTML($HTML);

        // Add Nodes
        if($Nodes->length > 0) {
            $this->Document = $Nodes->item(0)->ownerDocument;

            foreach($Nodes as $Node) {
                $this->AddNode($Node);
            }
        }

        return $this;
    }

    /**
     * Loads Nodes from a DOMDocument.
     * @api BLW
     * @since 1.0.0
     * @param \DOMDocument $Document Document to Add to current Object.
     * @param string $isDocument Wheather to load the Entire document or just its body.
     * @return \BLW\Interfaces\Element $this
     */
    public function AddDocument(\DOMDocument $Document, $isDocument = false)
    {
        if(!$isDocument) {
            foreach($Document->documentElement->lastChild->childNodes as $Node) {
                $this->AddNode($Node);
            }
        }

        else {
            $this->AddNode($Document->documentElement);
        }

        return $this;
    }

    /**
     * Adds a DOMNode to the current object.
     * @api BLW
     * @since 1.0.0
     * @param \DOMNode $Node Node to Add to Object.
     * @return \BLW\Interfaces\Element $this
     */
    final public function AddNode(\DOMNode $Node)
    {
        if (!$Node instanceof \DOMDocument) {

            if($Node->ownerDocument == $this->Document) {
                \SplDoublyLinkedList::push($Node);
            }

            else {
                throw new \BLW\Model\InvalidArgumentException(0);
            }
        }

        else {
            $this->AddDocument($Node, true);
        }

        return $this;
    }

    /**
     * Returns HTML of current element.
     * @api BLW
     * @since 1.0.1
     * @note Raises <code>E_USER_WARNING</code> when current node list is empty.
     * @return string The node html
     */
    public function GetHTML()
    {
        if (\SplDoublyLinkedList::isEmpty()) {
            trigger_error(sprintf('%s::GetHTML(): Current object is empty.', get_class($this)), E_USER_WARNING);
            return '';
        }

        $HTML = '';
        $i = 0;

        if (!isset($this->Document)) {
            $this->Document();
        }

        foreach($this as $k => $Node) {

            if($Node instanceof \BLW\Type\DOMElement) {
                if($k == 0 || $Node->ownerDocument != $this->Document) {
                    $HTML .= $Node->outerHTML();
                }
            }

            elseif($Node instanceof \BLW\Interfaces\Element) {
                if($Node->count() && $Node->Document() !== $this->Document)
                    $HTML .= $Node->GetHTML();
            }
        }

        return rtrim($HTML);
    }

    /**
     * Echos element. (Needed for chaining)
     * @api BLW
     * @since 1.0.1
     * @param bool $isDocument Whether object is a document element.
     * @return \BLW\Interfaces\Element $this
     */
    public function PrintHTML()
    {
        if(!\SplDoublyLinkedList::isEmpty()) {
            print $this->GetHTML();
        }

        return $this;
    }

    /**
     * Set / Get Current elements tag.
     * @note Raises <code>E_USER_ERROR</code> if default node does not exist.
     * @param string $Tag New tag name. (<code>[A-Za-z][\w_-]*</code>)
     * @return string | \BLW\Interfaces\Element Returns current tagName string or $this
     */
    public function tag($Tag = NULL)
    {
        if(!\SplDoublyLinkedList::isEmpty()) {
            if(($Node = \SplDoublyLinkedList::bottom()) instanceof \BLW\Type\DOMElement) {

                if(is_null($Tag)) {
                    return $Node->tagName;
                }

                elseif(($NewNode = $Node->tag($Tag)) instanceof \DOMElement) {
                    \SplDoublyLinkedList::offsetSet(0, $NewNode);
                    return $this;
                }

                else {
                    throw new \Exception('Unable to generate new node.');
                    return $this;
                }
            }
        }

        trigger_error(sprintf('%s::tag(): Current Element has no default node.', get_class($this)), E_USER_ERROR);

        if(is_null($Tag)) {
            return '';
        }

        return $this;
    }

    /**
     * Returns nodes that meet xpath query.
     *
     * <h4>Note:</h4>
     *
     * <p>Only allow for relative searches.</p>
     *
     * <hr>
     * @api BLW
     * @since 1.0.1
     * @internal Based on Symphony Project DOM Crawler.
     * @param string $Query A CSS selector.
     * @return \DOMNodeList List of matched nodes.
     */
    public function filterXPath($Query)
    {
        if(!\SplDoublyLinkedList::isEmpty()) {
            $Document = new \DOMDocument('1.0', 'UTF-8');

            $Document->loadHTML($this->GetHTML());

            $XPath = new \DOMXPath($Document);
            $List  = $XPath->evaluate($Query);

            unset($XPath);

            return $List;
        }

        return new \DOMNodeList;
    }

    /**
     * Filters the list of nodes with a CSS selector.
     * @api BLW
     * @since 1.0.0
     * @link http://symfony.com/doc/current/components/css_selector.html Symfony > CssSelector
     * @param string $Selector A CSS selector
     * @return \DOMNodeList List of matched nodes.
     */
    public function filter($Selector)
    {
        return $this->filterXPath(CssSelector::toXPath($Selector));
    }

    /**
     * Filters the list of nodes with a CSS selector.
     * @api BLW
     * @since 1.0.0
     * @see \BLW\Type\Element::filter()
     * @see \BLW\Type\Element::filterXPath()
     * @param string $Selector A CSS / XPath selector
     * @param bool $isCSSSelector Whether <code>$Selector</code> is a CSS selector or XPAth.
     * @return \DOMNodeList List of matched nodes.
     */
    public function __invoke($Selector, $isCSSSelector = true)
    {
        if ($isCSSSelector) {
            return $this->filterXPath(CssSelector::toXPath($Selector));
        }

        return $this->filterXPath($Selector);
    }

    /**
     * All objects must have a string representation.
     * @return string String value of object.
     */
    public function __toString()
    {
        $String = \SplDoublyLinkedList::isEmpty()
            ? ''
            : $this->GetHTML()
        ;

	    foreach ($this->_Decorators as $Decorator) {
            $String = $Decorator->DecorateToString($String, $this);
        }

        return $String;
    }
}

return true;