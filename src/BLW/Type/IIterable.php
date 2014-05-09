<?php
/**
 * IIterable.php | Dec 26, 2013
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
 * Interface for all objects that can be contained by others.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +------------------------------------------------+
 * | ITERABLE                                       |
 * +------------------------------------------------+
 * | #Parent:  setParent()                          |
 * |           getParent()                          |
 * | #ID:      getID()                              |
 * +------------------------------------------------+
 * | getParent(): IObject|null                      |
 * +------------------------------------------------+
 * | setParent(): IDataMapper::Status               |
 * |                                                |
 * | $Parent:  IObject                              |
 * +------------------------------------------------+
 * | getID(): string                                |
 * +------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\Core
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 *
 * @property $Parent \BLW\Type\IObject [dynamic] Invokes getParent(} and setParent().
 * @property $ID string [readonly] Invokes getID().
 */
interface IIterable
{

    /**
     * Retrieves the current parent of the object.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return \BLW\Type\IObject Returns <code>null</code> if no parent is set.
     */
    public function getParent();

    /**
     * Sets parent of the current object if null.
     *
     * @api BLW
     * @since 1.0.0
     * @internal This is a one shot function (Only works once).
     *
     * @param mised $Parent
     *            New parent of object. (IObject|IContainer|IObjectStorage)
     * @return int Returns a <code>IDataMapper</code> status code.
     */
    public function setParent($Parent);

    /**
     * Clears parent of the current object.
     *
     * @api BLW
     * @since 1.0.0
     * @access private
     * @internal For internal use only.
     *
     * @return int Returns a <code>IDataMapper</code> status code.
     */
    public function clearParent();

    /**
     * Get the ID of the object.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return string Current ID.
     */
    public function getID();
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
