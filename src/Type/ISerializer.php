<?php
/**
 * ISerializer.php | Feb 10, 2014
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

use stdClass;

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
 * Interface for all object serializers.
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
 * @api     BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
interface ISerializer extends \BLW\Type\ISerializable, \BLW\Type\IIterable
{

    // Serializer flags
    const REPLACE_MEDIATORS       = 0x0002;
    const RESTORE_MEDIATORS       = 0x0004;
    const REPLACE_FILEDESCRIPTORS = 0x0008;
    const SERIALIZER_FLAGS        = 0x000c;

    /**
     * Encode an object as a string.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param \BLW\Type\ISerializable $Object
     *            Object to serialize.
     * @param integer $flags
     *            Encoding flags.
     * @return string $Object
     */
    public function encode(ISerializable $Object, $flags = ISerializer::SERIALIZER_FLAGS);

    /**
     * Restore an object state from its serialized string.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param \BLW\Type\ISerializable $Object
     *            Object to unserialize.
     * @param string $Data
     *            Serialized string
     * @param integer $flags
     *            Decoding flags.
     * @return boolean Returns <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function decode(ISerializable $Object, $Data, $flags = ISerializer::SERIALIZER_FLAGS);

    /**
     * Export properties of an object.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param \BLW\Type\ISerializable $Object
     *            Object to export.
     * @param array $Exported
     *            Exported properties.
     * @return integer Number of properties exported.
     */
    public function export(ISerializable $Object, array &$Exported = null);

    /**
     * Import properties to an object.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param \BLW\Type\ISerializable $Object
     *            Object to import into.
     * @param array $Exported
     *            Exported object.
     * @return integer Number of properties imported.
     */
    public function import(ISerializable &$Object, array $Exported);

    /**
     * All objects must have a string representation.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @return string $this
     */
    public function __toString();
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
