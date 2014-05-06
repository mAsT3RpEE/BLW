<?php
/**
 * AObjectStorage.php | Dec 27, 2013
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

use BLW\Model\InvalidArgumentException;


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

/**
 * Object container interface.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +------------------+
 * | OBJECTSTORAGE                                     |<--+---| SplObjectStorage |
 * +---------------------------------------------------+   |   +------------------+
 * | __toString(): string                              |   |   | SERIALIZABLE     |
 * +---------------------------------------------------+   |   | ================ |
 *                                                         +---| Serializable     |
 *                                                         |   +------------------+
 *                                                         +---| ITERABLE         |
 *                                                             +------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\Core
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 */
abstract class AObjectStorage extends \SplObjectStorage implements \BLW\Type\IObjectStorage
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
     * @return \BLW\Type\Serializer $this->Serializer
     */
    public function getSerializer()
    {
        global $BLW_Serializer;

        if (! $BLW_Serializer instanceof ISerializer) {
            $BLW_Serializer = new \BLW\Model\Serializer\PHP();
        }

        return $BLW_Serializer;
    }

    /**
     * Return a string representation of the object.
     *
     * @param ISerializer $Serializer
     *            Serializer handler to use.
     * @param int $flags
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
     * @param ISerializer $Serializer
     *            Serializer handler to use.
     * @param string $Data
     *            Serialized data.
     * @param int $flags
     *            De-Serialization flags.
     * @return bool Returns <code>TRUE</code> on success and false on failure.
     */
    final public function unserializeWith(ISerializer $Serializer, $Data, $flags = ISerializer::SERIALIZER_FLAGS)
    {
        // Is $Data a string?
        if (is_string($Data) ?: is_callable(array(
            $Data,
            '__toString'
        )))
            return $Serializer->decode($this, strval($Data), @intval($flags));

        // $Data is not a string
        else
            throw new InvalidArgumentException(1);

        // Error
        return false;
    }

    /**
     * Hook that is called just before an object is serialized.
     */
    final public function doSerialize()
    {}

    /**
     * Hook that is called just after an object is unserialized.
     */
    final public function doUnSerialize()
    {}

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
# ObjectStorage Trait
#############################################################################################

    /**
     * All objects must have a string representation.
     *
     * @return string $this
     */
    public function __toString()
    {
        static $items;

        // Creates a string representations of items
        $items = $items ?: function ($o)
        {
            $items = array();

            // Loop through each item
            foreach ($o as $v)
                // Get class
                $items[] = get_class($v);

            // Compile results
            return implode(IObjectStorage::GLUE, $items);
        };

        return sprintf('[IObjectStorage:%s]', $items($this));
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

return true;
