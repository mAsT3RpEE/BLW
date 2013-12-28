<?php
/**
 * BLW.php | Dec 31, 2013
 *
 * <h3>Introduction</h3>
 *
 * <p>Defines global BLW class used for configuration, initialization
 * and object creation.</p>
 *
 * <hr>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 *
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 * @copyright 2013-2018 mAsT3RpEE's Zone
 * @license MIT
 */

use BLW\Model\Object;
use BLW\Model\Element;
use BLW\Model\Settings;
use BLW\Model\ActionParser;

if (!defined('BLW'))
{
    define('BLW', '1.0.0');

    require_once  __DIR__ . '/Interfaces/Object.php';
    require_once  __DIR__ . '/Type/Object.php';

    /**
     * Main class used to configure, initialize and run blw library.
     * @package BLW\Core
     * @api BLW
     * @since 1.0.0
     * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
     * @link http://mast3rpee.tk/projects/BLW/ BLW Library
     */
    final class BLW extends \BLW\Type\Object
    {
        /**
         * @var \BLW\Module\Object $Base Base object that all others should attatch to.
         */
        public static $Base = NULL;

        /**
         * @var \BLW\Interfaces\Object $Self Current / Last object.
         */
        public static $Self = NULL;

        /**
         * @var \BLW\Model\ActionHandler $_Actions Iterator with all parsed actions.
         */
        private static $_Actions = NULL;

        /**
         * Loads core BLW Library Interfaces for performance.
         * @return void
         */
        public static function LoadInterfaces()
        {
            require_once __DIR__ . '/Interfaces/Exception.php';

            require_once __DIR__ . '/Interfaces/ActiveRecord.php';
            require_once __DIR__ . '/Interfaces/Adaptor.php';
            require_once __DIR__ . '/Interfaces/Event.php';

            require_once __DIR__ . '/Interfaces/Decorator.php';
            require_once __DIR__ . '/Interfaces/Iterator.php';
            require_once __DIR__ . '/Interfaces/Mediator.php';
            require_once __DIR__ . '/Interfaces/Singleton.php';

            require_once __DIR__ . '/Interfaces/Element.php';
            require_once __DIR__ . '/Interfaces/Factory.php';
        }

        /**
         * Loads core BLW Library Types for performance.
         * @return void
         */
        public static function LoadTypes()
        {
            require_once __DIR__ . '/Type/LogicException.php';
            require_once __DIR__ . '/Type/RuntimeException.php';

            require_once __DIR__ . '/Type/ActiveRecord.php';
            require_once __DIR__ . '/Type/Adaptor.php';
            require_once __DIR__ . '/Type/Event.php';

            require_once __DIR__ . '/Type/Decorator.php';
            require_once __DIR__ . '/Type/Iterator.php';
            require_once __DIR__ . '/Type/Mediator.php';
            require_once __DIR__ . '/Type/Singleton.php';

            require_once __DIR__ . '/Type/Element.php';
            require_once __DIR__ . '/Type/Factory.php';

            require_once __DIR__ . '/Type/AjaxElement.php';
        }

        /**
         * Loads core BLW Library Models for performance.
         * @return void
         */
        public static function LoadModels()
        {
            require_once __DIR__ . '/Model/SymfonyMediator.php';
            require_once __DIR__ . '/Model/Event/General.php';
            require_once __DIR__ . '/Model/Event/ObjectItem.php';

            require_once __DIR__ . '/Model/ActionParser.php';
            require_once __DIR__ . '/Model/Element.php';
            require_once __DIR__ . '/Model/Object.php';
            require_once __DIR__ . '/Model/Settings.php';
        }

        /**
         * Loads core BLW Library classes performance.
         * @return void
         */
        public static function LoadLibraries()
        {
            self::LoadInterfaces();
            self::LoadTypes();
            self::LoadModels();
        }

        /**
         * Wrapper for BLW configuration array.
         * @api BLW
         * @since 1.0.0
         * @param bool $Reset Reset value of Configuration.
         * @return array Returns a static array that contains all configuration.
         */
        public static function& Config($Reset = false)
        {
            /*
             * Use static variable in order to have same config var across
            * all object instances
            */
            static $Configuration = array();

            if(!!$Reset) $Configuration = array(
                    'func_die' => function ($Title, $Messege) {
                        die(sprintf('<b>%s</b>: %s', $Title, $Message));
                    }
            );

            return $Configuration;
        }

        /**
         * Overloards PHP's die() method.
         * @link http://www.php.net/manual/en/function.die.php die()
         * @param string $Title Title of error messege.
         * @param string $Message Body of error messege.
         * @return void
         */
        public static function Error($Title, $Message)
        {
            if (!is_string($Title)) {
                throw new \BLW\Model\InvalidArgumentException(0);
            }

            elseif (!is_string($Message)) {
                throw new \BLW\Model\InvalidArgumentException(1);
            }

            else {
                $cfg = self::Config();
                call_user_func($cfg['func_die'], $Title, $Message);
            }
        }

        /**
         * Loads <code>.ini</code> file.
         * @param string $File Name of file to load (including `.ini`).
         * @return void
         */
        private static function LoadConfigINI($File = 'BLW.ini')
        {
            $cfg = self::Config(true);

            if(defined('BLW_PLUGIN_DIR')) {

                if (file_exists(BLW_PLUGIN_DIR . DIRECTORY_SEPARATOR . $File)) {

                    $cfg = parse_ini_file(BLW_PLUGIN_DIR . DIRECTORY_SEPARATOR . $File, true);

                    foreach ($cfg['CORE'] as $k => $v) {
                        if (!defined('BLW_' . $k)) define('BLW_' . $k, $v);
                    }
                }
            }

            elseif(file_exists($File)) {

                $cfg = parse_ini_file($File);

                foreach ($cfg['CORE'] as $k => $v) {
                    if (!defined('BLW_' . $k)) define('BLW_' . $k, $v);
                }
            }

            else {
                // No ini file -_-
                if(!defined('BLW_PLATFORM') || !defined('BLW_PLUGIN_DIR')) {
                    $Messege  = "<p>There doesn't seem to be a <code>$File</code> file. Please Install / Reinstall blw library.</p>";
                    self::Error('BLW Configuration Error', $Messege);
                }
            }
        }

        /**
         * Default values if not defined in config files.
         * @ignore
         * @return void
         */
        private static function LoadConfigDefault()
        {
            if(!defined('BLW_LIB_PHAR'))        { define('BLW_LIB_PHAR',        dirname(__DIR__));                                                              }
            if(!defined('BLW_APP_PHAR'))        { define('BLW_APP_PHAR',        BLW_LIB_PHAR);                                                                  }

            if(!defined('BLW_ASSETS_DIR'))      { define('BLW_ASSETS_DIR',      BLW_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'assets');                               }
            if(!defined('BLW_ASSETS_URL'))      { define('BLW_ASSETS_URL',      BLW_PLUGIN_URL . '/assets');                                                    }
            if(!defined('BLW_FRONTEND_DIR'))    { define('BLW_FRONTEND_DIR',    BLW_APP_PHAR .   sprintf('%1$ssrc%1$sBLW%1$sFrontend', DIRECTORY_SEPARATOR));   }
            if(!defined('BLW_FRONTEND_URL'))    { define('BLW_FRONTEND_URL',    BLW_ASSETS_URL . '/BLW.Frontend.');                                             }
            if(!defined('BLW_BACKEND_DIR'))     { define('BLW_BACKEND_DIR',     BLW_APP_PHAR .   sprintf('%1$ssrc%1$sBLW%1$sBackend', DIRECTORY_SEPARATOR));    }
            if(!defined('BLW_BACKEND_URL'))     { define('BLW_BACKEND_URL',     BLW_ASSETS_URL . '/BLW.Backend.');                                              }

            if(BLW_PLATFORM != 'standalone') {
                if(!defined('BLW_EXTENTION'))   { define('BLW_EXTENTION',       '\\_' . BLW_PLATFORM);                                                          }
            }

            else {
                if(!defined('BLW_EXTENTION'))   { define('BLW_EXTENTION',       '');                                                                            }
            }
        }

        /**
         * Configures BLW Libriary.
         * @api BLW
         * @since 0.1.0
         * @return void
         */
        public static function Configure()
        {
            // 1. Error Reporting
            error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );

            // 2. Config files
            self::LoadConfigINI();
            self::LoadConfigDefault();

            // 3. Validation
            if(!defined('BLW_PLATFORM') || !defined('BLW_PLUGIN_URL')) {
                trigger_error('`BLW_PLATFORM` or `BLW_PLUGIN_URL` are undefined. Config files may be corrupted.', E_USER_WARNING);
            }
        }

        /**
         * Initializes BlW Library.
         * @api BLW
         * @since 0.1.0
         * @return void
         */
        public static function Initialize(array $Data = array())
        {
            // Includes and defines
            self::LoadLibraries();
            self::Configure();

            // Object initialization
            Object::Initialize();
            Element::Initialize();
            Settings::Initialize();
            ActionParser::ClearInstance();

            // Globals
            self::$Base     = self::GetInstance();
            self::$_Actions = ActionParser::GetInstance();

            // Settings support
            if(defined('BLW_SETTINGS')) {
                self::Settings(true);
            }

            // Database support
            if(defined('BLW_DB')) {
                self::DB(true);
            }
        }

        /**
         * Handles converting Events into actions.
         * @param \BLW\Interfaces\Event $Event Event to test and convert.
         * @return void
         */
        public static function doAction(\BLW\Interfaces\Event $Event)
        {
            // Actions are matched against object ID's
            $Test = $Event->GetSubject()->GetID();

            // Try each action against object
            foreach (static::$_Actions as $Action) {

                // Object is scaler
                if (is_null($Action->Objects)) {
                    // Match object
                    if ($Action->Object == $Test) {
                        $Event->GetSubject()->_do($Action->Name, new \BLW\Model\Event\General($Action));
                    }
                }

                // Object is array
                else {
                    // Match objects
                    foreach ($Action->Objects as $ID) {
                        if ($ID === $Test) {
                            $Event->GetSubject()->_do($Action->Name, new \BLW\Model\Event\General($Action));
                        }
                    }
                }
            }
        }

        /**
         * Adds an action.
         *
         * <h3>About</h3>
         *
         * <p>Actions are events that are triggered based on object
         * events (ie Create).<p>
         *
         * <p>They are specific to a certain object id. An eample is
         * <code>AjaxElement</code> object that uses actions to handle
         * ajax requests.</p>
         *
         * <hr>
         * @param string $Action Event to turn into an action.
         */
        public static function AddAction($Action)
        {
            static::GetMediator()->Register($Action, array(__CLASS__, 'doAction'), 100);
        }

        /**
         * Removes an action.
         * @param string $Action
         */
        public static function RemAction($Action)
        {
            static::GetMediator()->Deregister($Action, array(__CLASS__, 'doAction'));
        }

        /**
         * Creates a BLW Library Object.
         * @api BLW
         * @since 0.1.0
         * @param string $Class Name of object to create.
         * @param array $Options Options passed to object.
         * @param bool $isExtention Load extention.
         * @return \BLW\Interfaces\Object Returns <code>NULL</code> if the class does not exist.
         */
        public static function O($Class, array $Options = array(), $isExtention = false)
        {
            $Static = sprintf('\\BLW\\%s%s', str_replace('.', '\\', $Class), $isExtention? BLW_EXTENTION : '');

            if(class_exists($Static)) {
                return new $Static($Options);
            }

            throw new \BLW\Model\InvalidArgumentException(0);
            return NULL;
        }
    }
}

return true;