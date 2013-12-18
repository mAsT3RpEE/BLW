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
namespace BLW; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

use Symfony\Component\CssSelector\CssSelector;

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
class Element extends \BLW\Object implements \BLW\ElementInterface
{
    /**
     * @var array $DefaultOptions Default options used by class if not set in constructor.
     * @api BLW
     * @since 0.1.0
     * @see \BLW\Object::__construct() Object::__construct()
     */
    public static $DefaultOptions = array(
        'HTML'                => '<span></span>'
        ,'DocumentVersion'    => '1.0'
    );
    
    /**
     * @var \DOMDocument $Document Pointer to current element document if created from string.
     * @link http://php.net/manual/en/class.domdocument.php
     */
    
    /**
     * Overloads parent function
     * @see \BLW\Object::ValidateOptions() Object::ValidateOptions()
     * @param mixed $Options Options to validate
     * @return bool Return <code>true</code> if options are valid <code>false</code> otherwise.
     */
    public static function ValidateOptions($Options)
    {
        return is_array($Options) || $Options instanceof \BLW\ObjectInterface || $Options instanceof \DOMNode || $Options instanceof \DOMNodeList;
    }
    
    /**
     * Builds Options for current object.
     * @see \BLW\Object::__construct() Objecct::__contruct()
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
     * Initializes a child class for subsequent use.
     * @param array $Options Initialization options. (Automatically adds blw_cfg())
     * @return array Returns Options used / generated during init.
     */
    public static function initChild(array $Data = array())
    {
        // Initialize self
        if(get_called_class() == __CLASS__) {
        
            if(!self::$Initialized || isset($Data['hard_init'])) {
        
                $ParentOptions        = parent::init();
                self::$DefaultOptions = array_replace($ParentOptions, self::$DefaultOptions, $Data);
                self::$Initialized    = true;
        
                unset(self::$DefaultOptions['hard_init']);
        
                self::$base = self::create();
                self::$self = &self::$base;
            }
            
            // Return Options
            return self::$DefaultOptions;
        }
        
        else {
            // Initialize children
            if(!static::$Initialized || isset($Data['hard_init'])) {
                static::$DefaultOptions = array_replace(self::$DefaultOptions, static::$DefaultOptions, $Data);
                static::$Initialized    = true;
                
                unset(static::$DefaultOptions['hard_init']);
            }
        }
        
        return static::$DefaultOptions;
    }
    
    /**
     * Hook that is called when a new instance is created.
     * @note Format is <code>mixed function (\BLW\ObjectInterface $o)</code>.
     * @param \Closure $Function Function to call after object has been created.
     * @return \BLW\Object Current object
     */
    public static function onCreate(\Closure $Function = NULL)
    {
        if(is_null($Function)) {
            
            if(isset(Object::$self->Options->HTML)) {
                Object::$self->LoadHTML(Object::$self->Options->HTML);
                unset(Object::$self->Options->HTML);
            }
            
            elseif(isset(Object::$self->Options->DOMNode)) {
                Object::$self->AddNode(Object::$self->Options->DOMNode);
                unset(Object::$self->Options->DOMNode);
            }
            
            elseif(isset(Object::$self->Options->DOMNodeList)) {
                foreach (Object::$self->Options->DOMNodeList as $Node) {
                    Object::$self->AddNode($Node);
                }
                unset(Object::$self->Options->DOMNode);
            }
            
            return parent::onCreate();
        }
        
        return parent::onCreate($Function);
    }
    
    /**
     * Returns options used by class.
     * @internal Can be overloaded to add more options, etc
     * @return \stdClass Returns Options used by the object.
     */
    public function GetOptions()
    {
        if($this->count()) {
            $Node = \SplDoublyLinkedList::offsetGet(0);
            
            if($Node instanceof \DOMNode) {
                $Options = parent::GetOptions();
                
                if (version_compare(PHP_VERSION, '5.3.6', '>=')) {
                    $Options->HTML = $Node->ownerDocument->saveHTML($Node);
                }
                
                else {
                    $document = new \DOMDocument('1.0', 'UTF-8');
                    $document->appendChild($document->importNode($Node, true));
                    $Options->HTML = rtrim(preg_replace(array('/^.*<body[^>]*>/i', '/<\/body[^>]*>.*$/i'), '', $document->saveHTML()));
                    unset($document);
                }
                
                return $Options;
            }
        }
        
        return $this->Options;
    }
    
