<?php
/**
 * Factory.php | Dec 28, 2013
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
 * Core Factory pattern class.
 *
 * <h4>Notice:</h4>
 *
 * <p>All Factory objects must either extend this class or
 * implement the <code>\BLW\Interfaces\Factory</code> interface.
 *
 * <hr>
 * @package BLW\Core
 * @api BLW
 * @since 0.1.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 */
interface Factory extends \BLW\Interfaces\Singleton
{
    /**
     * Creates a BLW Library Object.
     * @api BLW
     * @since 0.1.0
     * @param string $Class Name of object to create.
     * @param array $Options Options passed to object.
     * @param bool $isExtention Load extention.
     * @return \BLW\Interfaces\Object Returns the newly created object.
     */
    public static function O($Class, array $Options = array(), $isExtention = false);
}