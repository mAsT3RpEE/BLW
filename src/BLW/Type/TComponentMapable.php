<?php
/**
 * TComponentMapable.php | Feb 10, 2014
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
 * Trait for all objects that immport component methods / properties.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+
 * | COMPONENTMAPABLE                                  |
 * +---------------------------------------------------+
 * | _Component: object                                |
 * +---------------------------------------------------+
 * | __call(): _Component->###()                       |
 * |           Variable functions                      |
 * |                                                   |
 * | $name:       string                               |
 * | $arguments:  array                                |
 * +---------------------------------------------------+
 * | __get(): _Component->###                          |
 * |                                                   |
 * | $name:  string                                    |
 * +---------------------------------------------------+
 * | __set(): _Component->###                          |
 * |                                                   |
 * | $name:   string                                   |
 * | $value:  mixed                                    |
 * +---------------------------------------------------+
 * | __isset(): _Component->###                        |
 * |                                                   |
 * | $name:  string                                    |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\Core
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
trait TComponentMapable
{

    /**
     * Pointer to component of the object.
     *
     * @var mixed $_Component
     */
    protected $_Component;

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
        else
            throw new BadMethodCallException(sprintf('Call to undefined method `%s::%s()`.', get_class($this), $name));

        // Default
        return null;
    }

    /**
     * Import component properties.
     *
     * <h4>Note:</h4>
     *
     * <p>Raises a <b>Warning</b> if property is not found.</p>
     *
     * <hr>
     *
     * @param string $name
     *            Label of property to search for.
     * @return mixed Returns <code>null</code> if not found.
     */
    public function __get($name)
    {
        // IComponentMapable
        if (isset($this->_Component->{$name})) {
            return $this->_Component->{$name};
        }

        // Undefined property
        else
            trigger_error(sprintf('Undefined property: %s::$%s', get_class($this), $name), E_USER_NOTICE);

        // Default
        return null;
    }

    /**
     * Import component properties.
     *
     * @param string $name
     *            Label of property to search for.
     * @return bool Returns a <code>TRUE</code> if property exists. <code>FALSE</code> otherwise.
     */
    public function __isset($name)
    {
        // IComponentMapable
        return isset($this->_Component->{$name});
    }

    /**
     * Import component properties.
     *
     * @param string $name
     *            Label of property to set.
     * @param mixed $value
     *            Value of property.
     * @return bool Returns a <code>IDataMapper</code> status code.
     */
    public function __set($name, $value)
    {
        // Try to set component property
        try {
            $this->_Component->{$name} = $value;
            $result = IDataMapper::UPDATED;
        }

        // Error
        catch (\Exception $e) {
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
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
