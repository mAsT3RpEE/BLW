<?php
/**
 * AElement.php | Apr 2, 2014
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

use ReflectionMethod;
use DOMDocument;
use DOMNode;
use DOMAttr;
use OutOfBoundsException;
use UnexpectedValueException;
use IteratorIterator;

use BLW\Type\IObject;
use BLW\Type\IContainer;
use BLW\Type\IObjectStorage;
use BLW\Type\IWrapper;
use BLW\Type\DOM\IElement;
use BLW\Type\IDataMapper;

use BLW\Model\DOM\Exception;
use BLW\Model\InvalidArgumentException;


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
 * | DOMELEMENT                                        |<------| DOMElement        |
 * +---------------------------------------------------+       +-------------------+
 * | #Document:    getDocument()                       |<------| ITERABLE          |
 * | #innerHTML:   getInnerHTML()                      |       +-------------------+
 * |               setInnerHTML()                      |<------| FACTORY           |
 * | #outerHTML:   getOuterHTML()                      |       | ================= |
 * |               setOUterHTML()                      |       | createFromString  |
 * +---------------------------------------------------+       | createDocument    |
 * | createFromString(): IElement                      |       +-------------------+
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
 * | getInnerHTML():  string                           |
 * +---------------------------------------------------+
 * | setInnerHTML(): string (HTML)                     |
 * |                                                   |
 * | $HML:       string                                |
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
 * | $Node:  DOMNode                                   |
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
 */
abstract class AElement extends \DOMElement implements \BLW\Type\DOM\IElement
{

#############################################################################################
# Iterable Trait
#############################################################################################

    /**
     * Pointer to current parent of object.
     *
     * @var \BLW\Type\IObject $Parent
     */
    protected $_Parent = null;

#############################################################################################
# Element Trait
#############################################################################################

    /**
     * XPath of Element for object storage.
     *
     * @var array() $Stores
     */
    private $_XPath = '';

    /**
     * DOMDocument of Element for object storage.
     *
     * @var \BLW\Type\DOM\IDocument $Stores
     */
    private $_Document = null;

#############################################################################################




#############################################################################################
# Iterable Trait
#############################################################################################

    /**
     * Retrieves the current parent of the object.
     *
     * @return \BLW\Type\IObject Returns <code>null</code> if no parent is set.
     */
    final public function getParent()
    {
        return $this->_Parent;
    }

    /**
     * Sets parent of the current object if null.
     *
     * @internal This is a one shot function (Only works once).
     *
     * @param mised $Parent
     *            New parent of object. (IObject|IContainer|IObjectStorage)
     * @return int Returns a <code>DataMapper</code> status code.
     */
    final public function setParent($Parent)
    {
        // Make sur object is not a parent of itself
        if ($Parent === $this)
            return IDataMapper::INVALID;

        // Make sure parent is valid
        elseif (! $Parent instanceof IObject && ! $Parent instanceof IContainer && ! $Parent instanceof IObjectStorage && ! $Parent instanceof IWrapper)
            return IDataMapper::INVALID;

        // Make sure parent is not already set
        elseif (! $this->_Parent instanceof IObject && ! $this->_Parent instanceof IContainer && ! $this->_Parent instanceof IObjectStorage) {

            // Update parent
            $this->_Parent = $Parent;
            return IDataMapper::UPDATED;
        }

        // Else dont update parent
        else
            return IDataMapper::ONESHOT;
    }

    /**
     * Clears parent of the current object.
     *
     * @access private
     * @internal For internal use only.
     *
     * @return int Returns a <code>DataMapper</code> status code.
     */
    final public function clearParent()
    {
        $this->_Parent = null;
        return IDataMapper::UPDATED;
    }

#############################################################################################
# ArrayAccess Trait
#############################################################################################

    /**
     * Returns whether the requested index exists
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetexists.php ArrayAccess::offsetExists()
     *
     * @param mixed $index
     *            The index being checked.
     * @return bool <code>TRUE</code> if the requested index exists, <code>FALSE</code> otherwise.
     */
    final public function offsetExists($index)
    {
        // Is $index numeric?
        if (is_numeric($index)) {

            // Is index less than child nodes
            return intval($index) < $this->childNodes->length;
        }

        // Invalid $index
        else
            throw new OutOfBoundsException('Element[index]: index must be an integer');

        // Error
        return false;
    }

