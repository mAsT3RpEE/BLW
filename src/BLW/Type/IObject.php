<?php
/**
 * IObject.php | Nov 29, 2013
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
 * Core BLW object Interface.
 *
 * <h3>About</h3>
 *
 * <p>All Objects must either implement this interface,
 * use the <code>BLW\Type\TObject</code> trait or
 * extend the <code>\BLW\Type\AObject</code> class.</p>
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +------------------+
 * | OBJECT                                            |<------| SERIALIZABLE     |
 * +---------------------------------------------------+       | ================ |
 * | _DataMapper:  IDataMapper                         |       | Serializable     |
 * | _Status:      int                                 |       +------------------+
 * | _ID:          string                              |<------| DATAMAPABLE      |
 * | #ID:          _ID                                 |       +------------------+
 * | #Status:      _Status                             |<------| ITERABLE         |
 * | ####:         Dynamic properties                  |       +------------------+
 * +---------------------------------------------------+
 * | getInstance(): IObject                            |
 * |                                                   |
 * | $DataMapper:  IDataMapper                         |
 * | $ID:          string                              |
 * | $flags:       int                                 |
 * +---------------------------------------------------+
 * | createID(): string                                |
 * |                                                   |
 * | $Input:  null|int|string                          |
 * +---------------------------------------------------+
 * | getID() string                                    |
 * +---------------------------------------------------+
 * | setID() IDataMapper::Status                       |
 * |                                                   |
 * | $ID:  string                                      |
 * +---------------------------------------------------+
 * | clearStatus(): IObject $this                      |
 * +---------------------------------------------------+
 * | __toString(): string                              |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\Core
 * @api BLW
 * @since 0.1.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 *
 * @property string $_ID [protected] Current ID of object.
 * @property string $ID [dynamic] Invokes getID() and setID().
 * @property string $toString [readonly] Invokes __toString().
 */
interface IObject extends \BLW\Type\ISerializable, \BLW\Type\IIterable, \BLW\Type\IDataMapable
{
    // FLAGS
    const OBJECT_FLAGS = 0x0000;

    /**
     * Creates a new instance of the object.
     * (used for chaining).
     *
     * @api BLW
     * @since 0.1.0
     *
     * @param \BLW\Type\IDataMapper $DataMapper
     *            Used to map dynamic properties in object.
     * @param string $ID
     *            ID of object distinguishing it from another.
     * @param int $flags
     *            object creation flags.
     * @return \BLW\Type\IObject Returns a new instance of the class.
     */
    public static function getInstance(IDataMapper $DataMapper, $ID = null, $flags = IObject::OBJECT_FLAGS);

    /**
     * Creates a valid Object ID / Label / Name.
     *
     * <h4>Note:</h4>
     *
     * <p>Trigger <code>Warning</code> if Input is not scaler.</p>
     *
     * <hr>
     *
     * @api BLW
     * @since 0.1.0
     *
     * @param string|int|null $Input
     *            Input can be biased to help regenerate ID's.
     * @return string Returns empty string on errors.
     */
    public static function createID($Input = null);

    /**
     * Fetches the current ID of the object.
     *
     * @api BLW
     * @since 0.1.0
     *
     * @return string Current ID of the object.
     */
    public function getID();

    /**
     * Changes the ID of the current object.
     *
     * @api BLW
     * @since 0.1.0
     *
     * @param string $ID
     *            New ID.
     * @return int Returns a <code>IDataMapper</code> status code.
     */
    public function setID($ID);

    /**
     * Clears the status flag of the current object.
     *
     * @api BLW
     * @since 0.1.0
     *
     * @return int Returns a <code>IDataMapper</code> status code.
     */
    public function clearStatus();

    /**
     * All objects must have a string representation.
     *
     * <h4>Note:</h4>
     *
     * <p>Default is the serialized form of the object.</p>
     *
     * <hr>
     *
     * @api BLW
     * @since 0.1.0
     *
     * @return string $this
     */
    public function __toString();
}

return true;

