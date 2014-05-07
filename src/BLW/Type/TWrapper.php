<?php
/**
 * TWrapper.php | Dec 27, 2013
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
 * @version GIT 0.2.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
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
 * Wrapper pattern Trait.
 *
 * <h3>About</h3>
 *
 * <p>All decorator and adaptor objects must either implement
 * this Trait, use the <code>BLW\Type\IWrapper</code> Interface
 * or extend the <code>\BLW\Type\AWrapper</code> class.</p>
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
 * @api BLW
 * @since 0.1.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 *
 * @property mixed $_Component [protected] Pointer to component of current object.
 */
trait TWrapper
{
    use\BLW\Type\TSerializable;
    use\BLW\Type\TComponentMapable;
    use\BLW\Type\TIterable;

    /**
     * Constructor
     *
     * @param mixed $Component
     *            Component of wrapper class.
     * @param int $flags
     *            object creation flags.
     */
    public function __construct($Component, $flags = IWrapper::WRAPPER_FLAGS)
    {
        $this->_Component = &$Component;
    }

    /**
     * Creates a new instance of the object (used for chaining).
     *
     * @param mixed $Argument
     *            [optional] Constructor argument.
     * @param
     *            ...
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
                if (isset($this->_Component->{$name}))
                    return $this->_Component->{$name};

                // Undefined property
                else
                    trigger_error(sprintf('Undefined property: %s::$%s', get_class($this), $name), E_USER_NOTICE);
        }

        // Default
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
     * @return bool Returns a <code>IDataMapper</code> status code.
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
     * Dynamic properties.
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
            // IComponentMapable
            case 'Component':
                trigger_error(sprintf('Cannot modify readonly property: %s::$%s', get_class($this), $name), E_USER_NOTICE);
                break;
            // Undefined property
            default:
                $result = IDataMapper::UNDEFINED;
        }
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd