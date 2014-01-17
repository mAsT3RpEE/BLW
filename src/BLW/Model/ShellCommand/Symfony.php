<?php
/**
 * Symfony.php | Jan 09, 2014
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
 * @package BLW\Core
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model\ShellCommand; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

use BLW;

/**
 * Default BLW object.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 */
class Symfony extends \BLW\Type\Adaptor implements \BLW\Interfaces\ShellCommand
{
    /**
     * @var string TARGET_CLASS Used by GetInstance to generate instance of class
     */
    protected static $_Class = "\Symfony\\Component\\Process\\Process";

    /**
     * Hook that is called when a new instance is created.
     * @return \BLW\Interfaces\Adaptor $this
     */
    public static function doCreate()
    {
        $self = parent::doCreate();

        // Remove default timeout
        $self->GetSubject()->setTimeout(NULL);

        return $self;
    }

    /**
     * escapeshellarg alternative.
     * @throws \BLW\Model\InvalidArgumentException if argument cannot be represented as a string.
     * @param mixed $Argument Argument to sanitize.
     * @return string Sanitized Argument (Including leading space)
     */
    final public static function Argument($Argument)
    {
        $String = @strval($Argument);

        if(strpos($String, "\n") !== false) {
            throw new \BLW\Model\InvalidArgumentException(0);
        }

        elseif (preg_match('/^[^\s:;\'"<>?,;!@#$%^&*()+=$\\[\\]{}]+$/', $String)) {
            return ' '.$String;
        }

        elseif (!empty($String)) {
            return " '". addcslashes($String, '\\\'') . "'";
        }

        else {
            return '';
        }
    }

    /**
     * escapeshellarg alternative.
     * @throws \BLW\Model\InvalidArgumentException if <code>$Option</code> or <code>$value</code> are invalid.
     * @param mixed $Option Option to sanitize.
     * @param mixed $Value Option value to sanitize.
     * @return string Sanitized Option (Including leading space)
     */
    final public static function Option($Option, $Value = NULL)
    {
        if (empty($Option)) {
            return '';
        }

        $OptionString = @strval($Option);
        $ValueString  = @strval($Value);

        if (!preg_match('/^[\w_-]+$/', $OptionString)) {
            throw new \BLW\Model\InvalidArgumentException(0);
        }

        if(strpos($ValueString, "\n") !== false) {
            throw new \BLW\Model\InvalidArgumentException(1);
        }

        elseif (preg_match('#^[^\s:;\'"<>?,;!@$%^&*()+=$\\[\\]{}]+$#', $ValueString)) {
            return ' '.$OptionString.' '.$ValueString;
        }

        elseif (!empty($ValueString)) {
            return ' '.$OptionString."'". addcslashes($ValueString, '\\\'') . "'";
        }

        else {
            return ' '.$OptionString;
        }
    }

    /**
     * Runs the command.
     * @note Format of $Callback is <code>function (int $Type, string $Output)</code>.
     * @param callable $Callback callback function to handle output.
     * @return int The command exit code.
     */
    final public function Run($Callback = NULL)
    {
        if (is_null($Callback)) {
            $this->GetSubject()->run();
        }

        elseif (is_callable($Callback)) {
            $this->GetSubject()->run($Callback);
        }

        else {
            throw new \BLW\Model\InvalidArgumentException(0);
        }

        return $this;
    }

    /**
     * Gets the current output of the shell.
     * @return string STDOUT
     */
    final public function GetOutput()
    {
        return $this->GetSubject()->getOutput();
    }

    /**
     * Gets the current output of the shell.
     * @return string STDERR
     */
    final public function GetError()
    {
        return $this->GetSubject()->getErrorOutput();
    }

    /**
     * Returns the exit code of the shell command
     * @return int Exit Code
     */
    final public function ExitStatus()
    {
        return $this->GetSubject()->getExitCode();
    }

    /**
     * Gets process idle timeout.
     * @return float|null Current Timeout. Returns <code>NULL</code> if not set.
     */
    final public function GetTimeout()
    {
        return $this->GetSubject()->getIdleTimeout();
    }

    /**
     * Sets process idle timeout.
     * @param int|float $Timeout Maximum timme to wait for a response from process.
     * @return \BLW\Model\ShellCommand\Symfony
     */
    final public function SetTimeout($Timeout)
    {
        $this->GetSubject()->setTimeout(NULL);
        $this->GetSubject()->setIdleTimeout($Timeout);

        return $this;
    }

    /**
     * Hook that is called just before an object is serialized.
     * @return \BLW\Interfaces\Adaptor $this
     */
    public function doSerialize() {return $this;}

    /**
     * Hook that is called just after an object is unserialized.
     * @return \BLW\Interfaces\Adaptor $this
     */
    public function doUnSerialize() {return $this;}
}