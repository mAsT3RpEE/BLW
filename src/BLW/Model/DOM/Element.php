<?php
/**
 * Element.php | Apr 2, 2014
 *
 * @filesource
 * @license MIT
 * @copyright Copyright (c) 2013-2018, mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 *
 * @package BLW\DOM
 * @version GIT 0.2.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model\DOM;

use ReflectionMethod;
use DOMNode;
use DOMDocument;
use DOMXPath;
use OutOfBoundsException;
use UnexpectedValueException;
use IteratorIterator;

use BLW\Type\IDataMapper;
use BLW\Type\ISerializer;
use BLW\Type\DOM\IElement;
use BLW\Type\DOM\IDocument;

use BLW\Model\InvalidArgumentException;
use BLW\Model\DOM\Exception as DOMException;

use Symfony\Component\CssSelector\CssSelector;


// @codeCoverageIgnoreStart
if (! defined('BLW')) {

    if (strstr($_SERVER['PHP_SELF'], basename(__FILE__))) {
        header("$_SERVER[SERVER_PROTOCOL] 404 Not Found");
        header('Status: 404 Not Found');

        $_SERVER['REDIRECT_STATUS'] = 404;

        echo "<html>\r\n<head><title>404 Not Found</title></head><body bgcolor=\"white\">\r\n<center><h1>404 Not Found</h1></center>\r\n<hr>\r\n<center>nginx/1.5.9</center>\r\n</body>\r\n</html>\r\n";
        exit();
    }

    return false;
}
// @codeCoverageIgnoreEnd


/**
 * Improvement over PHP's Element class.
 *
 * <h4>Note</h4>
 *
 * <p>No attempts to make this class serializable will
 * ever be attempted.</p>
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +-------------------+
 * | ELEMENT                                           |<------| DOMElement        |
 * +---------------------------------------------------+       +-------------------+
 * | #Document: getDocument()                          |<------| FACTORY           |
 * | #innerHTML: getInnerHTML()                        |       | ================= |
 * | setInnerHTML()                                    |       | createFromString  |
 * | #outerHTML: getOuterHTML()                        |       | createDocument    |
 * | setOUterHTML()                                    |       +-------------------+
 * +---------------------------------------------------+<------| IteratorAggregate |
 * | createFromString(): Element                       |       +-------------------+
 * |                                                   |<------| ArrayAccess       |
 * | $HTML: string (HTML)                              |       +-------------------+
 * | $Encoding: string (utf-8)                         |
 * +---------------------------------------------------+
 * | createDocument(): Document                        |
 * +---------------------------------------------------+
 * | getDocument(): DOMDocument                        |
 * +---------------------------------------------------+
 * | getInnerHTML(): string                            |
 * +---------------------------------------------------+
 * | setInnerHTML(): string (HTML)                     |
 * |                                                   |
 * | $HML: string                                      |
 * | $Encoding: string                                 |
 * +---------------------------------------------------+
 * | getOuterHTML(): string                            |
 * +---------------------------------------------------+
 * | setOuterHTML(): string (HTML)                     |
 * |                                                   |
 * | $HML: string                                      |
 * | $Encoding: string                                 |
 * +---------------------------------------------------+
 * | append(): bool                                    |
 * |                                                   |
 * | $Node: DOMNode                                    |
 * +---------------------------------------------------+
 * | prepend(): bool                                   |
 * |                                                   |
 * | $Node: DOMNode                                    |
 * +---------------------------------------------------+
 * | replace(): bool                                   |
 * |                                                   |
 * | $Node: DOMNode                                    |
 * +---------------------------------------------------+
 * | wrapOuter(): bool                                 |
 * |                                                   |
 * | $Node: DOMNode                                    |
 * +---------------------------------------------------+
 * | wrapInner(): bool                                 |
 * |                                                   |
 * | $Node: DOMNode                                    |
 * +---------------------------------------------------+
 * | filterXPath(): NodeList                           |
 * |                                                   |
 * | $Query: String (XPath)                            |
 * +---------------------------------------------------+
 * | filter(): filterXPath()                           |
 * |                                                   |
 * | $Selector: string (CSS Selector)                  |
 * +---------------------------------------------------+
 * | __toString(): string                              |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\DOM
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @todo Replace pasted code with a function.
 */
