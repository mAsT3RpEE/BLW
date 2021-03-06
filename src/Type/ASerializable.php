<?php
/**
 * ASerializable.php | Feb 05, 2014
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
 * Abstract class for all objects that can be serialized.
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
 * | clearStatus():   IDataMapper::Status              |
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
 * @api     BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://www.php.net/manual/en/class.serializable.php Serializable Interface
 *
 * @property $_Status int [protected] Current status flag of the class.
 * @property $Status int [readonly] $_Status
 * @property $Serialier \BLW\Type\ISerializer [readonly] Invokes getSerializer().
 */
abstract class ASerializable implements \BLW\Type\ISerializable
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
     * @return boolean Returns <code>TRUE</code> on success and <code>FALSE</code> on failure.
     */
    final public function unserialize($serialized)
    {
        try {

            // Unserialize object
            return $this->unserializeWith($this->getSerializer(), $serialized);
        }

        // @codeCoverageIgnoreStart

        catch (\RuntimeException $e) {


            // Error status
            $this->_Status |= $e->getCode();

            // Error
            return false;

        }

        // @codeCoverageIgnoreEnd
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
        // Is $Data is not a string?
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
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
