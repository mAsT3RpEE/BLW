<?php
/**
 * ActionParser.php | Dec 31, 2013
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
namespace BLW\Model; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

/**
 * Actions Generator. Used by BLW class.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class ActionParser extends \BLW\Type\Singleton
{
    /**
     * @var string ACTION Action key in arrays / json.
     */
    const ACTION       = 'a';

    /**
     * @var string OBJECT Object key in arrays / json.
     */
    const OBJECT       = 'o';

    // Action Types
    const TYPE_GET     = 0x0001;
    const TYPE_POST    = 0x0002;
    const TYPE_COOKIE  = 0x0004;
    const TYPE_SESSION = 0x0008;
    const TYPE_DB      = 0x0010;
    const TYPE_OTHER   = 0x0080;

    /**
     * Hook that is called when a new instance is created.
     * @note Format is <code>mixed function (\BLW\Interface\Event $Event)</code>.
     * @return \BLW\Interfaces\Object $this
     */
    final public static function doCreate()
    {
        parent::doCreate();
        \BLW::$Self->ParseActions();
        return \BLW::$Self;
    }

    /**
     * Parses Actions from $_GET.
     * return void
     */
    private function ParseGet()
    {
        $Action = new \stdClass();

        if (isset($_GET[self::ACTION]) && isset($_GET[self::OBJECT])) {

            if (is_scalar($_GET[self::ACTION]) && is_scalar($_GET[self::OBJECT])) {
                $Action->Name    = 'Action.' . $_GET[self::ACTION];
                $Action->Type    = self::TYPE_GET;
                $Action->Objects = NULL;
                $Action->Object  = $_GET[self::OBJECT];
                $this[]          = clone $Action;
            }

            elseif (is_scalar($_GET[self::ACTION]) && is_array($_GET[self::OBJECT])) {
                $Action->Name    = 'Action.' . $_GET[self::ACTION];
                $Action->Type    = self::TYPE_POST;
                $Action->Objects = new \ArrayIterator($_GET[self::OBJECT]);
                $Action->Object  = $Action->Objects->valid()
                    ? $Action->Objects->current()
                    : NULL
                ;
                $this[]          = clone $Action;
            }

            else {
                trigger_error('Invalid get action.', E_USER_NOTICE);
            }
        }

        unset($Action);
    }

    /**
     * Parses Actions from $_POST.
     * return void
     */
    private function ParsePost()
    {
        $Action  = new \stdClass();

        if (isset($_POST[self::ACTION]) && isset($_POST[self::OBJECT])) {

            if (is_scalar($_POST[self::ACTION]) && is_scalar($_POST[self::OBJECT])) {
                $Action->Name    = 'Action.' . $_POST[self::ACTION];
                $Action->Objects = NULL;
                $Action->Object  = @strval($_POST[self::OBJECT]);
                $this[]          = clone $Action;
            }

            elseif (is_scalar($_POST[self::ACTION]) && is_array($_POST[self::OBJECT])) {
                $Action->Name    = 'Action.' . $_POST[self::ACTION];
                $Action->Objects = new \ArrayIterator($_POST[self::OBJECT]);
                $Action->Object  = $Action->Objects->valid()
                    ? $Action->Objects->current()
                    : NULL
                ;
                $this[]          = clone $Action;
            }

            else {
                trigger_error('Invalid post action.', E_USER_NOTICE);
            }
        }

        unset($Action);
    }

    /**
     * Parses Actions from $_GET and $_POST.
     * return \BLW\Interfaces\Object $this
     */
    public function ParseActions()
    {
        while(count($this)) {
            unset($this[0]);
        }

        $this->ParseGet();
        $this->ParsePost();

        return $this;
    }
}

return true;