class Element extends \BLW\Type\DOM\AElement
{

#############################################################################################
# Factory Trait
#############################################################################################

    /**
     * Return an array of factory methods associated with the class.
     *
     * @return \ReflectionMethod[] Array of factory methods.
     */
    public static function getFactoryMethods()
    {
        return array(
            new ReflectionMethod(get_called_class(), 'createFromString'),
            new ReflectionMethod(get_called_class(), 'createDocument')
        );
    }

    /**
     * Turns a string into a Element.
     *
     * @api BLW
     * @since 0.2.0
     * @link http://www.php.net/manual/en/function.mb-check-encoding.php mb_check_encoding()
     *
     * @param string $HTML
     *            Raw HTML string.
     * @param string $Encoding
     *            See mb_check_encoding()
     * @return \BLW\Type\IElement Returns <code>null</code> on error.
     */
    public static function createFromString($HTML, $Encoding = 'UTF-8')
    {
        // Is $HTML a string?
        if (is_string($HTML) ?: is_callable(array(
            $HTML,
            '__toString'
        ))) {

            // Trim whitespaces
            $HTML = trim($HTML);

            // Is string properly encoded?
            if (mb_check_encoding($HTML, $Encoding)) {

                // Disable errors
                $current         = libxml_use_internal_errors(true);
                $disableEntities = libxml_disable_entity_loader(true);

                // Load HTML
                $Document        = new Document();

                $Document->loadHTML($HTML);

                // Enable errors
                libxml_use_internal_errors($current);
                libxml_disable_entity_loader($disableEntities);

                // Check $HTML
                $Regex    = sprintf('!%s!s', IElement::R_STARTTAG);
                $FirstTag = preg_match($Regex, $HTML, $m)
                    ? strtolower($m['tagname'])
                    : '*';

                // First tag is doctype?
                if ($FirstTag == 'doctype' || $FirstTag == 'html') {

                    // Return DocumentElement
                    return $Document->documentElement;
                }

                // Does tag exist
                $List = $Document->getElementsByTagName($FirstTag);

                // Yes return 1st tag
                if ($List->length)
                    return $List->item(0);

                // No return null
                else
                    return null;
            }

            // Invalid encoding
            else
                throw new DOMException('Invalid Encoding ');
        }

        // Invalid $HTML
        else
            throw new InvalidArgumentException(0);

        // Error
        return false;
    }

    /**
     * Creates an instance of DOMDocument.
     *
     * <h4>Note</h4>
     *
     * <p>Should return the same instance on consecutive calls</p>
     *
     * <hr>
     *
     * @api BLW
     * @since 0.2.0
     * @see \BLW\Type\DOM\IDocument IDOMDocument
     *
     * @return \BLW\Type\DOM\IDocument Generated object.
     */
    public static function createDocument()
    {
        static $cache = null;

        return $cache = $cache ?: new Document();
    }

#############################################################################################
# Element Trait
#############################################################################################

