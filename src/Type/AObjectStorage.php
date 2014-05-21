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
 * @version GIT: 0.2.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type;

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
 * @api     BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
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
        // Is $Data a string?
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
        // Creates a string representations of items
        $items = array();

        // Loop through each item
        foreach ($this as $o) {
            // Get class
            $items[] = get_class($o);
        }

        // Compile results
        return sprintf('[IObjectStorage:%s]', implode(IObjectStorage::GLUE, $items));
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

    /**
     * Dynamic properties.
     *
     * @param string $name Label of property to search for.
     * @return mixed Returns <code>NULL</code> if not found.
     */
    public function __get($name)
    {
        switch($name)
        {
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
            // Undefined property
            default:
                trigger_error(sprintf('Undefined property %s::$%s', get_class($this), $name), E_USER_NOTICE);
        }
    }

    /**
     * Dynamic properties.
     *
     * @param string $name Label of property to search for.
     * @return boolean Returns a <code>TRUE</code> if property exists. <code>FALSE</code> otherwise.
     */
    public function __isset($name)
    {
        switch($name)
        {
            // ISerializable
            case 'Status':
            case 'Serializer':
                return true;
            // IIterable
            case 'Parent':
                return $this->_Parent !== null;
            case 'ID':
                return $this->getID() !== null;
            // Undefined property
            default:
                false;
        }
    }

    /**
     * Dynamic properties.
     *
     * @param string $name Label of property to set.
     * @param mixed $value Value of property.
     */
    public function __set($name, $value)
    {
        // Try to set property
        switch($name)
        {
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
            // Undefined property
            default:
                $result = IDataMapper::UNDEFINED;
        }

        // Check results
        if (list($Message, $Level) = ADataMapper::getErrorInfo($result, get_class($this), $name)) {
            trigger_error($Message, $Level);
        }
    }

    /**
     * Unmap dynamic properties from DataMapper.
     *
     * @param string $name Label of dynamic property. (case sensitive)
     */
    public function __unset($name)
    {
        // Try to set property
        switch($name)
        {
            // ISerializable
            case 'Status':
                $this->clearStatus();
                break;
            // IIterable
            case 'Parent':
                $this->clearParent();
                break;
            // Undefined property
        }
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
