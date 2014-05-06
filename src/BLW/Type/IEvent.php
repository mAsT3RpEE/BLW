<?php
/**
 * IEvent.php | Dec 27, 2013
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
 * Core Event interface.
 *
 * <h3>About</h3>
 *
 * <p>All Events must either implement this interface</p>
 *
 * <p>You can call the method stopPropagation() to abort
 * the execution of further listeners in your event listener.</p>
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+
 * | EVENT                                             |
 * +---------------------------------------------------+
 * | $_isPropagationStopped:  bool                     |
 * | $_Subject:               mixed                    |
 * | $_Context:               array                    |
 * +---------------------------------------------------+
 * | isPropagationStopped(): bool                      |
 * +---------------------------------------------------+
 * | stopPropagation():                                |
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
 *
 * @property bool $_isPropagationStopped [private] Whether no further event listeners should be triggered.
 * @property mixed $_Subject [protected] Object / Variable associated with the event.
 * @property array $_Context [protected] Event parameters.
 */
interface IEvent
{

    /**
     * Returns whether further event listeners should be triggered.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return bool Whether propagation was already stopped for this event.
     */
    public function isPropagationStopped();

    /**
     * Stops the propagation of the event to further event listeners.
     *
     * <h4>Note:</h4>
     *
     * <p>If multiple event listeners are connected to the same event, no
     * further event listener will be triggered once any trigger calls
     * <code>stopPropagation()</code>.
     *
     * <hr>
     *
     * @api BLW
     * @since 1.0.0
     */
    public function stopPropagation();

    /**
     * All objects must have a string representation.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return string String value of object.
     */
    public function __toString();
}

return true;
