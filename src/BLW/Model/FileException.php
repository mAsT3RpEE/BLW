<?php
/**
 * FileException.php | Dec 28, 2013
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
 * @package BLW
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

/**
 * Makes reporting errors just easy.
 * @package BLW\Core
 * @api BLW
 * @version 1.0.0
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://php.net/InvalidArgumentException Language Reference
 */
final class FileException extends \BLW\Type\RuntimeException
{
    /**
     * Overloads parent constructor
     *
     * @param int $File File exception is associated with.
     * @param string $message
     * <ul>
     * <li><b>%header%</b>: <code>class::function(arguments):</code>.</li>
     * <li><b>%argno%</b>: Invalid argument position.</li>
     * <li><b>%args%</b>: All arguments as a string.</li>
     * <li><b>%class%</b>: Class of of function.</li>
     * <li><b>%func%</b>: Function with invalid argument.</li>
     * </ul>
     * @param int $code Exception code.
     * @param \Exception $previous Previous Exception.
     * @return void
     */
    public function __construct($File, $message = NULL, $code = 0, \Exception $previous = NULL)
    {
        if (is_null($message)) {
        	$message = '%header% File error: (%file%) with access (%access%).';
        }

        $Replacements             = $this->GetFields();
        $Replacements['%file%']   = @strval($File);
        $Replacements['%access%'] = is_file($File)
            ? sprintf('%o', @fileperms($File))
            : 'invalid'
        ;

        $message  = str_replace(array_keys($Replacements), array_values($Replacements), $message);
        $message .= $Replacements['%caused%'];

        \RuntimeException::__construct($message, $code, $previous);
    }
}

return true;