<?php
/**
 * Adaptor.php | Dec 27, 2013
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
 * Core Adapter pattern class.
 *
 * <h4>Notice:</h4>
 *
 * <p>All Adaptor objects must either extend this class or
 * implement the <code>\BLW\Interfaces\Adaptor</code> interface.
 *
 * <hr>
 * @package BLW\Core
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 */
interface Adaptor extends \ArrayAccess, \Serializable
{
    /**
     * Creates an instance of the adaptor object.
     * @note Default creates
     * @param ...
     * @return \BLW\Interface\Adaptor
     */
    public static function GetInstance();

    /**
     * Getter for subject property.
     * @return mixed $subject The adaptor subject.
     */
    public function GetSubject();

    /**
     * Import child methods.
     * @param string $name Property interacted with.
     * @return mixed Overloaded method return value.
     */
    public function __call($name, array $arguments);

    /**
     * Import child properties.
     * @param string $name Property interacted with.
     * @return mixed Overloaded propery
     */
    public function __get($name);

    /**
     * Import child properties.
     * @param string $name Property interacted with.
     * @param mixed $value Value to set property to.
     * @return void
     */
    public function __set($name, $value);

    /**
     * Import child properties.
     * @param string $name Property interacted with.
     * @return bool Returns true if the dyanamic property exists.
     */
    public function __isset($name);

    /**
     * Import child properties.
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
}

return true;