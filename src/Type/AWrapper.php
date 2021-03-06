<?php
/**
 * AWrapper.php | Dec 27, 2013
 *
 * @filesource
 * @license MIT
 * @copyright Copyright (c) 2013-2018, mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 *
 * @filesource
 * @copyright mAsT3RpEE's Zone
 * @license MIT
 */

/**
 *
 * @package BLW\Core
 * @version GIT: 0.2.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type;

use ReflectionClass;
use BadMethodCallException;

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
 * Wrapper pattern abstract class.
 *
 * <h3>About</h3>
 *
 * <p>All decorator and adaptor objects must either extend
 * this class, use the <code>BLW\Type\IWrapper</code> Interface
 * or use the <code>\BLW\Type\TWrapper</code> trait.</p>
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +------------------+
 * | WRAPPER                                           |<------| SERIALIZABLE     |
 * +---------------------------------------------------+       | ================ |
 * | _Component:  mixed                                |       | Serializable     |
 * | ###:         Import component properties          |       +------------------+
 * +---------------------------------------------------+       | COMPONENTMAPABLE |
 * | __construct():                                    |       +------------------+
 * |                                                   |       | ITERABLE         |
 * | $Component:  mixed                                |       +------------------+
 * | $flags:      int                                  |
 * +---------------------------------------------------+
 * | getInstance(): IWrapper                           |
 * |                                                   |
 * | $Component:  mixed                                |
 * | $flags:      int                                  |
 * +---------------------------------------------------+
 * | ###(): Import component functions()               |
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
 * @property mixed $_Component [protected] Pointer to component of current object.
 */
abstract class AWrapper extends \BLW\Type\ASerializable implements \BLW\Type\IWrapper
{

#############################################################################################
# ComponentMapable Trait
#############################################################################################

    /**
     * Pointer to component of the object.
     *
     * @var mixed $_Component
     */
    protected $_Component;

#############################################################################################
# Iterable Trait
#############################################################################################

    /**
     *
     * @var \BLW\Type\IObject $Parent Pointer to current parent of object.
     */
    protected $_Parent = null;

#############################################################################################




#############################################################################################
# ComponentMapable Trait
#############################################################################################

    /**
     * Import component methods.
     *
     * @throws \BadMethodCallException If method is not found.
     *
     * @param string $name
     *            Label of method to look for.
     * @param array $arguments
     *            Arguments to pass to method.
     * @return mixed Component method return value.
     */
    public function __call($name, array $arguments)
    {
        // Import component methods
        if (is_callable(array(
            $this->_Component,
            $name
        ))) {
            return call_user_func_array(array(
                $this->_Component,
                $name
            ), $arguments);
        }

        // Vaiable functions
        elseif (isset($this->{$name}) ? is_callable($this->{$name}) : false) {
            return call_user_func_array($this->{$name}, $arguments);
        }

        // Undefined method
        else {
            throw new BadMethodCallException(sprintf('Call to undefined method `%s::%s()`.', get_class($this), $name));
        }
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

        // Else dont update parent
        } else {
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

    /**
     * Get the ID of the object.
     *
     * @return string Current ID.
     */
    abstract public function getID();

#############################################################################################
# Wrapper Trait
#############################################################################################

    /**
     * Constructor
     *
     * @param mixed $Component
     *            Component of wrapper class.
     * @param integer $flags
     *            object creation flags.
     */
    public function __construct($Component, $flags = IWrapper::WRAPPER_FLAGS)
    {
        $this->_Component = &$Component;
    }

    /**
     * Creates a new instance of the object (used for chaining).
     *
     * @param ...
     * @return \BLW\Type\IWrapper Returns a new instance of the class.
     */
    public static function getInstance()
    {
        // Create class
        $Class = new ReflectionClass(get_called_class());

        return $Class->newInstanceArgs(func_get_args());
    }

    /**
     * All objects must have a string representation.
     *
     * @return string String value of object.
     */
    public function __toString()
    {
        return sprintf('[%s:%s]', get_class($this), is_object($this->_Component) ? get_class($this->_Component) : gettype($this->_Component));
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
            // IComponentMapable
            case 'Component':
                return $this->_Component;
            default:

                // Component property
                if (isset($this->_Component->{$name})) {
                    return $this->_Component->{$name};

                // Undefined property
                } else {
                    trigger_error(sprintf('Undefined property: %s::$%s', get_class($this), $name), E_USER_NOTICE);
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

            // IComponentMapable
            case 'Component':
                return $this->_Component !== null;
            default:
                return isset($this->_Component->{$name});
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
        switch ($name) {
            // ISerializable
            case 'Status':
            case 'Serializer':
            // IIterable
            case 'ID':
                $result = IDataMapper::READONLY;
                break;
            case 'Parent':
                $result = $this->setParent($value);
                break;
            // IComponentMapable
            case 'Component':
                $result = IDataMapper::READONLY;
                break;
            default:

                // Try to set component property
                try {
                    $this->_Component->{$name} = $value;
                    $result = IDataMapper::UPDATED;
                }

                // Error
                catch (\Exception $e) {
                    $result = IDataMapper::UNDEFINED;
                }
        }

        // Check results
        if (list($Message, $Level) = ADataMapper::getErrorInfo($result, get_class($this), $name)) {
            trigger_error($Message, $Level);
        }
    }

    /**
     * Dynamic properties.
     *
     * @param string $name
     *            Label of dynamic property. (case sensitive)
     */
    public function __unset($name)
    {
        // Try to set property
        switch ($name) {
            // ISerializable
            case 'Status':
                $this->clearStatus();
                break;
            // IIterable
            case 'Parent':
                $this->clearParent();
                break;
            // IComponentMapable
            default:
                unset($this->_Component->{$name});
        }
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
