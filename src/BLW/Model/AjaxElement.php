<?php
/**
 * AjaxElement.php | Nov 29, 2013
 *
 * Copyright (c) mAsT3RpEE's Zone
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
 * Default BLW DOM Element object.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 */
class AjaxElement extends \BLW\Type\AjaxElement
{
    /**
     * @var array $DefaultOptions Default options used by class if not set in constructor.
     * @see \BLW\Type\Object::__construct() Object::__construct()
     */
    public static $DefaultOptions = array(
        'HTML'              => '<span class="ajax"></span>'
        ,'DocumentVersion'  => '1.0'
        ,'AJAX'             => array()
        ,'Type'             => self::TYPE_COOKIE
    );

    /**
     * @var bool $Initialized Used to store class information status.
     */
    protected static $_Initialized = false;

    /**
     * Get inline JavaScript used by object.
     *
     * <h4>Note:</h4>
     *
     * <p>Should return <code>NULL</code> if no inline JavaScript is set.</p>
     * <hr>
     * @return string
     */
    public function InlineJS()
    {
        return NULL;
    }
}

return true;