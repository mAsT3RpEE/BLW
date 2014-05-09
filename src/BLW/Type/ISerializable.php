<?php
/**
 * ISerializable.php | Feb 05, 2014
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
 * Interface for all objects that can be serialized.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +-----------------+
 * | SERIALIZABLE                                      |<------| Serializable    |
 * +---------------------------------------------------+       +-----------------+
 * | _Status:      int                                 |
 * | #Status:      _Status                             |
 * | #Serializer:  getSerializer()                     |
 * +---------------------------------------------------+
 * | getSerializer(): ISerializer                      |
 * +---------------------------------------------------+
 * | serialize(): getSerializer()::serialize()         |
 * +---------------------------------------------------+
 * | unserialize(): getSerializer()::unserialize()     |
 * +---------------------------------------------------+
 * | serializeWith(): $Serializer::serialize()         |
 * |                                                   |
 * | $Serializer:  ISerializer                         |
 * +---------------------------------------------------+
 * | unserializeWith(): $Serailizer::unserialize()     |
 * |                                                   |
 * | $Serializer:  ISerializer                         |
 * +---------------------------------------------------+
 * | doSerialize():                                    |
 * +---------------------------------------------------+
 * | doUnSerialize():                                  |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\Core
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://www.php.net/manual/en/class.serializable.php Serializable Interface
 *
 * @property $_Status int [protected] Current status flag of the class.
 * @property $Status int [readonly] $_Status
 * @property $Serialier \BLW\Type\ISerializer [readonly] Invokes getSerializer().
 */
interface ISerializable extends \Serializable
{
    // ERRORS
    const SERIALIZE_ERROR   = 0x0100;
    const UNSERIALIZE_ERROR = 0x0200;

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
     * <hr>
     *
     * @return \BLW\Type\Serializer $this->Serializer
     */
    public function getSerializer();

    /**
     * Return a string representation of the object.
     *
     * @api BLW
     * @since 1.0.0
     * @link http://www.php.net/manual/en/serializable.serialize.php Serializable::serialize()
     *
     * @return string $this
     */
    public function serialize();

    /**
     * Return an object state from it serialized string.
     *
     * @api BLW
     * @since 1.0.0
     * @link http://www.php.net/manual/en/serializable.unserialize.php Serializable::unserialize()
     *
     * @param string $serialized
     * @return bool Returns <code>TRUE</code> on success. Returns <code>FALSE</code> otherwise.
     */
    public function unserialize($serialized);

    /**
     * Return a string representation of the object.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param ISerializer $Serializer
     *            Serializer handler to use.
     * @return string $this
     */
    public function serializeWith(ISerializer $Serializer);

    /**
     * Return an object state from it serialized string.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param ISerializer $Serializer
     *            Serializer handler to use.
     * @param string $Data
     *            String to unserialize.
     * @return bool Returns <code>TRUE</code> on success and false on failure.
     */
    public function unserializeWith(ISerializer $Serializer, $Data);

    /**
     * Hook that is called just before an object is serialized.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return \BLW\Type\Serializable $this
     */
    public function doSerialize();

    /**
     * Hook that is called just after an object is unserialized.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return void
     */
    public function doUnSerialize();
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