    /**
     * Hook that is called just before an object is serialized.
     * @note Format is <code>mixed function (\BLW\ObjectInterface $o)</code>.
     * @param \Closure $Function Function to call before object is serialized.
     * @return \BLW\Object $this
     */
    public function onSerialize(\Closure $Function = NULL)
    {
        if(is_null($Function)) {
            
            // Save Document
            if ($this->Document instanceof \DOMDocument) {
                
                foreach ($this as $k => $Node) if ($Node instanceof \DOMNode) {
                    if ($Node->ownerDocument == $this->Document) {
                        \SplDoublyLinkedList::offsetSet($k, $Node->getNodePath());
                    }
                }
                
                $this->Document = $this->Document->saveHTML();
            }
            
            return parent::onSerialize();
        }
    
        return parent::onSerialize($Function);
    }
    
    /**
     * Hook that is called just after an object is unserialized.
     * @note Format is <code>mixed function (\BLW\ObjectInterface $o)</code>.
     * @param \Closure $Function Function to call after Object has been unserialized.
     * @return \BLW\Object $this
     */
    public function onUnSerialize(\Closure $Function = NULL)
    {
        if(is_null($Function)) {
            
            // Load Document
            if (is_string($this->Document)) {
                $HTML = $this->Document;
                $this->Document()->loadHTML($HTML);
                $XPath = new \DOMXPath($this->Document());
                
                foreach ($this as $k => $Node) if (is_string($Node)) {
                    \SplDoublyLinkedList::offsetSet($k, $XPath->query($Node)->item(0));
                }
            }
            
            return parent::onSerialize();
        }
    
        return parent::onSerialize($Function);
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
                throw new InvalidClassException('%header% Options->DocumentVersion does not exits.');
                return NULL;
            }
            
