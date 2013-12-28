<?php
/**
 * SymfonyMediator.php | Dec 28, 2013
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
 * @package BLW\Core
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

/**
 * Mediator class that handles _on and _do commands.
 * @package BLW\Core
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class SymfonyMediator extends \BLW\Type\Mediator
{
    /**
     * @var string $_Class Used by GetInstance to generate instance of class
     */
    protected static $_Class = '\\Symfony\\Component\\EventDispatcher\\EventDispatcher';

    /**
     * Triggers an event.
     * @param string $Action Action to trigger.
     * @param \BLW\Interfaces\Event $Event Event object associated with the event.
     * @return \BLW\Interfaces\Event Event used by trigger.
     */
    public function Trigger($Action, \BLW\Interfaces\Event $Event)
    {
        return $this->GetSubject()->dispatch($Action, $Event);
    }

    /**
     * Registers a function to handle an event.
     * @note Format is <code>function (\BLW\Interface\Event $Event)</code>.
     * @param string $Action Action to register.
     * @param callable $Function Function to call when event is triggered.
     * @param int $Priority Priority of the function. (Higher value = Higher priority)
     * @return \BLW\Interfaces\Object $this
     */
    public function Register($Action, $Function, $Priority = 0)
    {
        if (is_callable($Function)) {
            $this->GetSubject()->addListener($Action, $Function, @intval($Priority));
        }

        else {
            throw new \BLW\Model\InvalidArgumentException(2);
        }

        return $this;
    }

    /**
     * Deregister a function from handling an event.
     * @param string $Action Action to register.
     * @param callable $Function Function to call when event is triggered.
     * @return \BLW\Interfaces\Object $this
     */
    public function Deregister($Action, $Function)
    {
        if (is_callable($Function)) {
            $this->GetSubject()->removeListener($Action, $Function);
        }

        else {
            throw new \BLW\Model\InvalidArgumentException(2);
        }

        return $this;
    }
}

return true;