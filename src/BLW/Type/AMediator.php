<?php
/**
 * AMediator.php | Dec 28, 2013
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
 * Core Mediator pattern class.
 *
 * <h4>Notice:</h4>
 *
 * <p>All mediators must implement <code>IMediator</code> interface</p>
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
abstract class AMediator extends \BLW\Type\ASerializable implements \BLW\Type\IMediator
{

#############################################################################################
# Iterable Trait
#############################################################################################

    /**
     * Pointer to current parent of object.
     *
     * @var \BLW\Type\IObject $Parent
     */
    protected $_Parent = null;

#############################################################################################
# Mediator Trait
#############################################################################################

    /**
     * Subscribed callbacks registered with register().
     *
     * @see \BLW\Type\IMediator::register() IMediator::register()
     *
     * @var array[] $_Callbacks [protected]
     */
    protected $_Callbacks = array();

    /**
     * Cached version of $_Callbacks sorted by priority.
     *
     * @var callable[] $_CachedCallbacks
     */
    private $_CachedCallbacks = array();

#############################################################################################




#############################################################################################
# Iterable Trait
#############################################################################################

    /**
     * Retrieves the current parent of the object.
     *
     * @return \BLW\Type\IObject Returns <code>null</code> if no parent is set.
     */
    final public function getParent()
    {
        return $this->_Parent;
    }

    /**
     * Sets parent of the current object if null.
     *
     * @internal This is a one shot function (Only works once).
     *
     * @param mised $Parent
     *            New parent of object. (IObject|IContainer|IObjectStorage)
     * @return int Returns a <code>DataMapper</code> status code.
     */
    final public function setParent($Parent)
    {
        // Make sur object is not a parent of itself
        if ($Parent === $this)
            return IDataMapper::INVALID;

        // Make sure parent is valid
        elseif (! $Parent instanceof IObject && ! $Parent instanceof IContainer && ! $Parent instanceof IObjectStorage && ! $Parent instanceof IWrapper)
            return IDataMapper::INVALID;

        // Make sure parent is not already set
        elseif (! $this->_Parent instanceof IObject && ! $this->_Parent instanceof IContainer && ! $this->_Parent instanceof IObjectStorage) {

            // Update parent
            $this->_Parent = $Parent;
            return IDataMapper::UPDATED;
        }

        // Else dont update parent
        else
            return IDataMapper::ONESHOT;
    }

    /**
     * Clears parent of the current object.
     *
     * @access private
     * @internal For internal use only.
     *
     * @return int Returns a <code>DataMapper</code> status code.
     */
    final public function clearParent()
    {
        $this->_Parent = null;
        return IDataMapper::UPDATED;
    }

    /**
     * Get the ID of the object.
     *
     * @return string Current ID.
     */
    abstract public function getID();

#############################################################################################
# Mediator Trait
#############################################################################################

    /**
     * Registers a function to handle an event.
     *
     * <h4>Format</h4>
     *
     * <pre>void function (IEvent $Event, string $EventID, IMediator $Mediator)</pre>
     *
     * <hr>
     *
     * @param string $EventName
     *            Action to register.
     * @param callable $Callback
     *            Function to call when event is triggered.
     * @param int $Priority
     *            Priority of the function. (Higher value = Higher priority)
     * @return bool <code>TRUE</code> on success <code>FALSE</code> on failure.
     */
    abstract public function register($EventName, $Callback, $Priority = 0);

    /**
     * Deregister a function from handling an event.
     *
     * @param string $EventName
     *            Action to deregister.
     * @param callable $Callback
     *            Function to call when event is triggered.
     */
    abstract public function deregister($EventName, $Callback);

    /**
     * Triggers an event.
     *
     * @param string $EventName
     *            Action to trigger.
     * @param \BLW\Type\IEvent $Event
     *            Event object associated with the event.
     * @param int $flags
     *            Mediation flags.
     */
    abstract public function trigger($EventName, IEvent $Event = null, $flags = 0);

    /**
     * Checks if an event has any subscribers
     *
     * @param string $EventName
     *            Action to check.
     * @return bool <code>TRUE</code> if callbacks are registered <code>FALSE</code> otherwise.
     */
    abstract public function isRegistered($EventName);

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
     * @param IEventSubscriber $Subscriber
     *            The subscriber to add.
     * @return bool <code>TRUE</code> on success <code>FALSE</code> on failure.
     */
    abstract public function addSubscriber(IEventSubscriber $Subscriber);

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
     * @param IEventSubscriber $Subscriber
     *            The subscriber to remove.
     */
    abstract public function remSubscriber(IEventSubscriber $Subscriber);

#############################################################################################

}
