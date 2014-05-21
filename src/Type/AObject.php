<?php
/**
 * AObject.php | Nov 29, 2013
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
 * Core BLW object abstract class.
 *
 * <h3>About</h3>
 *
 * <p>All Objects must either extend this class,
 * use the <code>BLW\Type\TObject</code> trait or
 * implement the <code>\BLW\Type\IObject</code> interface.</p>
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +------------------+
 * | OBJECT                                            |<------| SERIALIZABLE     |
 * +---------------------------------------------------+       | ================ |
 * | _DataMapper:  IDataMapper                         |       | Serializable     |
 * | _Status:      int                                 |       +------------------+
 * | _ID:          string                              |<------| DATAMAPABLE      |
 * | #ID:          _ID                                 |       +------------------+
 * | #Status:      _Status                             |<------| ITERABLE         |
 * | ####:         Dynamic properties                  |       +------------------+
 * +---------------------------------------------------+
 * | __constructor():                                  |
 * |                                                   |
 * | $DataMapper:  IDataMapper                         |
 * | $ID:          string                              |
 * | $flags:       int                                 |
 * +---------------------------------------------------+
 * | getInstance(): __construct()                      |
 * +---------------------------------------------------+
 * | createID(): string                                |
 * |                                                   |
 * | $Input:  null|int|string                          |
 * +---------------------------------------------------+
 * | getID() string                                    |
 * +---------------------------------------------------+
 * | setID() IDataMapper::Status                       |
 * |                                                   |
 * | $ID:  string                                      |
 * +---------------------------------------------------+
 * | __toString(): string                              |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\Core
 * @api     BLW
 * @since 0.1.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 *
 * @property string $ID [dynamic] Invokes getID() and setID().
 * @property string $toString [readonly] Invokes __toString().
 */
abstract class AObject extends \BLW\Type\ASerializable implements \BLW\Type\IObject
{

#############################################################################################
# DataMapable Trait
#############################################################################################

    /**
     * Pointer to DataMapper of the object.
     *
     * @var $_DataMapper \BLW\Type\IDataMapper
     */
    protected $_DataMapper = null;

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
# Object Trait
#############################################################################################

    /**
     * Current ID of object.
     *
     * @see \BLW\Type\IObject::getID() IObject::getID()
     * @see \BLW\Type\IObject::setID() IObject::setID()
     *
     * @var string $_ID
     */
    protected $_ID = '';

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
# Object Trait
#############################################################################################

    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$ID</code> is not a string.
     *
     * @param \BLW\Type\IDataMapper $DataMapper
     *            Used to map dynamic properties in object.
     * @param string $ID
     *            ID of object distinguishing it from another.
     * @param integer $flags
     *            Object creation flags.
     */
    public function __construct(IDataMapper $DataMapper = null, $ID = null, $flags = IObject::OBJECT_FLAGS)
    {
        // DataMapper
        $this->_DataMapper = $DataMapper ?: new ArrayObject();

        // Objectid

        // Is $ID NULL
        if ($ID === null) {
            $this->_ID = $this->createID();

        // Is $ID scalar
        } elseif (is_scalar($ID) ?: is_callable(array(
            $ID,
            '__toString'
        ))) {
            $this->_ID = strval($ID);

        // $ID is invalid
        } else {
            throw new InvalidArgumentException(1);
        }
    }

    /**
     * Creates a new instance of the object.
     * (used for chaining).
     *
     * @param \BLW\Type\IDataMapper $DataMapper
     *            Used to map dynamic properties in object.
     * @param string $ID
     *            ID of object distinguishing it from another.
     * @param integer $flags
     *            Object creation flags.
     * @return \BLW\Type\IObject Returns a new instance of the class.
     */
    public static function getInstance(IDataMapper $DataMapper, $ID = null, $flags = IObject::OBJECT_FLAGS)
    {
        return new static($DataMapper, $ID, $flags);
    }

    /**
     * Creates a valid Object ID / Label / Name.
     *
     * @throws \BLW\Model\InvalidArgumentException If $Input is not scalar.
     *
     * @param string|int|null $Input
     *            Input can be biased to help regenerate ID's.
     * @return string Returns empty string on error.
     */
    public static function createID($Input = null)
    {
        static $Salt = 'BLW_';

        // NULL $Input
        if ($Input === null) {
            // Create new id
            return $Salt . md5($Salt . microtime());

        // Scalar $Input
        } elseif (is_scalar($Input) ?: is_callable(array(
            $Input,
            '__toString'
        ))) {
            // Regenerate id
            return $Salt . md5($Salt . strval($Input));

        // Invalid $Input
        } else {
            throw new InvalidArgumentException(0);
        }
    }

