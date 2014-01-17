<?php
/**
 * Control.php | Jan 09, 2014
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
 * Core Control interface.
 *
 * <h4>Notice:</h4>
 *
 * <p>All controls must either implement this interface</p>
 *
 * <hr>
 * @package BLW\Core
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 */
interface Control extends \BLW\Interfaces\Singleton
{
    /**
     * Performs a control action.
     * @param string $Action action to perform.
     * @return \BLW\Interfaces\Singleton $this
     */
    public function doAction($Action);
}