            $this->Document = new \DOMDocument($this->Options->DocumentVersion, 'UTF-8');
        }
        
        return $this->Document;
    }
    
    /**
     * Converts HTML string into DOMNodes and ataches them to the object.
     * @api BLW
     * @since 1.0.0
     * @param string $HTML HTML string to load.
     * @return \BLW\Element $this
     */
    public function LoadHTML($HTML)
    {
        // Validate HTML
        if(!is_string($HTML) || empty($HTML)) {
            throw new \BLW\InvalidArgumentException(0);
            return $this;
        }
        
        // Empty current Element
        for($i=0,$k=true;$i<count($this);$i++,$k=true) {
            
            foreach ($this as $i => $o) {
                if ($o instanceof \DOMNode || $o instanceof \BLW\ElementInterface) {
                    unset($this[$i]);
                    $k = false;
                    break;
                }
            }
            
            if($k) break;
        }
        
        $this->Document = NULL;
        
        // Disable errors
        $current         = libxml_use_internal_errors(true);
        $disableEntities = libxml_disable_entity_loader(true);
        
        // Convert HTML
        if (function_exists('mb_convert_encoding') && in_array('UTF-8', mb_list_encodings())) {
            $HTML = mb_convert_encoding($HTML, 'HTML-ENTITIES', 'UTF-8');
        }
        
        // Load HTML
        $this->Document()->loadHTML($HTML);
        
        // Enable errors
        libxml_use_internal_errors($current);
        libxml_disable_entity_loader($disableEntities);
        
        // Add DOMElements
        $isDocument = preg_match('/<html/i', $HTML) > 0;
        
        $this->AddDocument($this->Document, $isDocument);
    }
    
    /**
     * Loads Nodes from a DOMDocument.
     * @api BLW
     * @since 1.0.0
     * @param \DOMDocument $Document Document to Add to current Object.
     * @param string $isDocument Wheather to load the Entire document or just its body.
     * @return \BLW\Element $this
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
     * @return \BLW\Element $this
     */
    final public function AddNode(\DOMNode $Node)
    {
        if (!$Node instanceof \DOMDocument) {
            
            if($Node->ownerDocument == $this->Document) {
                \SplDoublyLinkedList::push($Node);
            }
            
            else {
                throw new \BLW\InvalidArgumentException(0);
            }
        }
        
        else {
            $this->AddDocument($Node);
        }
        
        return $this;
    }
    
    /**
     * Returns HTML of current element.    
     * @api BLW
     * @since 1.0.1
     * @throws \InvalidArgumentException When current node list is empty.
     * @return string The node html
     */
    public function GetHTML()
    {
        if (!count($this)) {
            trigger_error(sprintf('%s::GetHTML(): Current object is empty.', get_class($this)), E_USER_WARNING);
            return '';
        }
        
        $HTML = '';
        $i = 0;
        
        if (version_compare(PHP_VERSION, '5.3.6', '>=')) {
            
            foreach($this as $k => $Node) {
                
                if($Node instanceof \DOMNode) {
                    if($k == 0 || $Node->ownerDocument != $this->Document) {
                        $HTML .= $Node->ownerDocument->saveHTML($Node);
                    }
                }
                
                elseif($Node instanceof \BLW\ElementInterface) {
                    if(count($Node) && $Node->Document() !== $this->Document())
                        $HTML .= $Node->GetHTML();
                }
            }
        }
        
        else {
            
            foreach($this as $Node) {
                
                if($Node instanceof \DOMNode) {
                    
                    $document = new \DOMDocument('1.0', 'UTF-8');
                    $document->appendChild($document->importNode($Node, true));
                    $HTML .= rtrim(preg_replace(array('/^.*<body[^>]*>/i', '/<\/body[^>]*>.*$/i'), '', $document->saveHTML()));
                    unset($document);
                }
                
                elseif($Node instanceof ElementInterface) {
                    if(count($Node) && $Node->Document() != $this->Document())
                        $HTML .= $Node->GetHTML();
                }
            }
        }
        
        return $HTML;
    }

    /**
     * Echos element. (Needed for chaining)
     * @api BLW
     * @since 1.0.1
     * @param bool $isDocument Whether object is a document element.
     * @return \BLW\Element $this
     */
    public function PrintHTML($isDocument = false)
    {
        if($this->count()) {
            print $this->GetHTML($isDocument);
        }
        
        return $this;
    }
    
    /**
     * Set / Get Current elements tag.
     * @note Raises <code>E_USER_ERROR</code> if default node does not exist.
     * @param string $Tag New tag name. (<code>[A-Za-z][\w_-]*</code>)
     * @return string | \BLW\Element Returns current tagName string or $this
     */
    public function tag($Tag = NULL)
    {
        if(is_null($Tag)) {
            
            if(($Node = \SplDoublyLinkedList::offsetGet(0)) instanceof \DOMElement) {
                return $Node->tagName;
            }
            
            else {
                trigger_error(sprintf('%s::tag(): Current Element has no default node.', get_class($this)), E_USER_ERROR);
                return '';
            }
        }
        
        elseif(preg_match('/[A-Za-z][\w_-]*/', @strval($Tag))) {
            
            if(($Node = \SplDoublyLinkedList::offsetGet(0)) instanceof \DOMElement) {
                $New = $Node->ownerDocument->createElement($Tag);
                
                foreach ($Node->attributes as $Attribute) {
                    $New->setAttribute($Attribute->nodeName, $Attribute->nodeValue);
                }
                
                while ($Node->firstChild) {
                    $Renamed->appendChild($Node->firstChild);
                }
                
                return $Node->parentNode->replaceChild($New, $Node);
            }
            
            else {
                trigger_error(sprintf('%s::tag(): Current Element has no default node.', get_class($this)));
            }
        }
        
        else {
            throw new \BLW\InvalidArgumentException(0);
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
        if(count($this)) {
            $Document = new \DOMDocument('1.0', 'UTF-8');
            
            $Document->loadHTML($this->GetHTML());
            
            $XPath = new \DOMXPath($Document);
            $List  = $XPath->query($Query);
            
            unset($Document, $XPath);
            
            return $List;
        }
        
        return array();
    }
     
    /**
     * Filters the list of nodes with a CSS selector.
     * @api BLW
     * @since 1.0.1
     * @link http://symfony.com/doc/current/components/css_selector.html Symfony > CssSelector
     * @param string $Selector A CSS selector
     * @return \DOMNodeList List of matched nodes.
     */
    public function filter($Selector)
    {
        return $this->filterXPath(CssSelector::toXPath($Selector));
    }
    
    /**
     * @ignore
     */
    public function __toString()
    {
        if($this->count()) {
            return $this->GetHTML();
        }
        
        return '';
    }
}

return ;