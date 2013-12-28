<?php
/**
 * InvalidClassException.php | 20 Nov 2013
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

namespace BLW\Model; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

use Exception;

/**
 * Makes reporting errors just easy.
 *
 * @package BLW\Core
 * @api BLW
 * @version 1.0.0
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @link http://php.net/LogicException PHP Reference > LogicException Class
 */
final class InvalidClassException extends \BLW\Type\LogicException
{
    /**
     * Overloads parent constructor
     *
     * @param int $status Current status of the class.
     * @param name $message
     * <ul>
     * <li><b>%header%</b>: <code>class::function(arguments):</code> </li>
     * <li><b>%status%</b>: Status code of current object.</li>
     * <li><b>%args%</b>: All arguments as a string.</li>
     * <li><b>%class%</b>: Class of of function.</li>
     * <li><b>%func%</b>: Function with invalid argument.</li>
     * </ul>
     * @param int $code Exception code.
     * @param \Exception $previous Previous Exception.
     * @return void
     */
    public function __construct($status, $message = '%header% Current class is currupted. Status: %status%.', $code = 0, Exception $previous = NULL)
    {
        $Replacements = $this->GetFields();
        $message      = str_replace(array_keys($Replacements), array_values($Replacements), $message);
        $message      = str_replace('%status%', @strval($status), $message);
        $message     .= $Replacements['%caused%'];

        \LogicException::__construct($message, $code, $previous);
    }
}

return true;