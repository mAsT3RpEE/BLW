<?php
/**
 * IObjectStorage.php | Dec 27, 2013
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
 * Object container interface.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +-------------------+
 * | OBJECTSTORAGE                                     |<--+---| SplObjectStorage  |
 * +---------------------------------------------------+   |   +-------------------+
 * | __toString(): string                              |   |   | SERIALIZABLE      |
 * +---------------------------------------------------+   |   | ================= |
 *                                                         +---| Serializable      |
 *                                                         |   +-------------------+
 *                                                         +---| ITERABLE          |
 *                                                             +-------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\Core
 * @api     BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 */
interface IObjectStorage extends \BLW\Type\ISerializable, \BLW\Type\IIterable, \ArrayAccess, \Countable, \Iterator
{

    const GLUE = ',';

    /**
     * Returns whether the requested index exists
     *
     * @api BLW
     * @since   1.0.0
     * @link http://www.php.net/manual/en/arrayaccess.offsetexists.php ArrayAccess::offsetExists()
     *
     * @param object $index
     *            The index being checked.
     * @return boolean <code>TRUE</code> if the requested index exists, <code>FALSE</code> otherwise.
     */
    public function offsetExists($index);

    /**
     * Returns the value at the specified index
     *
     * @api BLW
     * @since   1.0.0
     * @link http://www.php.net/manual/en/arrayaccess.offsetget.php ArrayAccess::offsetGet()
     *
     * @param object $index
     *            The index with the value.
     * @return mixed The value at the specified index or <code>FALSE</code>.
     */
    public function offsetGet($index);

    /**
     * Sets the value at the specified index to newval
     *
     * @api BLW
     * @since   1.0.0
     * @link http://www.php.net/manual/en/arrayaccess.offsetset.php ArrayAccess::offsetSet()
     *
     * @param object $index
     *            The index being set.
     * @param mixed $newval
     *            The new value for the index.
     * @return void
     */
    public function offsetSet($index, $newval);

    /**
     * Unsets the value at the specified index
     *
     * @api BLW
     * @since   1.0.0
     * @link http://www.php.net/manual/en/arrayaccess.offsetunset.php ArrayAccess::offsetUnset()
     *
     * @param object $index
     *            The index being unset.
     * @return void
     */
    public function offsetUnset($index);

    /**
     * Get the number of public objects in the ObjectStorage
     *
     * @api BLW
     * @since   1.0.0
     * @link http://www.php.net/manual/en/countable.count.php Countable::count()
     *
     * @return integer The number of objects in storage.
     */
    public function count();

    /**
     * Adds an object in the storage.
     *
     * @api BLW
     * @since   1.0.0
     * @link http://www.php.net/manual/en/splobjectstorage.attach.php SplObjectStorage::attatch()
     *
     * @param object $object
     *            The object to add.
     * @param mixed $data
     *            [optional] The data to associate with the object.
     * @return void
     */
    public function attach($object, $data = null);

    /**
     * Removes an object from the storage.
     *
     * @api BLW
     * @since   1.0.0
     * @link http://www.php.net/manual/en/splobjectstorage.detach.php SplObjectStorage::detatch()
     *
     * @param object $object
     *            The object to remove.
     */
    public function detach($object);

    /**
     * Checks if the storage contains a specific object
     *
     * @api BLW
     * @since   1.0.0
     * @link http://www.php.net/manual/en/splobjectstorage.contains.php
     *
     * @param object $object
     *            The object to look for.
     * @return boolean true if the object is in the storage, false otherwise.
     */
    public function contains($object);

    /**
     * All objects must have a string representation.
     *
     * <h4>Note:</h4>
     *
     * <p>Default is the serialized form of the object.</p>
     *
     * <hr>
     *
     * @api BLW
     * @since   1.0.0
     *
     * @return string String value of object.
     */
    public function __toString();
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
