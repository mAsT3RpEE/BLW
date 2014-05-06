<?php
/**
 * IMediator.php | Dec 28, 2013
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
 * Core Mediator pattern interface.
 *
 * <h4>Notice:</h4>
 *
 * <p>All mediators must either implement this interface</p>
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +------------------+
 * | MEDIATOR                                          |<------| SERIALIZABLE     |
 * +---------------------------------------------------+       | ================ |
 * | register():                                       |<------| Serializable     |
 * |                                                   |       +------------------+
 * | $EventName:  string                               |       | ITERABLE         |
 * | $Callback:   callable                             |       +------------------+
 * | $Priority:   int                                  |
 * +---------------------------------------------------+
 * | deregister():                                     |
 * |                                                   |
 * | $EventName:  string                               |
 * | $Callback:   callable                             |
 * +---------------------------------------------------+
 * | trigger():                                        |
 * |                                                   |
 * | $EventName:  string                               |
 * | $Event:      IEvent                               |
 * | $flags:      int                                  |
 * +---------------------------------------------------+
 * | isRegistered(): bool                              |
 * +---------------------------------------------------+
 * | addSubscriber(): bool                             |
 * |                                                   |
 * | $Subscriber:  IEventSubscriber                    |
 * +---------------------------------------------------+
 * | remSubscriber(): bool                             |
 * |                                                   |
 * | $Subscriber:  IEventSubscriber                    |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\Core
 * @api BLW / Symfony
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
interface IMediator extends \BLW\Type\ISerializable, \BLW\Type\IIterable
{

    const MEDIATOR_FLAGS = 0x0000;

    /**
     * Registers a function to handle an event.
     *
     * <h4>Format</h4>
     *
     * <pre>void function (IEvent $Event, string $EventID, IMediator $Mediator)</pre>
     *
     * <hr>
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param string $EventName
     *            Action to register.
     * @param callable $Callback
     *            Function to call when event is triggered.
     * @param int $Priority
     *            Priority of the function. (Higher value = Higher priority)
     * @return bool <code>TRUE</code> on success <code>FALSE</code> on failure.
     */
    public function register($EventName, $Callback, $Priority = 0);

    /**
     * Deregister a function from handling an event.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param string $EventName
     *            Action to deregister.
     * @param callable $Callback
     *            Function to call when event is triggered.
     */
    public function deregister($EventName, $Callback);

    /**
     * Triggers an event.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param string $EventName
     *            Action to trigger.
     * @param \BLW\Type\IEvent $Event
     *            Event object associated with the event.
     * @param int $flags
     *            Mediation flags.
     */
    public function trigger($EventName, IEvent $Event = null, $flags = 0);

    /**
     * Checks if an event has any subscribers
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param string $EventName
     *            Action to check.
     * @return bool <code>TRUE</code> if callbacks are registered <code>FALSE</code> otherwise.
     */
    public function isRegistered($EventName);

    /**
     * Adds an event subscriber.
     *
     * <h4>Note:</h4>
     *
     * <p>The subscriber is asked for all the events he is
     * interested in and registered for these events.</p>
     *
     * <hr>
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param IEventSubscriber $Subscriber
     *            The subscriber to add.
     * @return bool <code>TRUE</code> on success <code>FALSE</code> on failure.
     */
    public function addSubscriber(IEventSubscriber $Subscriber);

    /**
     * Removes an event subscriber.
     *
     * <h4>Note:</h4>
     *
     * <p>The subscriber is asked for all the events he is
     * interested in and deregistered for these events.</p>
     *
     * <hr>
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param IEventSubscriber $Subscriber
     *            The subscriber to remove.
     */
    public function remSubscriber(IEventSubscriber $Subscriber);
}
