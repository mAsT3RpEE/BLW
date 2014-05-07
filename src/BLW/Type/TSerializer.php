<?php
/**
 * TSerializer.php | Feb 14, 2014
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

use stdClass;
use ReflectionObject;
use ReflectionProperty;


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
 * Trait for all object serializers.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +------------------+
 * | SERIALIZER                                        |<------| SERIALIZABLE     |
 * +---------------------------------------------------+       | ================ |
 * | SERIALIZER_FLAGS                                  |<------| Serializable     |
 * | REPLACE_MEDIATORS                                 |       +------------------+
 * | RESTORE_MEDIATORS                                 |       | ITERABLE         |
 * | REPLACE_FILE_DESCRIPTORS                          |       +------------------+
 * +---------------------------------------------------+
 * | getID(): string                                   |
 * +---------------------------------------------------+
 * | encode() string                                   |
 * |                                                   |
 * | $Object:  ISerializable                           |
 * | $flags:   int                                     |
 * +---------------------------------------------------+
 * | decode() bool                                     |
 * |                                                   |
 * | $Object:  ISerializable                           |
 * | $Data:    string                                  |
 * | $flags:   int                                     |
 * +---------------------------------------------------+
 * | export(): int                                     |
 * |                                                   |
 * | $Object:    ISerializable                         |
 * | $Exported:  stdClass                              |
 * +---------------------------------------------------+
 * | import(): int                                     |
 * |                                                   |
 * | $Object:    ISerializable                         |
 * | $Exported:  stdClass                              |
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
trait TSerializer
{
    use\BLW\Type\TSerializable;
    use\BLW\Type\TIterable;

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
     * Encode an object as a string.
     *
     * @param \BLW\Type\ISerializable $Object
     *            Object to serialize.
     * @param int $flags
     *            Encoding flags.
     * @return string $Object
     */
    abstract public function encode(ISerializable $Object, $flags = ISerializer::SERIALIZER_FLAGS);

    /**
     * Restore an object state from its serialized string.
     *
     * @param \BLW\Type\ISerializable $Object
     *            Object to unserialize.
     * @param string $Data
     *            Serialized string
     * @param int $flags
     *            Decoding flags.
     * @return bool Returns <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    abstract public function decode(ISerializable $Object, $Data, $flags = ISerializer::SERIALIZER_FLAGS);

    /**
     * Export properties to an instance of stdClass.
     *
     * @param \BLW\Type\ISerializable $Object
     *            Object ro export.
     * @param array $Exported
     *            Exported object.
     * @return int Number of properties exported.
     */
    final public function export(ISerializable $Object, array &$Exported = null)
    {
        // Make sure $Exported is an object
        $Exported = $Exported ?: array();

        // Gets properties of Object to export to $Exportded.
        $Properties = function (ISerializable $o)
        {
            // Do pre Serialization.
            $o->doSerialize();

            // Get properties
            $Properties = array();
            $Reflection = new ReflectionObject($o);

            foreach ($Reflection->getProperties() as $Property) {

                // Filter out static properties
                if ($Property->isStatic())
                    continue;

                $Property->setAccessible(true);

                $Properties[] = $Property;
            }

            // Return properties
            return $Properties;
        };

        // Export properties
        $count = 0;
        $Clone = clone $Object;

        foreach ($Properties($Clone) as $Property) {
            $Exported[$Property->getName()] = $Property->getValue($Clone);

            $count += 1;
        }

        return $count;
    }

    /**
     * Import properties from an instance of stdClass.
     *
     * @param \BLW\Type\ISerializable $Object
     *            Object to import into.
     * @param array $Exported
     *            Exported object.
     * @return int Number of properties imported.
     */
    final public function import(ISerializable &$Object, array $Exported)
    {
        // Import properties
        $Reflection = new ReflectionObject($Object);
        $count      = 0;

        foreach ($Exported as $name => $value) {

            // Does the class have the property
            if ($Reflection->hasProperty($name)) {

                // Set its value
                $Property  = $Reflection->getProperty($name);

                $Property->setAccessible(true);
                $Property->setValue($Object, $value);

                // Update count
                $count++;
            }

            // No, try to dynamically set it
            else {

                try {
                    $Object->{$name} = $value;

                    // Update count
                    $count += 1;
                }

                catch (\Exception $e) {}
            }
        }

        // Unserialize Event
        $Object->doUnSerialize();

        // Done
        return $count;
    }

    /**
     * All objects must have a string representation.
     *
     * @return string $this
     */
    public function __toString()
    {
        return '[' . basename(get_class($this)) . 'Serializer]';
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
            // ISerializer
            default:
                trigger_error(sprintf('Undefined property %s::$%s', get_class($this), $name), E_USER_NOTICE);
        }

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
            // ISerializer
            default:
                return false;
        }
    }

    /**
     * Dynamic properties.
     *
     * @param string $name
     *            Label of property to set.
     * @param mixed $value
     *            Value of property.
     * @return void
     */
    public function __set($name, $value)
    {
        $result = 0;

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
            // ISerializer
            default:
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
                trigger_error(sprintf('Cannot modify non-existant property: %s::$%s', get_class($this), $name), E_USER_ERROR);
                break;
        }
    }

    /**
     * Unmap dynamic properties from DataMapper.
     *
     * @param string $name
     *            Label of dynamic property. (case sensitive)
     * @return bool Returns a <code>TRUE</code> if property exists. <code>FALSE</code> otherwise.
     */
    public function __unset($name)
    {
        // Try to unset property
        switch ($name) {
            // IIterable
            case 'Parent':
                $result = $this->clearParent();
                break;
            // ISerializable
            default:
                $result = IDataMapper::INVALID;
        }
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd