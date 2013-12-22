<?php
/**
 * AjaxElement.php | Dec 10, 2013
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
namespace BLW; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

use Symfony\Component\CssSelector\CssSelector;

/**
 * Class for elements that contain others.
 * @package BLW\Core
 * @api BLW
 * @version 1.0.0
 * @since 0.1.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class ElementContainer extends \BLW\Element
{
    /**
     * @var array $DefaultOptions Default options used by class if not set in constructor.
     * @api BLW
     * @since 0.1.0
     * @see \BLW\Object::__construct() Object::__construct()
     */
    public static $DefaultOptions = array(
		'HTML'		=> '<div></div>'
    );

	/**
	 * Overloads GetHTML.
	 * @see \BLW\Element::GetHTML()
	 * @return string HTML output of Form
	 */
	public function GetHTML()
	{
		return preg_replace('"(.*)(</'.$this->tag().'>)(.*)"is', '$1$3$2', parent::GetHTML(), 1);
	}
}

return ;