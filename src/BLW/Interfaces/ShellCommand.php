<?php
/**
 * ShellCommand.php | Jan 05, 2014
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
namespace BLW\Interfaces; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

/**
 * Interface for applications (both web and console).
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 */
interface ShellCommand
{
    const MERGE_OUTPUT = ' 2>&1';

    /**
     * Creates an instance of the adaptor object.
     * @param string $CommandLine Commandline to run
     * @return \BLW\Interface\ShellCommand New adaptor instance.
     */
    public static function GetInstance(/* ... */);

    /**
     * Adds an argument to commandline.
     *
     * <h4>Example:</h4>
     *
     * <pre><code>ShellCommand::Argument('http://www.google.com')</code></pre>
     *
     * <h4>
     * @param mixed $Argument Argument to sanitize.
     * @return string Sanitized Argument (Including leading space)
     */
    public static function Argument($Argument);

    /**
     * Adds an argument to commandline.
     *
     * <h4>Example:</h4>
     *
     * <pre><code>ShellCommand::Option('--x', 'foo')</code></pre>
     *
     * <h4>
     * @param mixed $Option Option to sanitize.
     * @param mixed $Value Option value to sanitize.
     * @return string Sanitized Option (Including leading space)
     */
    public static function Option($Option, $Value = NULL);

    /**
     * Runs the command.
     * @note Format of $Callback is <code>function (int $Type, string $Output)</code>.
     * @param callable $Callback Callback function to handle output.
     * @param ...
     * @return int The command exit code.
     */
    public function Run($Callback = NULL /*, ... */);

    /**
     * Gets process idle timeout.
     * @return float|null Current Timeout. Returns <code>NULL</code> if not set.
     */
    public function GetTimeout();

    /**
     * Sets process idle timeout.
     * @param int|float $Timeout Maximum timme to wait for a response from process.
     * @return \BLW\Model\ShellCommand\Symfony
     */
    public function SetTimeout($Timeout);

    /**
     * Gets the current output of the shell.
     * @return string STDOUT
     */
    public function GetOutput();

    /**
     * Gets the current output of the shell.
     * @return string STDERR
     */
    public function GetError();

    /**
     * Returns the exit code of the shell command
     * @return int Exit Code
     */
    public function ExitStatus();

    /**
     * Retrieves the current parent of the object.
     * @return \BLW\Interfaces\Object Returns <code>NULL</code> if no parent is set.
     */
    public function GetParent();

    /**
     * Sets parent of the current object if NULL.
     * @internal For internal use only.
     * @internal This is a one shot function (Only works once).
     * @param \BLW\Interfaces\Object $Parent Parent of current object.
     * @return \BLW\Interfaces\ShellCommand $this
     */
    function SetParent(\BLW\Interfaces\Object $Parent);

    /**
     * Clears parent of the current object.
     * @internal For internal use only.
     * @return \BLW\Interfaces\ShellCommand $this
     */
    public function ClearParent();

    /**
     * Returns the parent of the current object.
     * @note Changes the current context to the parent.
     * @return \BLW\Interfaces\Object Returns <code>NULL</code> if parent does not exits.
     */
    public function& parent();
}