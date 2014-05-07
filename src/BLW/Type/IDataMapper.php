<?php
/**
 * IDataMapper.php | Feb 10, 2014
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
 * Interface for all dynamic property / method mapper objects.
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
interface IDataMapper extends \BLW\Type\IFactory, \ArrayAccess, \IteratorAggregate
{
    // Status flags
    const UPDATED    = 0x0000;
    const READONLY   = 0x0001;
    const WRITEONLY  = 0x0002;
    const ONESHOT    = 0x0010;
    const INVALID    = 0x0020;
    const UNDEFINED  = 0x0040;

    // Mapping flags
    const IS_CALLABLE = 0x0001;
    const IS_DYNAMIC  = 0x0002;

    /**
     * Returns whether the requested index exists
     *
     * @api BLW
     * @since 1.0.0
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
     * @since 1.0.0
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
     * @since 1.0.0
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
     * @since 1.0.0
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
     * @since 1.0.0
     * @link http://www.php.net/manual/en/countable.count.php Countable::count()
     *
     * @return int The number of public properties in the ArrayObject.
     */
    public function count();

    /**
     * Create a new iterator from an ArrayObject instance
     *
     * @api BLW
     * @since 1.0.0
     * @link http://www.php.net/manual/en/iteratoraggregate.getiterator.php IteratorAggregate::getIterator()
     *
     * @return \RecursiveArrayIterator An instance implementing <code>Iterator</code>.
     */
    public function getIterator();

    /**
     * Loads an array of fields into mapper.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param array $fields
     *            Parameters to pass to <code>__setField()</code>.
     * @return bool Returns <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function __loadFields(array $fields);

    /**
     * Map a dynamic properties to an object.
     *
     * @api BLW
     * @since 1.0.0
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
    public function __setField($name, $read, $write, $flags = 0);

    /**
     * Creates a closure to automatically read the value of a variable.
     *
     * @api BLW
     * @since 1.0.0
     * @see \BLW\Type\IDataMapper::__setField()
     *
     * @param mixed $variable
     *            Variable to turn into a closure.
     * @return \Closure Generated function.
     */
    public static function createRead(&$variable);

    /**
     * Creates a closure to automatically read the value of a variable.
     *
     * @api BLW
     * @since 1.0.0
     * @see \BLW\Type\IDataMapper::__setField()
     *
     * @param mixed $variable
     *            Variable to turn into a closure.
     * @return \Closure Generated function.
     */
    public static function createWrite(&$variable);
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
