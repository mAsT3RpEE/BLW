<?php
/**
 * ApplicationCommand.php | Jan 05, 2014
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
namespace BLW\Interfaces; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

/**
 * Interface for applications (both web and console).
 * @api BLW
 * @since 1.0.0
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 */
interface ApplicationCommand
{
    /**
     * Creates a new instance of the object.
     * @param ...
     * @return \BLW\Interfaces\ApplicationCommand Returns a new instance of the class.
     */
    public static function GetInstance(/* ... */);

    /**
     * Runs the command.
     * @internal Requires current view to be an application view.
     * @param ...
     * @return int The command exit code.
     */
    public function Run(/* ... */);

    /**
     * Get the string of the current command.
     * @return string ID / action of command.
     */
    public function GetID();

    /**
     * Set the string of the current command.
     * @param string $ID New application name.
     * @return \BLW\Interfaces\ApplicationCommand $this
     */
    public function SetID($ID);

    /**
     * Retrieves the current parent of the object.
     * @return \BLW\Interfaces\Object Returns <code>NULL</code> if no parent is set.
     */
    public function GetParent();

    /**
     * Sets parent of the current object if NULL.
     * @internal For internal use only.
     * @internal This is a one shot function (Only works once).
     * @param \BLW\Interfaces\Object $Parent Parent of current object.
     * @return \BLW\Interfaces\ApplicationCommand $this
     */
    function SetParent(\BLW\Interfaces\Object $Parent);

    /**
     * Clears parent of the current object.
     * @internal For internal use only.
     * @return \BLW\Interfaces\ApplicationCommand $this
     */
    public function ClearParent();

    /**
     * Returns the parent of the current object.
     * @note Changes the current context to the parent.
     * @return \BLW\Interfaces\Object Returns <code>NULL</code> if parent does not exits.
     */
    public function& parent();
}