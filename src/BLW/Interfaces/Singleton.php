<?php
/**
 * Singleton.php | Dec 28, 2013
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
 * Singleton pattern interface.
 * @package BLW\Core
 * @api BLW
 * @since 0.1.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 */
interface Singleton extends \BLW\Interfaces\Object
{
    /**
     * Changes the current singleton instance.
     * @param \BLW\Interfaces\Object $NewObject Object to set singleton value to.
     * @return bool Returns <code>TRUE</code> on success and <code>FALSE</code> on failure.
     */
    public static function SetInstance(\BLW\Interfaces\Singleton $NewObject);

    /**
     * Clears the current signleton instance.
     * @return \BLW\Interfaces\Object $this
     */
    public static function ClearInstance();

    /**
     * Hook that is called when instance is changed.
     * @see \BLW\Type\Singleton::onUpdate()
     * @return \BLW\Interfaces\Object $this
     */
    public function doUpdate();

    /**
     * Hook that is called when instance is changed.
     * @note Format is <code>mixed function (\BLW\Interfaces\Event $Event)</code>.
     * @see \BLW\Type\Singleton::doUpdate()
     * @param callable $Function Function to call on change.
     * @return \BLW\Interfaces\Object $this
     */
    public function onUpdate($Function);

    /**
     * Hook that is called when instance is deleted.
     * @see \BLW\Type\Singleton::onDelete()
     * @return \BLW\Interfaces\Object $this
     */
    public function doDelete();
    /**
     * Hook that is called when instance is deleted.
     * @note Format is <code>mixed function (\BLW\Interfaces\Event $Event)</code>.
     * @see \BLW\Type\Singleton::doDelete()
     * @param callable $Function Function to call on change.
     * @return \BLW\Interfaces\Object $this
     */
    public function onDelete($Function);
}