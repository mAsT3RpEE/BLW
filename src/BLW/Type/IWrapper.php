<?php
/**
 * IWrapper.php | Dec 27, 2013
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
 * Wrapper pattern Interface.
 *
 * <h3>About</h3>
 *
 * <p>All decorator and adaptor objects must either implement
 * this interface, use the <code>BLW\Type\TWrapper</code> trait
 * or extend the <code>\BLW\Type\AWrapper</code> class.</p>
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +------------------+
 * | WRAPPER                                           |<------| SERIALIZABLE     |
 * +---------------------------------------------------+       | ================ |
 * | _Component:  mixed                                |       | Serializable     |
 * | ###:         Import component properties          |       +------------------+
 * +---------------------------------------------------+       | COMPONENTMAPABLE |
 * | __construct():                                    |       +------------------+
 * |                                                   |       | ITERABLE         |
 * | $Component:  mixed                                |       +------------------+
 * | $flags:      int                                  |
 * +---------------------------------------------------+
 * | getInstance(): IWrapper                           |
 * |                                                   |
 * | $Component:  mixed                                |
 * | $flags:      int                                  |
 * +---------------------------------------------------+
 * | ###(): Import component functions()               |
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
 * @property mixed $_Component [protected] Pointer to component of current object.
 */
interface IWrapper extends \BLW\Type\ISerializable, \BLW\Type\IComponentMapable, \BLW\Type\IIterable
{
    // Flags
    const WRAPPER_FLAGS = 0x0000;

    /**
     * Creates a new instance of the object (used for chaining).
     *
     * @api BLW
     * @since 0.1.0
     *
     * @param mixed $Argument
     *            [optional] Constructor argument.
     * @param ...
     *
     * @return \BLW\Type\IWrapper Returns a new instance of the class.
     */
    public static function getInstance();

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
