<?php
/**
 * Event.php | Dec 27, 2013
 *
 * Copyright (c) 2013-2018 mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 *
 * @filesource
 * @copyright mAsT3RpEE's Zone
 * @license MIT
 */

/**
 *	@package BLW\Core
 *	@version 1.0.0
 *	@author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Interfaces;

/**
 * Core Event interface.
 *
 * <h4>Notice:</h4>
 *
 * <p>All Events must either implement this interface or
 * extend the <code>\BLW\Type\Event</code> class.
 *
 * <hr>
 * @package BLW\Core
 * @api BLW
 * @since 0.1.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 */
interface Event extends \ArrayAccess, \Iterator, \Countable, \Serializable
{
    /**
     * Getter for subject property.
     * @return mixed $subject The adaptor subject.
     */
    public function GetSubject();

    /**
     * Property methods.
     * @param string $name Property interacted with.
     * @return mixed Overloaded method return value.
     */
    public function __call($name, array $arguments);

    /**
     * Dynamic properties.
     * @param string $name Property interacted with.
     * @return mixed Overloaded propery
     */
    public function __get($name);

    /**
     * Dynamic properties.
     * @param string $name Property interacted with.
     * @param mixed $value Value to set property to.
     * @return void
     */
    public function __set($name, $value);

    /**
     * Dynamic properties.
     * @param string $name Property interacted with.
     * @return bool Returns true if the dyanamic property exists.
     */
    public function __isset($name);

    /**
     * Dynamic properties.
     * @param string $name Property interacted with.
     * @return mixed Overloaded propery
     */
    public function __unset($name);

    /**
     * All objects must have a string representation.
     * @note Default is the serialized form of the object.
     * @return string String value of object.
     */
    public function __toString();

    /**
     * Returns whether further event listeners should be triggered.
     * @api Symfony
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
     * <hr>
     * @api Symfony
     * @since 1.0.0
     * @return void
     */
    public function stopPropagation();
}

return true;