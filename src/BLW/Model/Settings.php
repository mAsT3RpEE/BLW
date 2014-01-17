<?php
/**
 * Settings.php | Dec 11, 2013
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
 * XXX.
 * @package BLW\Core
 * @api BLW
 * @version 1.0.0
 * @since 0.1.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class Settings extends \BLW\Type\Singleton
{
    /**
     * Initializes a child class for subsequent use.
     * @param array $Options Initialization options. (Automatically adds blw_cfg())
     * @return array Returns Options used / generated during init.
     */
    public static function Initialize(array $Data = array())
    {
        $ID = session_id();

        if (BLW_PLATFORM == 'standalone' && empty($ID) || isset($Data['hard_init'])) {

            if (!defined('STDIN')) {
                @sesssion_name('trackid');
                @session_start();
            }

            else {
                @session_id('STDIN');
                @session_start();
            }
        }

        return parent::Initialize($Data);
    }

    /**
     * Hook that is called when a new instance is created.
     * @return \BLW\Interfaces\Object $this
     */
    public static function doCreate()
    {
        if (!isset($_SESSION['BLW'])) {
            $_SESSION['BLW'] = array();
        }

        elseif (!is_array($_SESSION['BLW'])) {
            $_SESSION['BLW'] = array();
        }

        return parent::doCreate();
    }

    /**
     * Get a certain settings value.
     * @param string $Name Id of value to get
     * @return mixed|NULL Returns <code>NULL</code> if not already set.
     */
    public function Get($Name)
    {
        return isset($_SESSION['BLW'][$Name])
            ? $_SESSION['BLW'][$Name]
            : NULL
        ;
    }

    /**
     * Set a certain settings value.
     * @param string $Name Id of value to set.
     * @param mixed $Value New value.
     */
    public function Set($Name, $Value)
    {
        $_SESSION['BLW'][$Name] = $Value;
    }
}

return true;