    /**
     * Retrieves the current document of DOMNode.
     *
     * <h4>Note</h4>
     *
     * <p>If the element is not atached to a document function
     * will create one and update <code>$Element</code>.</p>
     *
     * <hr>
     *
     * @param \DOMNode $Element
     *            [optional] Imported node if new document is created.
     * @return \BLW\Type\DOM\IDocument Found / Generated document.
     */
    public static function getDocument(DOMNode & $Node = null)
    {
        // Does element have a document?
        if ($Node->ownerDocument instanceof \DOMDocument) {

            // Return document
            return $Node->ownerDocument;
        }

        // No Document
        else {

            // Create Document
            $Document = self::createDocument();

            // Create node
            switch ($Node->nodeType) {
                case XML_ELEMENT_NODE:
                    $New = $Document->createElement($Node->tagName, $Node->nodeValue);
                    break;
                case XML_ATTRIBUTE_NODE:
                    $New = $Document->createAttribute($Node->name);
                    break;
                case XML_TEXT_NODE:
                    $New = $Document->createTextNode($Node->wholeText);
                    break;
                case XML_CDATA_SECTION_NODE:
                    $New = $Document->createCDATASection($Node->data);
                    break;
                case XML_TEXT_NODE:
                    $New = $Document->createTextNode($Node->wholeText);
                    break;

                // Invalid DOMNode
                default:
                    throw new DOMException('Invalid node with no ownder document');
            }

            // Copy attributes

            // @codeCoverageIgnoreStart
            if ($Node->hasAttributes()) {
                foreach ($Node->attributes as $Attribute) {
                    $New->setAttribute($Attribute->name, $Attribute->value);
                }
            }
            // @codeCoverageIgnoreEnd

            // Update $Node
            $Node = $New;

            // Return document
            return $Document;
        }
    }

    /**
     * Retrieves the Inner HTML of element.
     *
     * @return string Raw HTML. Returns <code>FALSE</code> on error.
     */
    public function getInnerHTML()
    {
        // Does document exist?
        if ($this->ownerDocument instanceof DOMDocument) {

            // Return value
            $innerHTML = '';

            // @codeCoverageIgnoreStart

            // PHP >= 5.3.6
            if (version_compare(PHP_VERSION, '5.3.6', '>=')) {

                // Loop through each child
                foreach ($this->childNodes as $child) {

                    // Add child HTML
                    $innerHTML .= $child->ownerDocument->saveHTML($child);
                }
            }

            // PHP <= 5.3.6
            else {

                // Loop through each child
                foreach ($this->childNodes as $child) {

                    // Create new Document
                    $Document = new Document();

                    // Attach child
                    $Document->appendChild($Document->importNode($child, true));

                    // Save HTML
                    $innerHTML .= $Document->saveHTML();
                }

                unset($Document);
            }

            // @codeCoverageIgnoreEnd

            // Done
            return $innerHTML;
        }

        // Error
        return false;
    }

    /**
     * Set the inner HTML of an element.
     *
     * @todo Fix this up
     * @link http://www.php.net/manual/en/function.mb-check-encoding.php mb_check_encoding()
     *
     * @throws \BLW\Model\DOM\Exception If <code>$HTML</code> is not properly encoded.
     * @throws \BLW\Model\InvalidArgumentException If <code>$HTML</code> is not a string.
     *
     * @param string $HTML
     *            Raw HTML.
     * @param string $Encoding
     *            See mb_check_encoding()
     * @return \BLW\Type\IElement $this
     */
    public function setInnerHTML($HTML, $Encoding = 'UTF-8')
    {
        // Is $HTML scalar?
        if (is_string($HTML) ?  : is_callable(array(
            $HTML,
            '__toString'
        ))) {

            // Trim whitespaces
            $HTML = trim($HTML);

            // Is string properly encoded?
            if (mb_check_encoding($HTML, $Encoding)) {

                // Parse Elements
                $Nodes    = array();
                $Document = new Document('1.0', $Encoding, get_class($this));

                $Document->loadHTML($HTML);

                // Get nodes relative to current tag name
                switch (strtolower($this->tagName)) {
                    case 'html':
                        $Nodes = $Document->documentElement->childNodes;
                        break;
                    case 'head':
                        $Nodes = $Document->documentElement->firstChild->childNodes;
                        break;
                    case 'body':
                    default:
                        $Nodes = $Document->documentElement->lastChild->childNodes;
                }

                // Remove children
                while ($Node = $this->firstChild)
                    $this->removeChild($Node);

                // Add new children
                foreach ($Nodes as $Node)
                    if ($Node = $this->ownerDocument->importNode($Node, true))
                        $this->appendChild($Node);
            }

            // Invalid encoding
            else
                throw new DOMException('Invalid Encoding');
        }

        // Invalid $HTML
        else
            throw new InvalidArgumentException(0);

        // Done
        return $this;
    }

