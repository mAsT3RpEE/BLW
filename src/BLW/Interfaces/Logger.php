<?php
/**
 * Logger.php | Jan 07, 2014
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
 * Logging class interface.
 *
 * <h4>Notice:</h4>
 *
 * <p>All Logger objects must either implement this interface.</p>
 *
 * <hr>
 * @package BLW\Core
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 */
interface Logger extends \BLW\Interfaces\Adaptor, \Psr\Log\LoggerInterface
{
    /**
     * Initializes Class for subsequent use.
     * @api BLW
     * @since 1.0.0
     * @param array $Data Optional initialization data.
     * @return array Returns the options generated. Used by child classes.
     */
    public static function Initialize(array $Data = array());

    /**
     * Fetches the current ID of the object.
     * @api BLW
     * @since 1.0.0
     * @return string Returns the ID of the current class.
     */
    public function GetID();
}

return true;