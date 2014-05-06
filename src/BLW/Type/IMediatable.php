<?php
/**
 * IMediatable.php | Feb 05, 2014
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
 * Interface for all objects that can be mediated.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+
 * | MEDIATABLE                                        |
 * +---------------------------------------------------+
 * | DEFAULT_MEDIATOR class                            |
 * +---------------------------------------------------+
 * | #Mediator:    getMediator()                       |
 * |               setMediator()                       |
 * | #MediatorID:  getMediatorID()                     |
 * +---------------------------------------------------+
 * | getMediator(): IMediator                          |
 * +---------------------------------------------------+
 * | setMediator(): IDataMapper::Status                |
 * |                                                   |
 * | $Mediator:  IMediator                             |
 * +---------------------------------------------------+
 * | clearMediator(): IDataMapper::Status              |
 * +---------------------------------------------------+
 * | _on()                                             |
 * |                                                   |
 * | $EventName:  string                               |
 * | $Callback:   callable                             |
 * | $Priotity:   int                                  |
 * +---------------------------------------------------+
 * | _do()                                             |
 * |                                                   |
 * | $EventName:  string                               |
 * | $Event:      IEvent                               |
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
 * @property $Mediator \BLW\Type\IMediator [dynamic] Invokes setMediator() and getMediator().
 * @property $MediatorID string [readonly] Invokes getMediatorID().
 */
interface IMediatable
{
    // ERRORS
    const INVALID_CALLBACK = 0x2000;

    /**
     * Get the current mediator of the object.
     *
     * @api BLW
     * @since 0.1.0
     *
     * @return \BLW\Type\IMediator Generates a default mediator if not set.
     */
    public function getMediator();

    /**
     * Set $Mediator dynamic property.
     *
     * @api BLW
     * @since 0.1.0
     *
     * @param \BLW\Type\IMediator $Mediator
     *            New mediator.
     * @return int Returns a <code>IDataMapper</code> status code.
     */
    public function setMediator($Mediator);

    /**
     * Clear $Mediator dynamic property.
     *
     * @api BLW
     * @since 0.1.0
     *
     * @return int Returns a <code>IDataMapper</code> status code.
     */
    public function clearMediator();

    /**
     * Generates a unique id used to identify object in mediator.
     *
     * @api BLW
     * @since 0.1.0
     *
     * @return string Hash of object.
     */
    public function getMediatorID();

    /**
     * Registers a function to execute on a mediator event.
     *
     * <h4>Format:</h4>
     *
     * <pre>mixed function (\BLW\Type\IEvent $Event)</pre>
     *
     * <hr>
     *
     * @api BLW
     * @since 0.1.0
     *
     * @param string $EventName
     *            Event ID to attach to.
     * @param callable $Callback
     *            Function to call.
     * @param int $Priority
     *            Priotory of <code>$Callback</code>. (Higher priority = Higher Importance)
     */
    public function _on($EventName, $Callback, $Priority = 0);

    /**
     * Activates a mediator event.
     *
     * @api BLW
     * @since 0.1.0
     *
     * @param string $EventName
     *            Event ID to activate.
     * @param \BLW\Type\IEvent $Event
     *            Event object associated with the event.
     */
    public function _do($EventName, IEvent $Event);
}

return true;