    /**
     * Returns the value at the specified index
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetget.php ArrayAccess::offsetGet()
     *
     * @throws \OutOfBoundsException If <code>$index</code> is not a valid index.
     *
     * @param mixed $index
     *            The index with the value.
     * @return mixed The value at the specified index or <code>FALSE</code>.
     */
    final public function offsetGet($index)
    {
        // Is $index numeric?
        if (is_numeric($index)) {

            // Loop through child nodes looking for index
            for ($current = $this->firstChild, $i = 0; !! $current && $i < $index; $current = $current->nextSibling, $i++);

            // Check results
            if (!! $current)
                return $current;

            // Invalid $index
            else
                trigger_error(sprintf('Undefined index %s[%s]', get_class($this), @strval($index)), E_USER_NOTICE);
        }

        // Invalid $index
        else
            throw new OutOfBoundsException('Element[index]: index must be an integer');

        // Error
        return null;
    }

    /**
     * Sets the value at the specified index to newval
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetset.php ArrayAccess::offsetSet()
     *
     * @throws \UnexpectedValueException If <code>$newval</code> is not a <code>DOMNode</code> object.
     * @throws \OutOfBoundsException If <code>$index</code> is not a valid index.
     *
     * @param mixed $index
     *            The index being set.
     * @param mixed $newval
     *            The new value for the index.
     */
    final public function offsetSet($index, $newval)
    {
        // Is Node writable?
        if ($this->ownerDocument instanceof DOMDocument) {

            // Is $newval an instance of DOMNode?
            if ($newval instanceof DOMNode) {

                // Does node belong to current document?
                if ($newval->ownerDocument !== $this->ownerDocument) {

                    // Import node
                    $newval = $this->ownerDocument->importNode($newval);
                }

                // Is #index null
                if (is_null($index)) {
                    // Append $newval
                    return $this->appendChild($newval);
                }

                // Is $index numeric?
                elseif (is_numeric($index)) {

                    // Loop through child nodes looking for index
                    for ($current = $this->firstChild, $i = 0; ! ! $current && $i < intval($index); $current = $current->nextSibling, $i ++);

                    // Check results
                    if ($current instanceof DOMNode) {

                        // eplace node
                        return $this->replaceChild($newval, $current);
                    }

                    // Invalid $index
                    else
                        trigger_error(sprintf('Undefined index %s[%s]', get_class($this), @strval($index)), E_USER_NOTICE);
                }

                // Invalid $index
                else
                    throw new OutOfBoundsException('Element[index]: index must be an integer');
            }

            // Invalid $newval
            else
                throw new UnexpectedValueException(sprintf('DOMNode object expected. %s given.', is_object($newval) ? get_class($newval) : gettype($newval)));
        }

        // Node is readonly
        else
            throw new Exception('Cannot modify readonly element');
    }

    /**
     * Unsets the value at the specified index
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetunset.php ArrayAccess::offsetUnset()
     *
     * @param mixed $index
     *            The index being unset.
     */
    final public function offsetUnset($index)
    {
        // Is Node writable?
        if ($this->ownerDocument instanceof DOMDocument) {

            // Is $index numeric?
            if (is_numeric($index)) {

                // Loop through child nodes looking for index
                for ($current = $this->firstChild, $i = 0; ! ! $current && $i < intval($index); $current = $current->nextSibling, $i ++);

                // Check results
                if ($current instanceof DOMNode) {
                    // Delete node
                    return $this->removeChild($current);
                }
            }

            // Invalid $index
            else
                throw new OutOfBoundsException('Element[index]: index must be an integer');
        }

        // Node is readonly
        else
            throw new Exception('Cannot modify readonly element');
    }

    /**
     * Get the number of public properties in the ArrayObject
     *
     * @api BLW
     * @since 0.2.0
     * @link http://www.php.net/manual/en/countable.count.php Countable::count()
     *
     * @return int The number of public properties in the ArrayObject.
     */
    public function count()
    {
        return $this->childNodes->length;
    }

#############################################################################################
# IteratorAggregate Trait
#############################################################################################

    /**
     * Create a new iterator from an ArrayObject instance
     *
     * @link http://www.php.net/manual/en/iteratoraggregate.getiterator.php IteratorAggregate::getIterator()
     *
     * @return \RecursiveArrayIterator An instance implementing <code>Iterator</code>.
     */
    public function getIterator()
    {
        return new IteratorIterator($this->childNodes);
    }

#############################################################################################
# Element Trait
#############################################################################################

