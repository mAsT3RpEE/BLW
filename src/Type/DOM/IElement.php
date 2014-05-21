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
 * @version GIT: 0.2.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
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
 * | #Document:   getDocument()                        |<------| ITERABLE          |
 * | #innerHTML:  getInnerHTML()                       |       +-------------------+
 * |              setInnerHTML()                       |<------| FACTORY           |
 * | #outerHTML:  getOuterHTML()                       |       | ================= |
 * |              setOUterHTML()                       |       | createFromString  |
 * +---------------------------------------------------+       | createDocument    |
 * | createFromString() IElement                       |       +-------------------+
 * |                                                   |<------| IteratorAggregate |
 * | $HTML:      string (HTML)                         |       +-------------------+
 * | $Encoding:  string (utf-8)                        |<------| ArrayAccess       |
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
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
interface IElement extends \BLW\Type\IIterable, \BLW\Type\IFactory, \IteratorAggregate, \ArrayAccess, \Countable
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
     * @return boolean <code>TRUE</code> if the requested index exists, <code>FALSE</code> otherwise.
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
     * @return \DOMNode|null The value at the specified index or <code>NULL</code>.
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
     * @return \DOMNode|null Added child.
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
     * @return \DOMNode|null Removed child.
     */
    public function offsetUnset($index);

    /**
     * Get the number of public properties in the ArrayObject
     *
     * @api BLW
     * @since 0.2.0
     * @link http://www.php.net/manual/en/countable.count.php Countable::count()
     *
     * @return integer The number of public properties in the ArrayObject.
     */
    public function count();

    /**
     * Create a new iterator from an ArrayObject instance
     *
     * @api BLW
     * @since 0.2.0
     * @link http://www.php.net/manual/en/iteratoraggregate.getiterator.php IteratorAggregate::getIterator()
     *
     * @return \Iterator An instance implementing <code>Iterator</code>.
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
     * @return \BLW\Type\DOM\IElement $Element. Returns <code>FALSE</code> on error.
     */
    public function replace(DOMNode $Element);

    /**
     * Wraps inner HTML with element.
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Element</code> cannot contain other elements.
     *
     * @param \BLW\Type\DOM\IElement $Element
     *            Element to wrapp child nodes in.
     * @return \BLW\Type\DOM\IElement $this. <code>FALSE</code> on error.
     */
    public function wrapInner(IElement $Element);

    /**
     * Wraps the current element inside another elements body.
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Element</code> cannot contain other elements.
     *
     * @param \BLW\Type\DOM\IElement $Element
     *            Element to wrap current node in.
     * @return \BLW\Type\DOM\IElement $this. <code>FALSE</code> in case of error.
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
     * @return \BLW\Model\DOM\NodeList Returns a container containing all matched elements.
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
     * @return \BLW\Model\DOM\NodeList Returns a container containing all matched elements.
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

    /**
     * Adds a new child before a reference node.
     *
     * @link http://www.php.net/manual/en/domnode.insertbefore.php DOMNode::insertBefore()
     *
     * @param \DOMNode $newnode
     *               The new node.
     * @param \DOMNode $refnode
     *               [optional] The reference node. If not supplied, newnode is appended to the children.
     * @return \DOMNode The inserted node.
     */
    public function insertBefore(DOMNode $newnode, DOMNode $refnode = null);

    /**
     * Replaces a child.
     *
     * @link http://www.php.net/manual/en/domnode.replacechild.php DOMNode::replaceChild()
     *
     * @param \DOMNode $newnode
     *           The new node. It must be a member of the target document, i.e. created by one of the DOMDocument->createXXX() methods or imported in the document by.
     * @param \DOMNode $oldnode
     *           The old node.
     * @return \DOMNode The old node or false if an error occur.
     */
    public function replaceChild(DOMNode $newnode, DOMNode $oldnode);

    /**
     * Removes child from list of children.
     *
     * @link http://www.php.net/manual/en/domnode.removechild.php DOMNode::removeChild()
     *
     * @param \DOMNode $oldnode
     *            The removed child.
     * @return \DOMNode If the child could be removed the function returns the old child.
     */
    public function removeChild(DOMNode $oldnode);

    /**
     * Adds new child at the end of the children.
     *
     * @link http://www.php.net/manual/en/domnode.appendchild.php DOMNode::appendChild()
     * @param \DOMNode $newnode
     *            The appended child.
     * @return \DOMNode The node added.
     */
    public function appendChild(DOMNode $newnode);

    /**
     * Checks if node has children.
     *
     * @link http://www.php.net/manual/en/domnode.haschildnodes.php DOMNode::hasChildNodes()
     *
     * @return bool Returns true on success or false on failure.
     */
    public function hasChildNodes();

    /**
     * Normalizes the node.
     *
     * @link http://www.php.net/manual/en/domnode.normalize.php DOMNode::normalize()
     *
     * @return void
     */
    public function normalize();

    /**
     * Checks if feature is supported for specified version.
     *
     * @link http://www.php.net/manual/en/domnode.issupported.php DOMNode::isSupporte()
     *
     * @param string $feature
     *            The feature to test. See the example of DOMImplementation::hasFeature for a list of features.
     * @param string $version
     *            The version number of the feature to test.
     * @return bool Returns true on success or false on failure.
     */
    public function isSupported($feature, $version);

    /**
     * Checks if node has attributes.
     *
     * @link http://www.php.net/manual/en/domnode.hasattributes.php DOMNode::hasAttributes()
     *
     * @return bool Returns true on success or false on failure.
     */
    public function hasAttributes();

    /**
     * Indicates if two nodes are the same node.
     *
     * @link http://www.php.net/manual/en/domnode.issamenode.php DOMNode::isSameNode()
     *
     * @param \DOMNode $node
     *            The compared node.
     * @return bool Returns true on success or false on failure.
     */
    public function isSameNode(DOMNode $node);

    /**
     * Gets the namespace prefix of the node based on the namespace URI.
     *
     * @link http://www.php.net/manual/en/domnode.lookupprefix.php DOMNode::lookupPrefix()
     *
     * @param string $namespaceURI
     *            The namespace URI.
     * @return string The prefix of the namespace.
     */
    public function lookupPrefix($namespaceURI);

    /**
     * Checks if the specified namespaceURI is the default namespace or not.
     *
     * @link http://www.php.net/manual/en/domnode.isdefaultnamespace.php DOMNode::isDefaultNamespace()
     *
     * @param string $namespaceURI
     *            The namespace URI to look for.
     * @return bool Return true if namespaceURI is the default namespace, false otherwise.
     */
    public function isDefaultNamespace($namespaceURI);

    /**
     * Gets the namespace URI of the node based on the prefix.
     *
     * @link http://www.php.net/manual/en/domnode.lookupnamespaceuri.php DOMNode::lookupNamespaceURI()
     *
     * @param prefix string
     *            The prefix of the namespace.
     * @return string The namespace URI of the node.
     */
    public function lookupNamespaceUri($prefix);

    /**
     * Get an XPath for a node.
     *
     * @link http://www.php.net/manual/en/domnode.getnodepath.php DOMNode::getNodePath()
     *
     * @return string a string containing the XPath, or &null; in case of an error.
     */
    public function getNodePath();

    /**
     * Get line number for a node
     *
     * @link http://www.php.net/manual/en/domnode.getlineno.php DOMNode::getLineNo()
     *
     * @return int Always returns the line number where the node was defined in.
     */
    public function getLineNo();

    /**
     * Canonicalize nodes to a string.
     *
     * @link http://www.php.net/manual/en/domnode.c14n.php DOMNode::C14N()
     *
     * @param bool $exclusive
     *            [optional] Enable exclusive parsing of only the nodes matched by the provided xpath or namespace prefixes.
     * @param bool $with_comments
     *            [optional] Retain comments in output.
     * @param array $xpath
     *            [optional] An array of xpaths to filter the nodes by.
     * @param array $ns_prefixes
     *            [optional] An array of namespace prefixes to filter the nodes by.
     * @return string canonicalized nodes as a string or false on failure
     */
    public function C14N($exclusive = null, $with_comments = null, array $xpath = null, array $ns_prefixes = null);

    /**
     * Canonicalize nodes to a file.
     *
     * @link http://www.php.net/manual/en/domnode.c14nfile.php DOMNode::C14NFile()
     *
     * @param string $uri
     *            Path to write the output to.
     * @param bool $exclusive
     *            [optional] Enable exclusive parsing of only the nodes matched by the provided xpath or namespace prefixes.
     * @param bool $with_comments
     *            [optional] Retain comments in output.
     * @param array $xpath
     *            [optional] An array of xpaths to filter the nodes by.
     * @param array $ns_prefixes
     *            [optional] An array of namespace prefixes to filter the nodes by.
     * @return int Number of bytes written or false on failure
     */
    public function C14NFile($uri, $exclusive = null, $with_comments = null, array $xpath = null, array $ns_prefixes = null);
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
