<?php
/**
 * AConfig.php | Jan 26, 2014
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

use ArrayObject;
use ArrayAccess;
use UnexpectedValueException;

use \BLW\Model\InvalidArgumentException;


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
 * Base container class.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +-----------------+
 * | CONFIG                                            |<------| ArrayObject     |
 * +---------------------------------------------------+       +-----------------+
 * | __construct()                                     |       | SERIALIZABLE    |
 * |                                                   |       | =============== |
 * | $input: array|ArrayAccess                         |       | Serializable    |
 * +---------------------------------------------------+       +-----------------+
 * | __toString(): string                              |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\Core
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property string[] $_Types Array of acceptable classes / types the container can contain.
 */
abstract class AConfig extends \ArrayObject implements \BLW\Type\IConfig
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
     * @param \BLW\Type\ISerializer $Serializer
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
     * @param \BLW\Type\ISerializer $Serializer
     *            Serializer handler to use.
     * @param string $Data
     *            Serialized data.
     * @param int $flags
     *            De-Serialization flags.
     * @return bool Returns <code>TRUE</code> on success and false on failure.
     */
    final public function unserializeWith(ISerializer $Serializer, $Data, $flags = ISerializer::SERIALIZER_FLAGS)
    {
        if (is_string($Data) ?  : is_callable(array(
            $Data,
            '__toString'
        ))) {
            return $Serializer->decode($this, strval($Data), @intval($flags));
        }

        else
            throw new InvalidArgumentException(2);

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
# Config Trait
#############################################################################################

    /**
     * Sets the value at the specified index to newval
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetset.php ArrayAccess::offsetSet()
     *
     * @param mixed $index
     *            The index being set.
     * @param mixed $newval
     *            The new value for the index.
     */
    public function offsetSet($index, $newval)
    {
        // Is value scalar / ArrayAcceess?
        if (is_null($newval) ?: is_scalar($newval) ?: $newval instanceof ArrayAccess)
            parent::offsetSet($index, $newval);

        // Value not scallar / ArrayAccess
        else
            throw new UnexpectedValueException(sprintf('Instance of ArrayAccess expected. Instead %s given.', is_object($newval) ? get_class($newval) : gettype($newval)));
    }

    /**
     * Appends the value
     *
     * @link http://www.php.net/manual/en/arrayobject.append.php ArrayObject::append()
     *
     * @param mixed $value
     *            The value being appended.
     */
    public function append($value)
    {
        // Is value scalar / ArrayAcceess?
        if (is_null($value) ?: is_scalar($value) ?: $value instanceof ArrayAccess)
            parent::append($value);

        // Value not scallar / ArrayAccess
        else
            throw new UnexpectedValueException(sprintf('Instance of ArrayAccess expected. Instead %s given.', is_object($value) ? get_class($value) : gettype($value)));
    }

    /**
     * Contstructor
     *
     * @link http://www.php.net/manual/en/arrayobject.construct.php ArrayObject::__construct()
     *
     * @param ArrayAccess[] $imput
     *            The input parameter accepts an array of <code>ArrayAccess</code>.
     */
    public function __construct(array $input = array())
    {
        parent::__construct($input, IConfig::FLAGS, IConfig::ITERATOR);
    }

    /**
     * All objects must have a string representation.
     *
     * @return string $this
     */
    public function __toString()
    {
        return sprintf('[IConfig:%d]', $this->count());
    }

#############################################################################################

}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