    /**
     * Retrieves the HTML of element and its children.
     *
     * @return string Raw HTML. Returns <code>FALSE</code> on error.
     */
    public function getOuterHTML()
    {
        // Does document exist?
        if ($this->ownerDocument instanceof DOMDocument) {

            // Return value
            $outerHTML = '';

            // @codeCoverageIgnoreStart

            // PHP >= 5.3.6
            if (version_compare(PHP_VERSION, '5.3.6', '>=')) {

                // Add HTML
                $outerHTML = $this->ownerDocument->saveHTML($this);
            }

            // PHP <= 5.3.6
            else {

                // Create new Document
                $Document = new Document();

                // Attach child
                $Document->appendChild($Document->importNode($child, true));

                // Save HTML
                $outerHTML = $Document->saveHTML();
            }

            // @codeCoverageIgnoreEnd

            // Done
            return $outerHTML;
        }

        // Error
        return false;
    }

    /**
     * Sets the HTML of the element.
     *
     * @link http://www.php.net/manual/en/function.mb-check-encoding.php mb_check_encoding()
     *
     * @param string $HTML
     *            Raw HTML.
     * @param string $Encoding
     *            See mb_check_encoding()
     * @return \BLW\Type\IElement $this. Returns <code>FALSE</code> on error.
     */
    public function setOuterHTML($HTML, $Encoding = 'UTF-8')
    {
        // Is Node writable?
        if ($this->ownerDocument instanceof DOMDocument) {

            // Is $HTML properly encoded?
            if (mb_check_encoding($HTML, $Encoding)) {

                // Create Node
                $Element = $this->createFromString($HTML, $Encoding);

                // Import node
                $Element = $this->ownerDocument->importNode($Element, true);

                // Replace node
                return $this->parentNode->replaceChild($Element, $this);
            }

            // Invalid encoding
            else
                throw new DOMException('Invalid encoding');
        }

        // Node is readonly
        else
            throw new DOMException('Cannot modify readonly element');

            // Error
        return false;
    }

    /**
     * Appends a DOMNode after last child in an element
     *
     * @param \DOMNode $Node
     *            Object to attach.
     * @return \BLW\Type\IElement $this. Returns <code>FALSE</code> on error.
     */
    public function append(DOMNode $Node)
    {
        // Is Node writable?
        if ($this->ownerDocument instanceof DOMDocument) {

            // Does node belong to current document?
            if ($Node->ownerDocument !== $this->ownerDocument)
                // Import node
                $Node = $this->ownerDocument->importNode($Node, true);

            // Add child
            return $this->appendChild($Node)
                ? $this
                : false;
        }

        // Node is readonly
        else
            throw new DOMException('Cannot modify readonly element');

        // Error
        return false;
    }

    /**
     * Prepends a DOMNode before 1st child in an element
     *
     * @param \DOMNode $Node
     *            Object to attach.
     * @return \BLW\Type\IElement $this. Returns <code>FALSE</code> on error.
     */
    public function prepend(DOMNode $Node)
    {
        // Is Node writable?
        if ($this->ownerDocument instanceof DOMDocument) {

            // Does node belong to current document?
            if ($Node->ownerDocument !== $this->ownerDocument)
                // Import node
                $Node = $this->ownerDocument->importNode($Node, true);

            // Does class have children?
            if (($first = $this->firstChild) instanceof DOMNode) {

                // Add child
                return $this->insertBefore($Node, $first)
                    ? $this
                    : false;
            }

            // No children.
            else {

                // Add child
                return $this->appendChild($Node)
                    ? $this
                    : false;
            }
        }

        // Node is readonly
        else
            throw new DOMException('Cannot modify readonly element');

        // Error
        return false;
    }

