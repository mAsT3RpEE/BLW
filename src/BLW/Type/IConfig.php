<?php
/**
 * IConfig.php | Jan 26, 2014
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

use ArrayObject;


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
 * | CONFIG                                            |<------| ArrayObject     |
 * +---------------------------------------------------+       +-----------------+
 * | __construct()                                     |       | SERIALIZABLE    |
 * |                                                   |       | =============== |
 * | $input: array|ArrayAccess                         |       | Serializable    |
 * +---------------------------------------------------+       +-----------------+
 * | __toString(): string                              |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\Core
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property string[] $_Types Array of acceptable classes / types the container can contain.
 */
Interface IConfig extends \BLW\Type\ISerializable, \IteratorAggregate, \ArrayAccess, \Countable
{

    const FLAGS = ArrayObject::STD_PROP_LIST;

    const ITERATOR = 'RecursiveArrayIterator';

    /**
     * Returns whether the requested index exists
     *
     * @api BLW
     * @since 0.1.0
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
     * @since 0.1.0
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
     * @since 0.1.0
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
     * @since 0.1.0
     * @link http://www.php.net/manual/en/arrayaccess.offsetunset.php ArrayAccess::offsetUnset()
     *
     * @param mixed $index
     *            The index being unset.
     */
    public function offsetUnset($index);

    /**
     * Appends the value
     *
     * @api BLW
     * @since 0.1.0
     * @link http://www.php.net/manual/en/arrayobject.append.php ArrayObject::append()
     *
     * @param mixed $value
     *            The value being appended.
     */
    public function append($value);

    /**
     * Get the number of public properties in the ArrayObject
     *
     * @api BLW
     * @since 0.1.0
     * @link http://www.php.net/manual/en/countable.count.php Countable::count()
     *
     * @return int The number of public properties in the ArrayObject.
     */
    public function count();

    /**
     * Sort the entries by value
     *
     * @api BLW
     * @since 0.1.0
     * @link http://www.php.net/manual/en/arrayobject.asort.php ArrayObject::asort()
     */
    public function asort();

    /**
     * Sort the entries by key
     *
     * @api BLW
     * @since 0.1.0
     * @link http://www.php.net/manual/en/arrayobject.ksort.php ArrayObject::ksort()
     */
    public function ksort();

    /**
     * Sort the entries with a user-defined comparison function and maintain key association
     *
     * @api BLW
     * @since 0.1.0
     * @link http://www.php.net/manual/en/arrayobject.uasort.php ArrayObject::uasort()
     *
     * @param callback $cmp_function
     *            <p> Function cmp_function should accept two parameters
     *            which will be filled by pairs of entries.</p>
     *
     *            <p>The comparison function must return an integer less
     *            than, equal to, or greater than zero if the first argument
     *            is considered to be respectively less than, equal to, or
     *            greater than the second.</p>
     */
    public function uasort($cmp_function);

    /**
     * Sort the entries by keys using a user-defined comparison function
     *
     * @api BLW
     * @since 0.1.0
     * @link http://www.php.net/manual/en/arrayobject.uksort.php ArrayObject::uksort()
     *
     * @param callback $cmp_function
     *            The callback comparison function.
     *
     *            <p> Function cmp_function should accept two parameters
     *            which will be filled by pairs of entry keys.</p>
     *
     *            <p>The comparison function must return an integer less
     *            than, equal to, or greater than zero if the first argument
     *            is considered to be respectively less than, equal to, or
     *            greater than the second.</p>
     */
    public function uksort($cmp_function);

    /**
     * Sort entries using a "natural order" algorithm
     *
     * @api BLW
     * @since 0.1.0
     * @link http://www.php.net/manual/en/arrayobject.natsort.php ArrayObject::natsort()
     */
    public function natsort();

    /**
     * Sort an array using a case insensitive "natural order" algorithm
     *
     * @api BLW
     * @since 0.1.0
     * @link http://www.php.net/manual/en/arrayobject.natcasesort.php ArrayObject::natcasesort()
     */
    public function natcasesort();

    /**
     * Create a new iterator from an ArrayObject instance
     *
     * @api BLW
     * @since 0.1.0
     * @link http://www.php.net/manual/en/iteratoraggregate.getiterator.php IteratorAggregate::getIterator()
     *
     * @return \RecursiveArrayIterator An instance implementing <code>Iterator</code>.
     */
    public function getIterator();

    /**
     * All objects must have a string representation.
     *
     * @api BLW
     * @since 0.1.0
     *
     * @return string $this
     */
    public function __toString();
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
