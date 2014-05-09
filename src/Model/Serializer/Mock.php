<?php
/**
 * Mock.php | Feb 15, 2014
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
namespace BLW\Model\Serializer;

use BLW\Type\ISerializer;
use BLW\Type\ISerializable;

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
 * Mock serializer used for testing purposes.
 *
 * @package BLW\Core
 * @api     BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class Mock extends \BLW\Type\ASerializer
{

    /**
     * Cache of serialized objects.
     *
     * @var \BLW\Type\ISerializable[]
     */
    public $Cache = array();

    /**
     * Last flags used.
     *
     * @var int $flags
     */
    public $flags = 0;

    /**
     * Encode an object as a string.
     *
     * @param \BLW\Type\ISerializable $Object
     *            Object to serialize.
     * @param integer $flags
     *            Encoding flags.
     * @return string $Object
     */
    public function encode(ISerializable $Object, $flags = ISerializer::SERIALIZER_FLAGS)
    {
        // Export porperties of object
        $this->export($Object, $Properties);

        // Store object.
        $Hash = spl_object_hash($Object);
        $this->Cache[$Hash] = $Properties;

        // Store flags
        $this->flags = $flags;

        return $Hash;
    }

    /**
     * Restore an object state from its serialized string.
     *
     * @param \BLW\Type\ISerializable $Object
     *            Object to unserialize.
     * @param string $Data
     *            Serialized string
     * @param integer $flags
     *            Decoding flags.
     * @return boolean Returns <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function decode(ISerializable $Object, $Data, $flags = ISerializer::SERIALIZER_FLAGS)
    {
        // Store flags
        $this->flags = $flags;

        // Look for Object in cache
        if (isset($this->Cache[$Data])) {

            // Restore properties
            $this->import($Object, $this->Cache[$Data]);

            // Return true
            return true;
        }

        // Not in cahce, return false
        return false;
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
