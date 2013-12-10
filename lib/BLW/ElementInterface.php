<?php
/**
 * ElementInterface.php | Dec 01, 2013
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
 *	@package BLW\Core
 *	@version 1.0.0
 *	@author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

/**
 * Core BLW Object Interface.
 * 
 * <h4>Notice:</h4>
 * 
 * <p>All Elements must either implement this interface or
 * extend the <code>\BLW\Element</code> class.
 * 
 * <hr>
 * 
 * @package BLW\Core
 * @api BLW
 * @since 0.1.0
 * @version 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ mAsT3RpEE's Zone > Projects > BLW
 */

interface ElementInterface extends \BLW\ObjectInterface
{
    /**
     * Returns the current elements document or creates one if it doesnt exist.
     * @return \DOMDocument Current Object's <code>DOMDocument</code>.
     */
    public function & Document();
    
	/**
	 * Converts HTML string into DOMNodes and ataches them to the object.
	 * @param string $HTML HTML string to load.
	 * @return \BLW\ElementInterface $this
	 */
	public function LoadHTML($HTML);
	
	/**
	 * Loads Nodes from a DOMDocument.
	 * @param \DOMDocument $Document Document to Add to current Object.
	 * @param string $isDocument Wheather to load the Entire document or just its body.
	 * @return \BLW\ElementInterface $this
	 */
	public function AddDocument(\DOMDocument $Document, $isDocument = false);
	
	/**
	 * Adds a DOMNode to the current object.
	 * @param \DOMNode $Node Node to Add to Object.
	 * @return \BLW\ElementInterface $this
	 */
	public function AddNode(\DOMNode $Node);
}