<?php
/**
 * ObjectItem.php | Dec 27, 2013
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
namespace BLW\Model\Event; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

/**
 * Event for passing a set of objects one at a time.
 * @package BLW\Core
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class ObjectItem extends \BLW\Type\Event\Symfony
{
    /**
     * Constructor.
     * @param mixed $Subject The subject of the event, usually an object.
     * @param int $Index Index of argument amongst it's pears.
     * @return void
     */
    public function __construct($Subject, $Index = -1)
    {
        parent::__construct($Subject, array('Index' => $Index));
    }
}