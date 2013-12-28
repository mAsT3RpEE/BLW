<?php
/**
 * Mediator.php | Dec 28, 2013
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
namespace BLW\Interfaces; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

/**
 * Core Mediator pattern interface.
 *
 * <h4>Notice:</h4>
 *
 * <p>All mediators must either implement this interface or
 * extend the <code>\BLW\Type\Mediator</code> class.
 *
 * <hr>
 * @package BLW\Core
 * @api BLW / Symfony
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 */
interface Mediator extends \BLW\Interfaces\Adaptor
{
    /**
     * Triggers an event.
     * @param string $Action Action to trigger.
     * @param \BLW\Interfaces\Event $Event Event object associated with the event.
     * @return \BLW\Interfaces\Event Event used by trigger.
     */
    public function Trigger($Action, \BLW\Interfaces\Event $Event);

    /**
     * Registers a function to handle an event.
     * @note Format is <code>function (\BLW\Interface\Event $Event)</code>.
     * @param string $Action Action to register.
     * @param callable $Function Function to call when event is triggered.
     * @param int $Priority Priority of the function. (Higher value = Higher priority)
     * @return \BLW\Interfaces\Event Event used by trigger.
     */
    public function Register($Action, $Function, $Priority = 0);

    /**
     * Deregister a function from handling an event.
     * @param string $Action Action to register.
     * @param callable $Function Function to call when event is triggered.
     * @return \BLW\Interfaces\Event Event used by trigger.
     */
    public function Deregister($Action, $Function);
}