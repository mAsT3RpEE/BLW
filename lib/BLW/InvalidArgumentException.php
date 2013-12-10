<?php
/**
 * InvalidArgumentException.php | Dec 2, 2013
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
 * @package BLW
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */

namespace BLW; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

/**
 * Makes reporting errors just easy.
 *
 * @package BLW\Core
 * @api BLW
 * @version 1.0.0
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @link http://php.net/InvalidArgumentException PHP Reference > InvalidArgumentException Class
 */

class InvalidClassException extends \InvalidArgumentException
{
    /**
     * Overloads parent constructor
     *
     * @param int $argno The argument that is invalid.
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
    public function __construct($argno, $message = '%header% Argument %argno% is invalid.', $code = 0, \Exception $previous = NULL)
    {
        $debug	= debug_backtrace();
        $args	= preg_replace('/\s+/', ' ', substr(print_r($debug[1]['args'], true), 5, -1));
        $file   = isset($debug[2])
            ? $debug[2]['file']
            : $debug[1]['file']
        ;
        $line   = isset($debug[2])
            ? $debug[2]['line']
            : $debug[1]['line']
        ;
        $message = str_replace('%header%',	'%class%::%func%(%args%):', $message);
        $message = str_replace('%argno%',	@strval($argno), $message);
        $message = str_replace('%args%',	$args, $message);
        $message = str_replace('%class%',	@$debug[1]['class'], $message);
        $message = str_replace('%func%',	@$debug[1]['function'], $message);
        
        $message .= sprintf(' Caused by %s on line %d.', $file, $line);
        
        parent::__construct($message, $code, $previous);
    }
}

return ;