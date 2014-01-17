<?php
/**
 * Monolog.php | Jan 07, 2014
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
namespace BLW\Model\Logger; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

use Monolog\Handler\RotatingFileHandler;

/**
 * Logging class interface that utilizes monolog.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 */
class Monolog extends \BLW\Type\Adaptor implements \BLW\Interfaces\Logger
{
    /**
     * @var string TARGET_CLASS Used by GetInstance to generate instance of class
     */
    protected static $_Class = '\\Monolog\\Logger';

    /**
     * @var array $DefaultOptions Default options used by class if not set in constructor.
     */
    public static $DefaultOptions = array(
         'DataSource' => 'BLW.log'
        ,'Level'      => \Monolog\Logger::NOTICE
    );

    /**
     * @var bool $Initialized Used to store class information status.
     */
    protected static $_Initialized = false;

    /**
     * Initializes Class for subsequent use.
     * @param array $Data Optional initialization data.
     * @return array Returns the options generated. Used by child classes.
     */
    public static function Initialize(array $Data = array())
    {
        // Initialize self
        if(!self::$_Initialized || isset($Data['hard_init'])) {
            self::$DefaultOptions   = array_replace(self::$DefaultOptions, $Data);
            self::$_Initialized     = true;

            unset(self::$DefaultOptions['hard_init']);
        }

        // Return Options
        return self::$DefaultOptions;
    }

    /**
     * Hook that is called when a new instance is created.
     * @return \BLW\Interfaces\Object $this
     */
    public static function doCreate()
    {
        $self    = parent::doCreate();

        // Add default handler
        $Handler = new RotatingFileHandler(self::$DefaultOptions['DataSource'], 7, self::$DefaultOptions['Level']);

        $self->GetSubject()->pushHandler($Handler);
    }

    /**
     * Fetches the current ID of the object.
     * @return string Returns the ID of the current class.
     */
    public function GetID()
    {
        return $this->GetSubject()->getName();
    }

    /**
     * System is unusable.
     * @param string $message
     * @param array $context
     * @return \BLW\Interfaces\Logger $this
     */
    public function emergency($message, array $context = array())
    {
        $this->GetSubject()->info($message, $context);
        return $this;
    }

    /**
     * Action must be taken immediately.
     * @param string $message
     * @param array $context
     * @return \BLW\Interfaces\Logger $this
     */
    public function alert($message, array $context = array())
    {
        $this->GetSubject()->alert($message, $context);
        return $this;
    }

    /**
     * Critical conditions.
     * @param string $message
     * @param array $context
     * @return \BLW\Interfaces\Logger $this
     */
    public function critical($message, array $context = array())
    {
        $this->GetSubject()->critical($message, $context);
        return $this;
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error($message, array $context = array())
    {
        $this->GetSubject()->error($message, $context);
        return $this;
    }

    /**
     * Exceptional occurrences that are not errors.
     * @param string $message
     * @param array $context
     * @return null
     */
    public function warning($message, array $context = array())
    {
        $this->GetSubject()->warning($message, $context);
        return $this;
    }

    /**
     * Normal but significant events.
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice($message, array $context = array())
    {
        $this->GetSubject()->notice($message, $context);
        return $this;
    }

    /**
     * Interesting events.
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info($message, array $context = array())
    {
        $this->GetSubject()->info($message, $context);
        return $this;
    }

    /**
     * Detailed debug information.
     * @param string $message
     * @param array $context
     * @return null
     */
    public function debug($message, array $context = array())
    {
        $this->GetSubject()->debug($message, $context);
        return $this;
    }

    /**
     * Logs with an arbitrary level.
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        $this->GetSubject()->log($level, $message, $context);
        return $this;
    }

    /**
     * Hook that is called just before an object is serialized.
     * @return \BLW\Interfaces\Logger $this
     */
    public function doSerialize()
    {
        try {
            // Remove all handlers
            while ($this->GetSubject()->popHandler());
        }

        catch (\Exception $e) {}

        return $this;
    }

    /**
     * Hook that is called just after an object is unserialized.
     * @return \BLW\Interfaces\Logger $this
     */
    public function doUnserialize()
    {
        // Add default handler
        if(!empty(static::$DefaultOptions['DataSource'])) {
            $Handler = new RotatingFileHandler(static::$DefaultOptions['DataSource'], 7, static::$DefaultOptions['Level']);

            $this->GetSubject()->pushHandler($Handler);
        }

        return $this;
    }
}

return true;