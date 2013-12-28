<?php
/**
 * RuntimeException.php | Dec 28, 2013
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
namespace BLW\Type; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

/**
 * Core exception class for Runtime Exceptions.
 * @package BLW\Core
 * @api BLW
 * @since 0.1.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 */
abstract class RuntimeException extends \RuntimeException implements \BLW\Interfaces\Exception
{
    /**
     * Generates fields to replace in messege string.
     * @return array Array of fields to replace in messege text.
     */
    public function GetFields()
    {
        $Trace     = $this->getTrace();
        $Arguments = print_r($Trace[0]['args'], true);

        preg_match_all('/^\s{1,4}\\[\d\\]\s*=>\s*(.*)/m', $Arguments, $Arguments);

        return array(
            '%header%' => '%class%::%func%(%args%):'
            ,'%args%'  => implode(', ', $Arguments[1])
            ,'%class%' => strval(@$Trace[0]['class'])
            ,'%func%'  => strval(@$Trace[0]['function'])
            ,'%caused%' => sprintf(' Caused by %s on line %d.', $this->getFile(), $this->getLine())
        );
    }
}