    /**
     * All objects must have a string representation.
     *
     * @link http://www.php.net/manual/en/language.oop5.magic.php#object.tostring __toString()
     *
     * @return string $this
     */
    public function __toString()
    {
        return $this->getOuterHTML() ?: '';
    }

    /**
     * Get the ID of the object.
     *
     * @return string Current ID.
     */
    public function getID()
    {
        return $this->getAttribute('id');
    }

    /**
     * Changes the ID of the current object.
     *
     * @param string $ID
     *            New ID.
     * @return int Returns a <code>IDataMapper</code> status code.
     */
    public function setID($ID)
    {
        // Is $ID a string?
        if (is_string($ID) ?  : is_callable(array(
            $ID,
            '__toString'
        ))) {

            // Update ID
            return $this->setAttribute('id', strval($ID)) instanceof DOMAttr ? IDataMapper::UPDATED : IDataMapper::INVALID;
        }

        // Error
        return IDataMapper::INVALID;
    }

    /**
     * Dynamic properties.
     *
     * @param string $name
     *            Label of property to search for.
     * @return mixed Returns <code>null</code> if not found.
     */
    public function __get($name)
    {
        switch ($name) {
            // IIterable
            case 'Parent':
                return $this->_Parent;
            case 'ID':
                return $this->getID();
            // Element
            case 'Document':
                return $this->getDocument($this);
            case 'innerHTML':
                return $this->getInnerHTML();
            case 'outerHTML':
                return $this->getOuterHTML();
            // Undefined property
            default:
                trigger_error(sprintf('Undefined property %s::$%s', get_class($this), $name), E_USER_NOTICE);
        }

        // Default value
        return null;
    }

    /**
     * Dynamic properties.
     *
     * @param string $name
     *            Label of property to search for.
     * @return bool Returns a <code>TRUE</code> if property exists. <code>FALSE</code> otherwise.
     */
    public function __isset($name)
    {
        switch ($name) {
            // IIterable
            case 'Parent':
                return $this->_Parent !== null;
            case 'ID':
                return $this->getID() !== null;
            // Element
            case 'Document':
                return $this->ownerDocument !== null;
            case 'innerHTML':
                return true;
            case 'outerHTML':
                return true;
            // Undefined property
            default:
                false;
        }
    }

    /**
     * Dynamic properties.
     *
     * @param string $name
     *            Label of property to set.
     * @param mixed $value
     *            Value of property.
     * @return bool Returns a <code>IDataMapper</code> status code.
     */
    public function __set($name, $value)
    {
        // Try to set property
        switch ($name) {
            // IIterable
            case 'ID':
                $result = $this->setID($value);
                break;
            case 'Parent':
                $result = $this->setParent($value);
                break;
            // Element
            case 'Document':
                $result = IDataMapper::READONLY;
                break;
            case 'innerHTML':
                $result = $this->setInnerHTML($value) ? IDataMapper::UPDATED : IDataMapper::INVALID;
                break;
            case 'outerHTML':
                $result = $this->setOuterHTML($value) ? IDataMapper::UPDATED : IDataMapper::INVALID;
                break;
            // Undefined property
            default:
                $result = IDataMapper::UNDEFINED;
        }

        // Check results
        switch ($result) {
            // Readonly property
            case IDataMapper::READONLY:
            case IDataMapper::ONESHOT:
                trigger_error(sprintf('Cannot modify readonly property: %s::$%s', get_class($this), $name), E_USER_NOTICE);
                break;
            // Invalid value for property
            case IDataMapper::INVALID:
                trigger_error(sprintf('Invalid value %s for property: %s::$%s', @print_r($value, true), get_class($this), $name), E_USER_NOTICE);
                break;
            // Undefined property property
            case IDataMapper::UNDEFINED:
                trigger_error(sprintf('Tried to modify non-existant property: %s::$%s', get_class($this), $name), E_USER_ERROR);
                break;
        }
    }

    /**
     * Unmap dynamic properties from DataMapper.
     *
     * @param string $name
     *            Label of dynamic property. (case sensitive)
     * @return bool Returns a <code>TRUE</code> if property exists. <code>FALSE</code> otherwise.
     */
    public function __unset($name)
    {
        // Try to set property
        switch ($name) {
            // ISerializable
            case 'Status':
                $result = $this->clearStatus();
                break;
            // IIterable
            case 'Parent':
                $result = $this->clearParent();
                break;
            // Undefined property
            default:
                $result = IDataMapper::UNDEFINED;
        }
    }

#############################################################################################

}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
