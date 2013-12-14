<?php
/**
 * ObjectInterface.php | Nov 29, 2013
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

interface ObjectInterface extends \Iterator, \ArrayAccess, \Countable, \SeekableIterator, \Serializable
{
	const	INVALID_OPTION   = 0x1000;
	const	INVALID_CALLBACK = 0x2000;
	const	RESERVED_1       = 0x4000;
	const	RESERVED_2       = 0x8000;
	
    /**
     * Sanitize object ID / Label / Name.
     * @note Raises warning if label is not a string and returns empty string.
     * @param string $Label String to sanitize.
     * @return string Returns the sanitized label.
     */
    public static function SanitizeLabel($Label);
    
    /**
     * Validates options passed to Object::create().
     * @see \BLW\Object::__construct()
     * @param mixed $Options Options to validate
     * @return bool Return <code>true</code> if options are valid <code>false</code> otherwise.
     */
    public static function ValidateOptions($Label);
    
    /**
     * Builds Options used by an object.
     * 
     * <h4>Note:</h4>
     * 
     * <p>This has been purposelely made into a static function to limit the
     * capabilities of this function. If you need more functionallity (such as
     * access to $this), then create a function to further contruct object
     * after creation and overload Object::create()</p>
     * 
     * <hr>
     * @param mixed $Options
     * @return \stdClass Returns built options as an object.
     */
    public static function BuildOptions($Options);
    
    /**
     * Validates that a label is valid.
     * @param string $Label String to validate
     * @return bool Return <code>true</code> if label is valid.
     */
    public static function ValidateLabel($Label);
    
    /**
     * Creates a valid Object ID / Label / Name.
     * @note Raises warning if Input is not scaler.
     * @param string|int $Input ID can be biased to help regenerate ID's.
     * @return string Returns the new ID. Returns NULL on errors.
     */
    public static function Nounce($Input = NULL);
    
    /**
     * Initializes Class for subsequent use.
     * @param array $Data Optional initialization data.
     * @return array Returns the options generated. Used by child classes.
     */
    public static function init(array $Data = array());
    
    /**
     * Creates a new instance of the object.
     * @param array $Options Options to use in initializing class.
     * @return \BLW\ObjectInterface Returns a new instance of the class.
     */
    public static function create($Options = array());
    
    /**
     * Hook that is called when a new instance is created.
     * @note Format is <code>mixed function (\BLW\ObjectInterface $o)</code>.
     * @param \Closure $Function Function to call after object has been created.
     * @return \BLW\ObjectInterface $this
     */
    public function onCreate(\Closure $Funtion = NULL);
    
    /**
     * Fetches the current ID of the object.
     * @return string Returns the ID of the current class.
     */
    public function GetID();
    
    /**
     * Changes the ID of the current object.
     * @param string $ID New ID to give Object
     * @return \BLW\ObjectInterface $this
    */
    public function SetID($ID);
    
    /**
     * Hook that is called on change of ID.
     * @note Format is <code>mixed function (string $ID, string $OldID, \BLW\ObjectInterface $o)</code>.
     * @param \Closure $Function Function to call after ID has changed.
     * @return \BLW\ObjectInterface $this
     */
    public function onSetID(\Closure $Function);
    
    /**
     * Returns options used by class.
     * @internal Can be overloaded to add more options, etc
     * @return \stdClass Returns Options used by the object.
     */
    public function GetOptions();
    
    /**
     * Retrieves the current parent of the object.
     * @return \BLW\ObjectInterface
     */
    public function GetParent();
    
    /**
     * Sets parent of the current object if NULL.
     * @internal For internal use only.
     * @internal This is a one shot function (Only works once).
     * @param \BLW\ObjectInterface $o
     * @return \BLW\ObjectInterface $this
     */
    public function SetParent(\BLW\ObjectInterface &$Parent);
    
    /**
     * Clears parent of the current object.
     * @return \BLW\ObjectInterface $this
     */
    public function ClearParent();
    
    /**
     * Get the current status flag of the object.
     * @return int Returns the current status flags of the object.
     */
    public function Status();
    
    /**
     * Clears the status flag of the current object.
     * @return \BLW\ObjectInterface $this
     */
    public function ClearStatus();
    
    /**
     * Returns the child with current ID.
     * @note Changes the current context to the child.
     * @param string $ID Object ID of child to return.
     * @return \BLW\ObjectInterface Returns <code>NULL</code> if parent does not exits.
     */
    public function& child($ID);
    
    /**
     * Returns the parent of the current object.
     * @note Changes the current context to the parent.
     * @return \BLW\ObjectInterface Returns <code>NULL</code> if parent does not exits.
     */
    public function& parent();
    
    /**
     * Calls an anonymous function on each child of the function.
     * @note Function format: <code>mixed funtion ($o, $Index)</code>
     * @param \Closure $Function Function to call.
     * @return \BLW\ObjectInterface $this
     */
    public function each(\Closure $Function);
    
    /**
     * Call an anonymous function on object and all its descendants.
     * @note Function format: <code>mixed funtion ($o, $Index)</code>
     * @param \Closure $Function Funtion to call.
     * @return \BLW\ObjectInterface $this
     */
    public function walk(\Closure $Function);
    
    /**
     * Hook that is called when a child is added.
     * @note Format is <code>mixed function (\BLW\ObjectInterface $o, int $Index)</code>.
     * @param \Closure $Function Function to call after ID has changed.
     * @return \BLW\ObjectInterface $this
     */
    public function onAdd(\Closure $Function = NULL);
    
    /**
     * Hook that is called when a child is changed.
     * @note Format is <code>mixed function (\BLW\ObjectInterface $o, int $Index)</code>.
     * @param \Closure $Function Function to call after ID has changed.
     * @return \BLW\ObjectInterface $this
     */
    public function onUpdate(\Closure $Function = NULL);
    
    /**
     * Hook that is called when a child is deleted.
     * @note Format is <code>mixed function (\BLW\ObjectInterface $o, int $Index)</code>.
     * @param \Closure $Function Function to call after ID has changed.
     * @return \BLW\ObjectInterface $this
     */
    public function onDelete(\Closure $Function = NULL);
    
    /**
     * Loads and object data from an <code>obj.xxx.min.php</code> file.
     * @param string $Data Custom Data to reinstate class. (Used for info that cannot be serialized)
     * @return \BLW\Object $this
     */
    public function Load(array $Data = array());
        
    /**
     * Saves an element to an obj.xxx.min.php file.
     * @throws \BLW\InvalidArgumentException If <code>$File</code> is not a string.
     * @throws \BLW\FileError If unable to create / write to file.
     * @param string $File File to save the object to.
     * @return \BLW\Object $this
     */
    public function Save($File = NULL, array $Data = array());
}