<?php
/**
 * ActiveRecord.php | Dec 28, 2013
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
namespace BLW\Type; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

/**
 * Core ActiveRecord pattern class.
 *
 * <h4>Notice:</h4>
 *
 * <p>All <code>DAO</code> must either extend this class or
 * implement the <code>\BLW\Interfaces\ActiveRecord/code> interface.
 *
 * <hr>
 * @package BLW\Core
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 */
abstract class ActiveRecord extends \ActiveRecord\Model implements \BLW\Interfaces\ActiveRecord
{
}