    /**
     * Fetches the current ID of the object.
     *
     * @return string Current ID of the object.
     */
    public function getID()
    {
        return $this->_ID;
    }

    /**
     * Changes the ID of the current object.
     *
     * @param string $ID
     *            New ID.
     * @return integer Returns a <code>IDataMapper</code> status code.
     */
    public function setID($ID)
    {
        // Check if $ID is scalar?
        if (! is_scalar($ID) && ! is_callable(array(
            $ID,
            '__toString'
        ))) {
            // Invalid ID
            return IDataMapper::INVALID;
        }

        // Update ID
        $this->_ID = strval($ID);

        // Done
        return IDataMapper::UPDATED;
    }

    /**
     * Variable functions.
     *
     * <h4>Note:</h4>
     *
     * <p>Raises a <b>Warning</b> if method is not found.</p>
     *
     * <hr>
     *
     * @param string $name
     *            Label of dynamic method. (case sensitive)
     * @param array $arguments
     *            Arguments to pass to method.
     * @return mixed Returns the result of the function. Returns <code>null</code> on failure.
     */
    public function __call($name, array $arguments)
    {
        // Does property exist? Is it callable?
        if (isset($this->{$name}) && is_callable($this->{$name})) {

            // Call it
            return call_user_func_array($this->{$name}, $arguments);
        }

        // Property does not exist or is uncallable
        else {
            trigger_error(sprintf('Cannot call non-existant method %s::%s()', get_class($this), $name), E_USER_WARNING);
        }
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
            // ISerializable
            case 'Status':
                return $this->_Status;
            case 'Serializer':
                return $this->getSerializer();
            // IIterable
            case 'Parent':
                return $this->_Parent;
            case 'ID':
                return $this->getID();
            default:

                // IDataMapable
                if ($this->_DataMapper->offsetExists($name)) {
                    return $this->_DataMapper->offsetGet($name);

                // Undefined property
                } else {
                    trigger_error(sprintf('Undefined property %s::$%s', get_class($this), $name), E_USER_NOTICE);
                }
        }
    }

    /**
     * Dynamic properties.
     *
     * @param string $name
     *            Label of property to search for.
     * @return boolean Returns a <code>TRUE</code> if property exists. <code>FALSE</code> otherwise.
     */
    public function __isset($name)
    {
        switch ($name) {
            // ISerializable
            case 'Status':
            case 'Serializer':
                return true;
            // IIterable
            case 'Parent':
                return $this->_Parent !== null;
            case 'ID':
                return $this->getID() !== null;
            // IDataMapable
            default:
                return $this->_DataMapper->offsetExists($name);
        }
    }

    /**
     * Dynamic properties.
     *
     * @param string $name
     *            Label of property to set.
     * @param mixed $value
     *            Value of property.
     */
    public function __set($name, $value)
    {
        // Try to set property
        switch ($name) {
            // ISerializable
            case 'Status':
            case 'Serializer':
                $result = IDataMapper::READONLY;
                break;
            // IIterable
            case 'ID':
                $result = $this->setID($value);
                break;
            case 'Parent':
                $result = $this->setParent($value);
                break;
            // IDataMapable
            default:
                $result = $this->_DataMapper->offsetSet($name, $value);
        }

        // Check results
        if (list($Message, $Level) = ADataMapper::getErrorInfo($result, get_class($this), $name)) {
            trigger_error($Message, $Level);
        }
    }

    /**
     * Unmap dynamic properties from DataMapper.
     *
     * @param string $name
     *            Label of dynamic property. (case sensitive)
     */
    public function __unset($name)
    {
        // Try to unset property
        switch ($name) {
            // ISerializable
            case 'Status':
                $this->clearStatus();
                break;
            // IIterable
            case 'Parent':
                $this->clearParent();
                break;
            // IDataMapable
            default:
                $this->_DataMapper->offsetUnset($name);
        }
    }

    /**
     * All objects must have a string representation.
     *
     * <h4>Note:</h4>
     *
     * <p>Default is the serialized form of the object.</p>
     *
     * <hr>
     *
     * @return string String value of object.
     */
    public function __toString()
    {
        return $this->serialize();
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
