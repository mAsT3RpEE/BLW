<?php
/**
 * TSerializable.php | Feb 05, 2014
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
 * Trait for all objects that can be serialized.
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
trait TSerializable
{

    /**
     * Current status flag of the object.
     *
     * @var int $Status
     */
    protected $_Status = 0;

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
     * @link http://www.php.net/manual/en/serializable.serialize.php Serializable::serialize()
     *
     * @return string $this
     */
    final public function serialize()
    {
        // Call serializer
        return $this->serializeWith($this->getSerializer());
    }

    /**
     * Return an object state from it serialized string.
     *
     * @link http://www.php.net/manual/en/serializable.unserialize.php Serializable::unserialize()
     *
     * @param string $serialized
     * @return bool Returns <code>TRUE</code> on success and <code>FALSE</code> on failure.
     */
    final function unserialize($serialized)
    {
        // Unserialize object
        try {
            $this->unserializeWith($this->getSerializer(), $serialized);

            // Done
            return true;
        }

        catch (\RuntimeException $e) {
            $this->_Status |= $e->getCode();
        }

        // Error
        return false;
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
            throw new InvalidArgumentException(2);

        // Error
        return false;
    }

    /**
     * Hook that is called just before an object is serialized.
     */
    public function doSerialize()
    {}

    /**
     * Hook that is called just after an object is unserialized.
     */
    public function doUnSerialize()
    {}
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
