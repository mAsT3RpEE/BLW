<?php
/**
 * ADataMapper.php | Feb 10, 2014
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

use Closure;
use ArrayObject;
use UnexpectedValueException;
use ReflectionMethod;

use BLW\Model\InvalidArgumentException;

use Jeremeamia\SuperClosure\SerializableClosure;


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
 * Abstract class for all dynamic property / method mapper objects.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +--------------------+
 * | DATAMAPPER                                        |<------| ArrayAccess        |
 * +---------------------------------------------------+       +--------------------+
 * | READONLY                                          |<------| IteratorAggregate  |
 * | WRITEONLY                                         |       +--------------------+
 * | PRIVATE                                           |<------| FACTORY            |
 * | UPDATED                                           |       | ================== |
 * | ONESHOT                                           |       | createRead         |
 * | INVALID                                           |       | createWrite        |
 * | UNDEFINED                                         |       +--------------------+
 * +---------------------------------------------------+
 * | createRead(): Closure                             |
 * |                                                   |
 * | $Variable:  mixed                                 |
 * +---------------------------------------------------+
 * | createWrite(): Closure                            |
 * |                                                   |
 * | $Variable:  mixed                                 |
 * +---------------------------------------------------+
 * | __construct():                                    |
 * |                                                   |
 * | $data:  array                                     |
 * +---------------------------------------------------+
 * | __loadFields(): bool                              |
 * |                                                   |
 * | $fields:  array()                                 |
 * +---------------------------------------------------+
 * | __setField(): bool                                |
 * |                                                   |
 * | $Name:   string                                   |
 * | $Read:   callable                                 |
 * | $Write:  callable                                 |
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
abstract class ADataMapper extends \ArrayObject implements \BLW\Type\IDataMapper
{

#############################################################################################
# Iterator Trait
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
    public function offsetGet($index)
    {
        // Does the $read function exist?
        $callables = @parent::offsetGet($index);

        // Does read function exist?
        if (is_callable($callables[0])) {

            // Return $read function
            return call_user_func($callables[0]);
        }

        // Undefined field
        return null;
    }

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
        // Does the $read function exist?
        $callables = @parent::offsetGet($index);

        // Do write function exist?
        if (is_callable($callables[1])) {

            // Return $write function
            return call_user_func($callables[1], $newval);
        }

        // Undefined field
        return IDataMapper::UNDEFINED;
    }

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
            new ReflectionMethod(get_called_class(), 'createRead'),
            new ReflectionMethod(get_called_class(), 'createWrite')
        );
    }

    /**
     * Creates a closure to automatically read the value of a variable.
     *
     * @see \BLW\Type\IDataMapper::__setField()
     *
     * @param mixed $variable
     *            Variable to turn into a closure.
     * @return \Closure Generated function.
     */
    public static function createRead(&$variable)
    {
        // Creates a closure that returns the current value of a variable
        return function () use(&$variable)
        {
            return $variable;
        };
    }

    /**
     * Creates a closure to automatically read the value of a variable.
     *
     * @see \BLW\Type\IDataMapper::__setField()
     *
     * @param mixed $variable
     *            Variable to turn into a closure.
     * @return \Closure Generated function.
     */
    public static function createWrite(&$variable)
    {
        // Creates a closure that updates the current value of a variable
        // For format look @ __setField().
        return function ($value) use(&$variable)
        {
            $variable = $value;
            return IDataMapper::UPDATED;
        };
    }

#############################################################################################
# DataMapper Trait
#############################################################################################

    /**
     * Loads an array of fields into mapper.
     *
     * @param array $fields
     *            Parameters to pass to <code>__setField()</code>.
     * @return bool Returns <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function __loadFields(array $fields)
    {
        // Reset class
        parent::__construct(array(), ArrayObject::ARRAY_AS_PROPS, 'RecursiveArrayIterator');

        // Add fields
        foreach ($fields as $arguments)
            if (is_array($arguments)) {

                // Try to set field
                try {
                    call_user_func_array(array(
                        $this,
                        '__setField'
                    ), $arguments);
                }

                // Forward exceptions
                catch (InvalidArgumentException $e) {
                    throw new InvalidArgumentException(0, null, 0, $e);
                }
            }

        // Done
        return true;
    }

    /**
     * Map a dynamic properties to an object.
     *
     * <h3>Introduction</h3>
     *
     * <p>Reads to field are mapped to callable <code>$read</code>
     * which has the following format</p>
     *
     * <pre>mixed function ()</pre>
     *
     * <p>Writes to the field are mapped to the callable
     * <code>$write</code> which has the following format</p>
     *
     * <pre>IDataMapper::FLAGS function (mixed $value)</pre>
     *
     * <hr>
     *
     * @param string $name
     *            Label of dynamic property.
     * @param callable $read
     *            Function to get value from.
     * @param callable $write
     *            Function to set value to.
     * @param int $flags
     *            Mapping flags.
     * @return bool Returns <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function __setField($name, $read, $write, $flags = 0)
    {
        // Is name scalar?
        if (! is_scalar($name) ? ! is_callable(array(
            $name,
            '__toString'
        )) : false) {
            throw new InvalidArgumentException(0);
            return false;
        }

        // Is read callable?
        if (! is_callable($read)) {
            throw new InvalidArgumentException(1);
            return false;
        }

        // Is write callable?
        if (! is_callable($write)) {
            throw new InvalidArgumentException(2);
            return false;
        }

        // Is flags an integer
        if (! is_numeric($flags)) {
            throw new InvalidArgumentException(3);
            return false;
        }

        // Make sure closures are serializable
        if ($read instanceof Closure)
            $read = new SerializableClosure($read);

        if ($write instanceof Closure)
            $write = new SerializableClosure($write);

        // Add field
        parent::offsetSet(strval($name), array(
            $read,
            $write
        ));

        // Done
        return true;
    }

#############################################################################################

}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
