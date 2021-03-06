<?php
/**
 * Symfony.php | Dec 28, 2013
 *
 * Copyright (c) 2013-2018 Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 *
 * @filesource
 * @copyright Fabien Potencier <fabien@symfony.com>
 * @license MIT
 */

/**
 *
 * @package BLW\Core
 * @version GIT: 0.2.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model\Mediator;

use Closure;
use Traversable;
use BLW\Type\IEvent;
use BLW\Type\IEventSubscriber;
use BLW\Type\IMediator;
use BLW\Type\AMediator;
use BLW\Model\GenericEvent;
use Jeremeamia\SuperClosure\SerializableClosure;

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
 * Mediator based on Symfony Event dispatcher.
 *
 * <h3>Introduction</h3>
 *
 * <p>Because I only needed 1 part of the event dispatcher and it was less than 200
 * lines of code, I decided to simply place it here. The main reason was the fact
 * that symfony use typehinting based on its own intearnal classes. So programmes
 * cannot implement their own version of <code>IEvent</code> and have it work with
 * symfony's event dispatcher.</p>
 *
 * <p>If they decide to change their parameter type hinting to Interfaces I will
 * remove this code and simply extend IEvent to incorporate their interface.</p>
 *
 * <p>This version also adds the benefit of converting closures to their
 * serializable form using <code>SerializableClosure</code> class.</p>
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +------------------+
 * | MEDIATOR                                          |<------| SERIALIZABLE     |
 * +---------------------------------------------------+       | ================ |
 * | register():                                       |<------| Serializable     |
 * |                                                   |       +------------------+
 * | $ID:         string                               |       | ITERABLE         |
 * | $EventName:  string                               |       +------------------+
 * | $Callback:   callable                             |
 * | $Priority:   int                                  |
 * +---------------------------------------------------+
 * | deregister():                                     |
 * |                                                   |
 * | $ID:        string                                |
 * | $EventName: string                                |
 * | $Callback:  callable                              |
 * +---------------------------------------------------+
 * | trigger():                                        |
 * |                                                   |
 * | $ID:         string                               |
 * | $EventName:  string                               |
 * | $Event:      IEvent                               |
 * | $flags:      int                                  |
 * +---------------------------------------------------+
 * | isRegistered(): bool                              |
 * +---------------------------------------------------+
 * | addSubscriber(): bool                             |
 * |                                                   |
 * | $Subscriber: IEventSubscriber                     |
 * +---------------------------------------------------+
 * | remSubscriber(): bool                             |
 * |                                                   |
 * | $Subscriber: IEventSubscriber                     |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\Core
 * @api     BLW
 * @since   1.0.0
 * @link https://github.com/symfony/EventDispatcher/blob/master/EventDispatcher.php EventDispatcher.php
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class Symfony extends \BLW\Type\AMediator
{

#############################################################################################
# Iterator Trait
#############################################################################################

    /**
     * Get the ID of the object.
     *
     * @return string Current ID.
     */
    public function getID()
    {
        return '[Mediator:Symfony]';
    }

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
     * @param integer $Priority
     *            Priority of the function. (Higher value = Higher priority)
     * @return boolean <code>TRUE</code> on success <code>FALSE</code> on failure.
     */
    public function register($EventName, $Callback, $Priority = 0)
    {
        // Ensure closures are serializable
        if ($Callback instanceof Closure) {
            $Callback = new SerializableClosure($Callback);
        }

        // Add callback
        $this->_Callbacks[$EventName][$Priority][] = $Callback;

        // Delete cache
        unset($this->_CachedCallbacks[$EventName]);

        // Done
        return true;
    }

    /**
     * Deregister a function from handling an event.
     *
     * @param string $EventName
     *            Action to deregister.
     * @param callable $Callback
     *            Function to call when event is triggered.
     */
    public function deregister($EventName, $Callback)
    {
        // Ensure closures are serializable
        if ($Callback instanceof Closure) {
            $Callback = new SerializableClosure($Callback);
        }

        // Is event registered?
        if (isset($this->_Callbacks[$EventName])) {
            // Search Event for callback
            foreach ($this->_Callbacks[$EventName] as $Priority => $Callbacks) {
                if (($i = array_search($Callback, $Callbacks, false)) !== false) {
                    // Delete callback and cache
                    unset($this->_Callbacks[$EventName][$Priority][$i], $this->_CachedCallbacks[$EventName]);
                }
            }
        }
    }

    /**
     * Triggers an event.
     *
     * @param string $EventName
     *            Action to trigger.
     * @param \BLW\Type\IEvent $Event
     *            Event object associated with the event.
     * @param integer $flags
     *            Mediation flags.
     */
    public function trigger($EventName, IEvent $Event = null, $flags = 0)
    {
        // Are there any subscribed callbacks for event?
        if (isset($this->_Callbacks[$EventName])) {
            // Dispatch event
            $this->_dispatch($this->getCallbacks($EventName), $EventName, $Event ?  : new GenericEvent());
        }
    }

    /**
     * Checks if an event has any subscribers
     *
     * @param string $EventName
     *            Action to check.
     * @return boolean <code>TRUE</code> if callbacks are registered <code>FALSE</code> otherwise.
     */
    public function isRegistered($EventName)
    {
        return (bool) count($this->getCallbacks($EventName));
    }

    /**
     * Return an array of callbacks associated with an event.
     *
     * @param string $EventName
     *            Action to get callbacks for.
     * @return callable[] Array of callbacks sorted by priority.
     */
    public function getCallbacks($EventName)
    {
        // Cache results
        if (! isset($this->_CachedCallbacks[$EventName])) {
            $this->_sortCallbacks($EventName);
        }

        // Return results
        return $this->_CachedCallbacks[$EventName];
    }

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
     * @return boolean <code>TRUE</code> on success <code>FALSE</code> on failure.
     */
    public function addSubscriber(IEventSubscriber $Subscriber)
    {
        $return = 1;

        // Loop through each subscriver event
        foreach ($Subscriber->getSubscribedEvents() as $EventName => $Params) {

            // Params is a string (method name)
            if (is_string($Params)) {
                // register event with subscriber method
                $return &= $this->register($EventName, array(
                    $Subscriber,
                    $Params
                ));

            // Params is an array with 1st param as a string
            } elseif (is_array($Params) ? is_string($Params[0]) : false) {

                // register event with subscriber method and priority
                $return &= $this->register($EventName, array(
                        $Subscriber,
                        $Params[0]
                ), isset($Params[1]) ? $Params[1] : 0);

            // Params is array|Traversable
            } elseif (is_array($Params) ?: $Params instanceof Traversable) {

                // Register each method / priority to event
                foreach ($Params as $NewParams) {
                    $return &= $this->register($EventName, array(
                            $Subscriber,
                            $NewParams[0]
                    ), isset($NewParams[1]) ? $NewParams[1] : 0);
                }
            }
        }

        // Done
        return (bool) $return;
    }

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
    public function remSubscriber(IEventSubscriber $Subscriber)
    {
        // Loop through each subscriver event
        foreach ($Subscriber->getSubscribedEvents() as $EventName => $Params) {

            // Params is array[]|Traversable[]
            if (is_array($Params) && is_array($Params[0]) ?: $Params[0] instanceof Traversable) {

                // Deregister each method
                foreach ($Params as $NewParams) {
                    $this->deregister(
                        $EventName,
                        array(
                            $Subscriber,
                            $NewParams[0]
                        )
                    );
                }

            // Otherwise, Deregester method
            } else {
                $this->deregister(
                    $EventName,
                    array(
                        $Subscriber,
                        is_string($Params) ? $Params : $Params[0]
                    )
                );
            }
        }
    }

    /**
     * Invokes all callbacks registered with an event id.
     *
     * @param callable[] $Callbacks
     *            The event callbacks.
     * @param string $EventName
     *            The event identifier to trigger.
     * @param \BLW\Type\IEvent
     *            $Event The event object to pass to the event handlers/callbacks.
     */
    protected function _dispatch($Callbacks, $EventName, IEvent $Event)
    {
        // Loop through all callbacks
        foreach ($Callbacks as $Callback) {

            // Call callback
            call_user_func($Callback, $Event, $EventName, $this);

            // Check if propergation has stopped
            if ($Event->isPropagationStopped()) {
                break;
            }
        }
    }

    /**
     * Sorts the internal list of Callbacks for the given event by priority.
     *
     * @param string $EventName
     *            The event identifier to sort.
     * @return void
     */
    private function _sortCallbacks($EventName)
    {
        // Reset cache
        $this->_CachedCallbacks[$EventName] = array();

        // Do callbacks exist for Event ID?
        if (isset($this->_Callbacks[$EventName])) {

            // Sort callbkacks by priority
            krsort($this->_Callbacks[$EventName]);

            // Merge callbacks
            $this->_CachedCallbacks[$EventName] = call_user_func_array('array_merge', $this->_Callbacks[$EventName]);
        }
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