    /**
     * Replaces the current Element with another.
     *
     * @param \DOMNode $Element
     *            Node to replace current element with.
     * @return \BLW\Type\IElement $Element. Returns <code>FALSE</code> on error.
     */
    public function replace(DOMNode $Element)
    {
        // Is current class writable?
        if ($this->ownerDocument instanceof DOMDocument) {

            // Does $Element belong to current document?
            if ($Element->ownerDocument !== $this->ownerDocument)
                // Import node
                $Element = $this->ownerDocument->importNode($Element, true);

            // Replace
            return $this->parentNode->replaceChild($Element, $this)
                ? $Element
                : false;
        }

        // Node is readonly
        else
            throw new DOMException('Current node is readonly');

            // Error
        return false;
    }

    /**
     * Wraps inner HTML with element.
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Element</code> cannot contain other elements.
     *
     * @param \BLW\Type\IElement $Element
     *            Element to wrap child nodes in.
     * @return \BLW\Type\IElement $this. <code>FALSE</code> on error.
     */
    public function wrapInner(IElement $Element)
    {
        // Is current class writable?
        if ($this->ownerDocument instanceof DOMDocument) {

            // Does $Element belong to current document?
            if ($Element->ownerDocument !== $this->ownerDocument)
                // Import node
                $Element = $this->ownerDocument->importNode($Element, false);

            // Shift children to $Element
            while ($child = $this->firstChild)
                // Remove from $this and send to $Element
                if ($child = $this->removeChild($child))
                    $Element->appendChild($child);

            // Normalize
            $Element->normalize();

            // Atach Element to current node
            return $this->appendChild($Element)
                ? $this
                : false;
        }

        // Node is readonly
        else
            throw new DOMException('Current node is readonly');

        // Error
        return false;
    }

    /**
     * Wraps the current element inside another elements body.
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Element</code> cannot contain other elements.
     *
     * @param \BLW\Type\IElement $Element
     *            Element to wrap current node in.
     * @return \BLW\Type\IElement $this. <code>FALSE</code> in case of error.
     */
    public function wrapOuter(IElement $Element)
    {
        // Is current class writable?
        if ($this->ownerDocument instanceof DOMDocument) {

            // Does $Element belong to current document?
            if ($Element->ownerDocument !== $this->ownerDocument)
                // Import node
                $Element = $this->ownerDocument->importNode($Element, false);

            // Replace node, and reatach this
            $this->replace($Element)->appendChild($this);

            // Done
            return $this;
        }

        // Node is readonly
        else
            throw new DOMException('Current node is readonly');

        // Error
        return false;
    }

    /**
     * Filters the document for elements matching the xpath query.
     *
     * @param string $Query
     *            XPath.
     * @return \BLW\Model\DOM\NodeList Returns an instance of <code>IContainer</code> containing all matched elements.
     */
    public function filterXPath($Query)
    {
        // Make sure we have a document
        $Document = $this->getDocument($this);

        // Run XPath
        $XPath    = new DOMXPath($Document);
        $List     = $XPath->evaluate($Query, $this);

        unset($XPath, $Document);

        // Create NodeList
        return new NodeList($List);
    }

    /**
     * Filters the document for elements matching the css selector.
     *
     * @link https://github.com/symfony/CssSelector/blob/master/CssSelector.php CssSelector
     * @see \BLW\Type\DOM\IDocument::filterXPath() IDOMDocument::filterXPath()
     *
     * @param string $Selector
     *            CSS Selector.
     * @return \BLW\Model\DOM\NodeList Returns an instance of <code>IContainer</code> containing all matched elements.
     */
    public function filter($Selector)
    {
        // Generate XPath
        $Query = CssSelector::toXPath($Selector);

        // Run XPath
        return $this->filterXPath($Query);
    }

#############################################################################################
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
