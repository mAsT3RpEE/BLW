<?php
/**
 * IElement.php | Apr 2, 2014
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
namespace BLW\Type\DOM;

use DOMNode;


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
 * Improvement over PHP's DOMElement class.
 *
 * <p>No attempts to make this class serializable will
 * ever be attempted.</p>
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +-------------------+
 * | DOMELEMENT                                        |<------| DOMElement        |
 * +---------------------------------------------------+       +-------------------+
 * | #Document:   getDocument()                        |<------| ITERABLE |
 * | #innerHTML:  getInnerHTML()                       |       +-------------------+
 * |              setInnerHTML()                       |<------| FACTORY |
 * | #outerHTML:  getOuterHTML()                       |       | ================= |
 * |              setOUterHTML()                       |       | createFromString |
 * +---------------------------------------------------+       | createDocument |
 * | createFromString() IElement                       |       +-------------------+
 * |                                                   |<------| IteratorAggregate |
 * | $HTML:      string (HTML)                         |       +-------------------+
 * | $Encoding:  string (utf-8)                        |<------| ArrayAccess |
 * +---------------------------------------------------+       +-------------------+
 * | createDocument(): DOMDocument                     |
 * +---------------------------------------------------+
 * | getDocument(): DOMDocument                        |
 * |                                                   |
 * | $Node:  DOMNode                                   |
 * +---------------------------------------------------+
 * | getInnerHTML(): string                            |
 * +---------------------------------------------------+
 * | setInnerHTML(): string (HTML)                     |
 * |                                                   |
 * | $HTML:      string                                |
 * | $Encoding:  string                                |
 * +---------------------------------------------------+
 * | getOuterHTML(): string                            |
 * +---------------------------------------------------+
 * | setOuterHTML(): string (HTML)                     |
 * |                                                   |
 * | $HML:       string                                |
 * | $Encoding:  string                                |
 * +---------------------------------------------------+
 * | append(): bool                                    |
 * |                                                   |
 * | $Node:  DOMNode                                   |
 * +---------------------------------------------------+
 * | prepend(): bool                                   |
 * |                                                   |
 * | $Node: DOMNode                                    |
 * +---------------------------------------------------+
 * | replace(): bool                                   |
 * |                                                   |
 * | $Node:  DOMNode                                   |
 * +---------------------------------------------------+
 * | wrapOuter(): bool                                 |
 * |                                                   |
 * | $Node:  IElement                                  |
 * +---------------------------------------------------+
 * | wrapInner(): bool                                 |
 * |                                                   |
 * | $Node:  IElement                                  |
 * +---------------------------------------------------+
 * | filterXPath(): NodeList                           |
 * |                                                   |
 * | $Query:  String (XPath)                           |
 * +---------------------------------------------------+
 * | filter(): filterXPath()                           |
 * |                                                   |
 * | $Selector:  string (CSS Selector)                 |
 * +---------------------------------------------------+
 * | __toString():  string                             |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\DOM
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
interface IElement extends \BLW\Type\IIterable, \BLW\Type\IFactory, \IteratorAggregate, \ArrayAccess
{
    // Regex
    const R_ATTRIBUTE = '[^\x22>]+(?:\x22[^\x22]*\x22)?[^\x22>]*';
    const R_NAME      = '[A-Za-z\p{L}][0-9\x2d:_A-Za-z\p{L}]*';
    const R_STARTTAG  = '<\s*(?<namespace>[A-Za-z\p{L}][0-9\x2d:_A-Za-z\p{L}]*:)?(?P<tagname>[A-Za-z\p{L}][0-9\x2d:_A-Za-z\p{L}]*)(?P<attribute>[^\x22>]+(?:\x22[^\x22]*\x22)?[^\x22>]*)*>';
    const R_ENDTAG    = '<\s*\x2f\s*(?<namespace2>[A-Za-z\p{L}][0-9\x2d:_A-Za-z\p{L}]*:)?(?P<tagname2>[A-Za-z\p{L}][0-9\x2d:_A-Za-z\p{L}]*)\s*>';
    const R_SINGLETAG = '<\s*(?<namespace>[A-Za-z\p{L}][0-9\x2d:_A-Za-z\p{L}]*:)?(?P<tagname>[A-Za-z\p{L}][0-9\x2d:_A-Za-z\p{L}]*)(?P<attribute>[^\x22>]+(?:\x22[^\x22]*\x22)?[^\x22>]*)*\x2f\s*>';

    /**
     * Returns whether the requested index exists
     *
     * @api BLW
     * @since 0.2.0
     * @link http://www.php.net/manual/en/arrayaccess.offsetexists.php ArrayAccess::offsetExists()
     *
     * @param mixed $index
     *            The index being checked.
     * @return bool <code>TRUE</code> if the requested index exists, <code>FALSE</code> otherwise.
     */
    public function offsetExists($index);

    /**
     * Returns the value at the specified index
     *
     * @api BLW
     * @since 0.2.0
     * @link http://www.php.net/manual/en/arrayaccess.offsetget.php ArrayAccess::offsetGet()
     *
     * @param mixed $index
     *            The index with the value.
     * @return mixed The value at the specified index or <code>FALSE</code>.
     */
    public function offsetGet($index);

    /**
     * Sets the value at the specified index to newval
     *
     * @api BLW
     * @since 0.2.0
     * @link http://www.php.net/manual/en/arrayaccess.offsetset.php ArrayAccess::offsetSet()
     *
     * @param mixed $index
     *            The index being set.
     * @param mixed $newval
     *            The new value for the index.
     */
    public function offsetSet($index, $newval);

    /**
     * Unsets the value at the specified index
     *
     * @api BLW
     * @since 0.2.0
     * @link http://www.php.net/manual/en/arrayaccess.offsetunset.php ArrayAccess::offsetUnset()
     *
     * @param mixed $index
     *            The index being unset.
     */
    public function offsetUnset($index);

    /**
     * Get the number of public properties in the ArrayObject
     *
     * @api BLW
     * @since 0.2.0
     * @link http://www.php.net/manual/en/countable.count.php Countable::count()
     *
     * @return int The number of public properties in the ArrayObject.
     */
    public function count();

    /**
     * Create a new iterator from an ArrayObject instance
     *
     * @api BLW
     * @since 0.2.0
     * @link http://www.php.net/manual/en/iteratoraggregate.getiterator.php IteratorAggregate::getIterator()
     *
     * @return \RecursiveArrayIterator An instance implementing <code>Iterator</code>.
     */
    public function getIterator();

    /**
     * Turns a string into a DOMElement.
     *
     * @api BLW
     * @since 0.2.0
     * @link http://www.php.net/manual/en/function.mb-check-encoding.php mb_check_encoding()
     *
     * @param string $HTML
     *            Raw HTML string.
     * @param string $Encoding
     *            See mb_check_encoding()
     * @return \BLW\Type\DOM\IElement
     */
    public static function createFromString($HTML, $Encoding = 'UTF-8');

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
    public static function createDocument();

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
     * @api BLW
     * @since 0.2.0
     *
     * @param \DOMNode $Node
     *            [optional] Imported node if new document is created.
     * @return \BLW\Type\DOM\IDocument Found / Generated document.
     */
    public static function getDocument(DOMNode & $Node = null);

    /**
     * Retrieves the Inner HTML of element.
     *
     * @api BLW
     * @since 0.2.0
     *
     * @return string Raw HTML.
     */
    public function getInnerHTML();

    /**
     * Set the inner HTML of an element.
     *
     * @api BLW
     * @since 0.2.0
     * @link http://www.php.net/manual/en/function.mb-check-encoding.php mb_check_encoding()
     *
     * @throws \BLW\Model\DOM\Exception If <code>$HTML</code> is not properly encoded.
     * @throws \BLW\Model\InvalidArgumentException If <code>$HTML</code> is not a string.
     *
     * @param string $HTML
     *            Raw HTML.
     * @param string $Encoding
     *            See mb_check_encoding()
     * @return \BLW\Type\DOM\IElement $this
     */
    public function setInnerHTML($HTML, $Encoding = 'UTF-8');

    /**
     * Retrieves the HTML of element and its children.
     *
     * @api BLW
     * @since 0.2.0
     *
     * @return string Raw HTML.
     */
    public function getOuterHTML();

    /**
     * Sets the HTML of the element.
     *
     * @api BLW
     * @since 0.2.0
     * @link http://www.php.net/manual/en/function.mb-check-encoding.php mb_check_encoding()
     *
     * @param string $HTML
     *            Raw HTML.
     * @param string $Encoding
     *            See mb_check_encoding()
     * @return \BLW\Type\DOM\IElement $this. Returns <code>FALSE</code> on error.
     */
    public function setOuterHTML($HTML, $Encoding = 'UTF-8');

    /**
     * Appends a DOMNode after last child in an element
     *
     * @param \DOMNode $Node
     *            Object to attach.
     * @return \BLW\Type\DOM\IElement $this. Returns <code>FALSE</code> on error.
     */
    public function append(DOMNode $Node);

    /**
     * Prepends a DOMNode before 1st child in an element
     *
     * @param \DOMNode $Node
     *            Object to attach.
     * @return \BLW\Type\DOM\IElement $this. Returns <code>FALSE</code> on error.
     */
    public function prepend(DOMNode $Node);

    /**
     * Replaces the current Element with another.
     *
     * @param \DOMNode $Element
     *            Node to replace current element with.
     * @return \BLW\Type\IElement $Element. Returns <code>FALSE</code> on error.
     */
    public function replace(DOMNode $Element);

    /**
     * Wraps inner HTML with element.
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Element</code> cannot contain other elements.
     *
     * @param \BLW\Type\IElement $Element
     *            Element to wrapp child nodes in.
     * @return \BLW\Type\IElement $this. <code>FALSE</code> on error.
     */
    public function wrapInner(IElement $Element);

    /**
     * Wraps the current element inside another elements body.
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Element</code> cannot contain other elements.
     *
     * @param \BLW\Type\IElement $Element
     *            Element to wrap current node in.
     * @return \BLW\Type\IElement $this. <code>FALSE</code> in case of error.
     */
    public function wrapOuter(IElement $Element);

    /**
     * Filters the document for elements matching the xpath query.
     *
     * @api BLW
     * @since 0.1.0
     *
     * @param string $Query
     *            XPath.
     * @return \BLW\Model\ElementContainer Returns a container containing all matched elements.
     */
    public function filterXPath($Query);

    /**
     * Filters the document for elements matching the css selector.
     *
     * @api BLW
     * @since 0.1.0
     * @link https://github.com/symfony/CssSelector/blob/master/CssSelector.php CssSelector
     * @uses \BLW\Type\DOM\IElement::filterXPath() IElement::filterXPath()
     *
     * @param string $Selector
     *            CSS Selector.
     * @return \BLW\Model\ElementContainer Returns a container containing all matched elements.
     */
    public function filter($Selector);

    /**
     * All objects must have a string representation.
     *
     * @api BLW
     * @since 0.2.0
     * @link http://www.php.net/manual/en/language.oop5.magic.php#object.tostring __toString()
     *
     * @return string $this
     */
    public function __toString();
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd