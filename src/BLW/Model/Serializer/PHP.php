<?php
/**
 * PHP.php | Feb 15, 2014
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
namespace BLW\Model\Serializer;

use ReflectionObject;
use ReflectionProperty;

use BLW\Type\ISerializer;
use BLW\Type\ISerializable;


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
 * PHP object serializer.
 *
 * @package BLW\Core
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class PHP extends \BLW\Type\ASerializer
{

    /**
     * Encode an object as a string.
     *
     * @param \BLW\Type\ISerializable $Object
     *            Object to serialize.
     * @param int $flags
     *            Encoding flags.
     * @return string $Object
     */
    public function encode(ISerializable $Object, $flags = ISerializer::SERIALIZER_FLAGS)
    {
        // Export porperties of object
        $this->export($Object, $Properties);

        // Serialize properties
        return serialize($Properties);
    }

    /**
     * Restore an object state from its serialized string.
     *
     * @param \BLW\Type\ISerializable $Object
     *            Object to unserialize.
     * @param string $Data
     *            Serialized string
     * @param int $flags
     *            Decoding flags.
     * @return  bool Returns <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function decode(ISerializable $Object, $Data, $flags = ISerializer::SERIALIZER_FLAGS)
    {

        // Unserizlize properties
        $Properties = unserialize($Data);

        // Check results
        if (is_array($Properties)) {

            // Restore properties
            $this->import($Object, $Properties);

            // Return true
            return true;
        }

        // No properties, return false
        return false;
    }
}

return true;
