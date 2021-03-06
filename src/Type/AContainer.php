<?php
/**
 * AContainer.php | Jan 26, 2014
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
 * @package BLW\Core
 * @version GIT: 0.2.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type;

use ArrayObject;
use UnexpectedValueException;
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
 * Core container interface.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +-----------------+
 * | CONTAINER                                         |<------| ArrayObject     |
 * +---------------------------------------------------+       +-----------------+
 * | __construct()                                     |       | SERIALIZABLE    |
 * |                                                   |       | =============== |
 * | .....: class|type                                 |       | Serializable    |
 * +---------------------------------------------------+       +-----------------+
 * | validateValue(): bool                             |       | ITERABLE        |
 * |                                                   |       +-----------------+
 * | $value:  mixed                                    |
 * +---------------------------------------------------+
 * | __toString(): string                              |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\Core
 * @api     BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property string[] $_Types Array of acceptable classes / types the container can contain.
 */
abstract class AContainer extends \ArrayObject implements \BLW\Type\IContainer
{

#############################################################################################
# Serializable Trait
#############################################################################################

    /**
     * Current status flag of the object.
     *
     * @var int $Status
     */
    protected $_Status = 0;

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
# Container Trait
#############################################################################################

    /**
     * Array of acceptable classes / types the container can contain.
     *
     * @var string[] $_Types
     */
    protected $_Types = array();

#############################################################################################




#############################################################################################
# Serializable Trait
#############################################################################################

    /**
     * Generate $Serializer dynamic property.
     *
     * <h4>Note:</h4>
     *
     * <p>I decided to use a global state because the serializer is
     * needed during unserialization so it is simply imposible to pass
     * it as an argument to <code>unserialize()</code>.
     *
     * <p>Please create a serializer and serialize the class manually.</p>
     *
     * <pre>ISerializable::serializeWith(ISerializer)</pre>
     *
     * <hr>
     *
     * @return \BLW\Type\ISerializer $this->Serializer
     */
    public function getSerializer()
    {
        global $BLW_Serializer;

        // @codeCoverageIgnoreStart

        if (! $BLW_Serializer instanceof ISerializer) {
            $BLW_Serializer = new \BLW\Model\Serializer\PHP;
        }

        // @codeCoverageIgnoreEnd
        return $BLW_Serializer;
    }

    /**
     * Clears the status flag of the current object.
     *
     * @return integer Returns a <code>IDataMapper</code> status code.
     */
    public function clearStatus()
    {
        // Reset Status
        $this->_Status = 0;

        // Done
        return IDataMapper::UPDATED;
    }

    /**
     * Return a string representation of the object.
     *
     * @param ISerializer $Serializer
     *            Serializer handler to use.
     * @param integer $flags
     *            Serialization flags.
     * @return string $this
     */
    final public function serializeWith(ISerializer $Serializer, $flags = ISerializer::SERIALIZER_FLAGS)
    {
        return $Serializer->encode($this, @intval($flags));
    }

    /**
     * Return an object state from it serialized string.
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Data</code> is not a string.
     *
     * @param ISerializer $Serializer
     *            Serializer handler to use.
     * @param string $Data
     *            Serialized data.
     * @param integer $flags
     *            De-Serialization flags.
     * @return boolean Returns <code>TRUE</code> on success and false on failure.
     */
    final public function unserializeWith(ISerializer $Serializer, $Data, $flags = ISerializer::SERIALIZER_FLAGS)
    {
        // Is $Data not a string?
        if (! is_string($Data)) {
            throw new InvalidArgumentException(1);
        }

        return $Serializer->decode($this, $Data, @intval($flags));
    }

    /**
     * Hook that is called just before an object is serialized.
     */
    public function doSerialize()
    {
    }

    /**
     * Hook that is called just after an object is unserialized.
     */
    public function doUnSerialize()
    {
    }

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
     * @return integer Returns a <code>DataMapper</code> status code.
     */
    final public function setParent($Parent)
    {
        // Make sur object is not a parent of itself
        if ($Parent === $this) {
            return IDataMapper::INVALID;

        // Make sure parent is valid
        } elseif (! $Parent instanceof IObject && ! $Parent instanceof IContainer && ! $Parent instanceof IObjectStorage && ! $Parent instanceof IWrapper) {
            return IDataMapper::INVALID;

        // Make sure parent is not already set
        } elseif (! $this->_Parent instanceof IObject && ! $this->_Parent instanceof IContainer && ! $this->_Parent instanceof IObjectStorage) {

            // Update parent
            $this->_Parent = $Parent;

            return IDataMapper::UPDATED;
        }

        // Else dont update parent
        else {
            return IDataMapper::ONESHOT;
        }
    }

    /**
     * Clears parent of the current object.
     *
     * @access private
     * @internal For internal use only.
     *
     * @return integer Returns a <code>DataMapper</code> status code.
     */
    final public function clearParent()
    {
        $this->_Parent = null;

        return IDataMapper::UPDATED;
    }

#############################################################################################
# Container Trait
#############################################################################################

    /**
     * Sets the value at the specified index to newval
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetset.php ArrayAccess::offsetSet()
     *
     * @param mixed $index
     *            The index being set.
     * @param mixed $newval
     *            The new value for the index.
     */
    public function offsetSet($index, $newval)
    {
        if ($newval instanceof IIterable || $this->validateValue($newval)) {
            parent::offsetSet($index, $newval);

        } else {
            throw new UnexpectedValueException(sprintf('Invalid value: (%s).', is_object($newval) ? get_class($newval) : gettype($newval)));
        }
    }

    /**
     * Appends the value
     *
     * @param mixed $value
     *            The value being appended.
     */
    public function append($value)
    {
        // Is $value valid
        if ($value instanceof IIterable || $this->validateValue($value)) {
            parent::append($value);

        // $value is not valid
        } else {
            throw new UnexpectedValueException(sprintf('Invalid value: (%s).', is_object($value) ? get_class($value) : gettype($value)));
        }
    }

    /**
     * Constructor
     *
     * @param ...
     */
    public function __construct()
    {
        // Parent constructor
        parent::__construct(array(), IContainer::FLAGS, IContainer::ITERATOR);

        // Parse Types
        if (func_num_args()) {
            $this->_Types = @array_map('strval', func_get_args());

            // Sort types
            sort($this->_Types);

        // Default
        } else {
            $this->_Types[] = IContainer::DEFAULT_TYPE;
        }
    }

    /**
     * Determines if value is a valid value for the container.
     *
     * @param mixed $value
     *            Value to test.
     * @return boolean Returns <code>TRUE</code> if valid. <code>FALSE</code> otherwise.
     */
    final public function validateValue($value)
    {
        // Loop through each type
        foreach ($this->_Types as $Type) {
            // Validate against type
            if ($value instanceof $Type || gettype($value) == $Type) {
                return true;
            }
        }

        // Invalid value
        return false;
    }

    /**
     * Filters items based on a callback.
     *
     * <h4>Format:</h4>
     *
     * <pre>bool function (mixed $Item, scalar $Key)</pre>
     *
     * <hr>
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Callback</code> is not callable.
     *
     * @param callable $Callback
     *            Filtering function.
     */
    final public function filter($Callback)
    {
        $Filtered = array();

        // $Callback is not callable
        if (! is_callable($Callback)) {
            throw new InvalidArgumentException(0);
        }

        // Call calback against each item
        foreach ($this as $k => $v) {
            if (call_user_func($Callback, $v, $k)) {
                $Filtered[] = $v;
            }
        }

        // Done
        return $Filtered;
    }

    /**
     * Calls an anonymous function on each item of container.
     *
     * <h4>Format:</h4>
     *
     * <pre>mixed function (mixed $Item, scalar $Key)</pre>
     *
     * <hr>
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Callback</code> is not callable.
     *
     * @param callable $Callback
     *            Function to call.
     * @param array $return
     *            [optional] Array of results.
     * @return \BLW\Type\IContainer $this
     */
    final public function each($Callback, &$return = null)
    {
        $return = array();

        // Is $Callback callable
        if (! is_callable($Callback)) {
            throw new InvalidArgumentException(0);
        }

        // Loop through each item
        foreach ($this as $k => $v) {
            // Pass each item to callback
            $return[$k] = call_user_func($Callback, $v, $k);
        }

        // Done
        return $this;
    }

    /**
     * Call an anonymous function on object and all its descendants.
     *
     * <h4>Format:</h4>
     *
     * <pre>mixed function (mixed $Item, scalar $Key)</pre>
     *
     * <hr>
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Callback</code> is not callable.
     *
     * @param callable $Callback
     *            Function to call.
     * @param array $return
     *            [optional] Array of results.
     * @return \BLW\Type\IContainer $this
     */
    final public function walk($Callback, array &$return = null)
    {
        $return = $return ?: array();

        // Is $Callback callable
        if (! is_callable($Callback)) {
            throw new InvalidArgumentException(0);
        }

        // Loop through all items
        foreach ($this as $k => $v) {
            // Item is an instance of IContainer?
            if ($v instanceof IContainer) {
                // Call child
                $v->walk($Callback, $return[$k]);

            // Default
            } else {
                $return[$k] = call_user_func($Callback, $v, $k);
            }
        }

        // Current object
        $return['self'] = call_user_func($Callback, $this, 'self');

        // Done
        return $this;
    }

    /**
     * All objects must have a string representation.
     *
     * @return string $this
     */
    public function __toString()
    {
        $items = function (IContainer $o) {

            $items = array();

            // Loop through each item
            foreach ($o as $v) {

                // Is item scalar?
                if (is_scalar($v) ?: is_callable(array(
                    $v,
                    '__toString'
                ))) {
                    // String value of item
                    $items[] = strval($v);

                // Is item an object
                } elseif (is_object($v)) {
                    // Class of item
                    $items[] = get_class($v);

                // Default
                } else {
                    // Type of item
                    $items[] = gettype($v);
                }
            }

            // Done
            return implode(IContainer::GLUE, $items);
        };

        return sprintf('[IContainer:%s]', $items($this));
    }

    /**
     * Get the ID of the object.
     *
     * @return string Current ID.
     */
    public function getID()
    {
        return md5(strval($this));
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
