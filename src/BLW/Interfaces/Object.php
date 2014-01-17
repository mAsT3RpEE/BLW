<?php
/**
 * Object.php | Nov 29, 2013
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
 * Core BLW Object Interface.
 *
 * <h3>About</h3>
 *
 * <p>All Objects must either implement this interface or
 * extend the <code>\BLW\Type\Object</code> class.</p>
 *
 * <hr>
 * @package BLW\Core
 * @api BLW
 * @since 0.1.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 */
interface Object extends \Serializable
{
    // ERRORS
	const	INVALID_OPTION   = 0x1000;
	const	INVALID_CALLBACK = 0x2000;
	const	RESERVED_1       = 0x4000;
	const	RESERVED_2       = 0x8000;

    /**
     * Constructor
     * @throws \BLW\Model\InvalidArgumentException
     * <ul>
     * <li>If <code>$Options</code> is of an invalid type.</li>
     * <li>If <code>$Options</code> contains an invalid value.</li>
     * </ul>
     * @param mixed $Options Constructor Options
     * @return void
     */
    public function __construct($Options);

    /**
     * Validates options passed to Object::__construct().
     * @see \BLW\Type\Object::__construct()
     * @param mixed $Options Options to validate
     * @return bool Return <code>true</code> if options are valid <code>false</code> otherwise.
     */
    public static function ValidateOptions($Options);

    /**
     * Builds Options used by an object.
     *
     * <h4>Note:</h4>
     *
     * <p>This has been purposelely made into a static function to limit the
     * capabilities of this function. If you need more functionallity (such as
     * access to <code>$this</code>), then overload
     * <code>Object::onCreate()</code> method.</p>
     *
     * <hr>
     * @param mixed $Options Options to build
     * @return \stdClass Returns built options as an object.
     */
    public static function BuildOptions($Options);

    /**
     * Sanitize object ID / Label / Name.
     * @note Raises warning if label is not a string and returns empty string.
     * @param string $Label String to sanitize.
     * @return string Returns the sanitized label.
     */
    public static function SanitizeLabel($Label);

    /**
     * Validates that a label is valid.
     * @param string $Label String to validate
     * @return bool Return <code>true</code> if label is valid.
     */
    public static function ValidateLabel($Label);

    /**
     * Creates a valid Object ID / Label / Name.
     * @note Raises warning if Input is not scaler.
     * @param string|int $Input Input can be biased to help regenerate ID's.
     * @return string Returns the new ID. Returns <code>NULL</code> on errors.
     */
    public static function BuildLabel($Input = NULL);

    /**
     * Initializes Class for subsequent use.
     * @api BLW
     * @since 0.1.0
     * @param array $Data Optional initialization data.
     * @return array Returns the options generated. Used by child classes.
     */
    public static function Initialize(array $Data = array());

    /**
     * Creates a new instance of the object.
     * @api BLW
     * @since 0.1.0
     * @param array $Options Options to use in initializing class.
     * @return \BLW\Interfaces\Object Returns a new instance of the class.
     */
    public static function GetInstance($Options = array());

    /**
     * Fetches the current ID of the object.
     * @api BLW
     * @since 0.1.0
     * @return string Returns the ID of the current class.
     */
    public function GetID();

    /**
     * Changes the ID of the current object.
     * @api BLW
     * @since 0.1.0
     * @param string $ID New ID to give Object
     * @return \BLW\Interfaces\Object $this
     */
    public function SetID($ID);

    /**
     * Returns options used by class.
     * @internal Can be overloaded to add more options, etc
     * @return \stdClass Returns Options used by the object.
     */
    public function GetOptions();

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
     * @return \BLW\Interfaces\Object $this
     */
    public function SetParent(\BLW\Interfaces\Object $Parent);

    /**
     * Clears parent of the current object.
     * @return \BLW\Interfaces\Object $this
     */
    public function ClearParent();

    /**
     * Get the current status flag of the object.
     * @return int Returns the current status flags of the object.
     */
    public function Status();

    /**
     * Clears the status flag of the current object.
     * @return \BLW\Interfaces\Object $this
     */
    public function ClearStatus();

    /**
     * Returns the parent of the current object.
     * @note Changes the current context to the parent.
     * @return \BLW\Interfaces\Object Returns <code>NULL</code> if parent does not exits.
     */
    public function& parent();

    /**
     * Loads and object data from an <code>obj.xxx.min.php</code> file.
     * @api BLW
     * @since 0.1.0
     * @param string $Data Custom Data to reinstate class. (Used for info that cannot be serialized)
     * @return \BLW\Interfaces\Object $this
     */
    public function Load(array $Data = array());

    /**
     * Saves an element to an obj.xxx.min.php file.
     * @api BLW
     * @since 0.1.0
     * @throws \BLW\Model\InvalidArgumentException If <code>$File</code> is not a string.
     * @throws \BLW\FileError If unable to create / write to file.
     * @param string $File File to save the object to.
     * @return \BLW\Interfaces\Object $this
     */
    public function Save($File = NULL, array $Data = array());

    /**
     * Gets mediator object associated with the class.
     * @return \BLW\Interfaces\Mediator Returns <code>NULL</code> if no mediator exists.
     */
    public static function GetMediator();

    /**
     * Sets mediator object associated with the class.
     * @note Mediators are assiciated classwide instead of per instance using <code>Initialize</code> method.
     * @param \BLW\Interfaces\Mediator $Mediator Mediator to associate with the class.
     * @return void
     */
    public static function SetMediator(\BLW\Interfaces\Mediator $Mediator);

    /**
     * Activates a mediator event.
     * @param string $Name Event ID to activate.
     * @param \BLW\Interfaces\Event $Event Event object associated with the event.
     * @return \BLW\Interfaces\Object $this
     */
    public function _do($Name, \BLW\Interfaces\Event $Event);

    /**
     * Registers a function to execute on a mediator event.
     * @note Format is <code>mixed function (\BLW\Model\Event\SetID $Event)</code>.
     * @param string $Name Event ID to attach to.
     * @param callable $Action Function to call.
     * @return \BLW\Interfaces\Object $this
     */
    public function _on($Name, $Action);

    /**
     * Notifies a base class that a new decorator has been added.
     * @param \BLW\Interfaces\Decorator $Decorator Decorator object to add.
     * @return \BLW\Interfaces\Object $this
     */
    public function AddDecorator(\BLW\Interfaces\Decorator $Decorator);

    /**
     * Notifies a base class that a new decorator has been removed.
     * @param \BLW\Interfaces\Decorator $Decorator Decorator object to add.
     * @return \BLW\Interfaces\Object $this
     */
    public function RemDecorator(\BLW\Interfaces\Decorator $Decorator);

    /**
     * Hook that is called when a new instance is created.
     * @return \BLW\Interfaces\Object $this
     */
    public static function doCreate();

    /**
     * Hook that is called when a new instance is created.
     * @api BLW
     * @since 0.1.0
     * @note Format is <code>mixed function (\BLW\Interfaces\Object $Event)</code>.
     * @param callable $Function Function to call after object has been created.
     * @return \BLW\Interfaces\Object $this
     */
    public static function onCreate($Function);

    /**
     * Hook that is called on change of ID.
     * @note Format is <code>mixed function (\BLW\Model\Event\SetID $Event)</code>.
     * @return \BLW\Interfaces\Object $this
     */
    public function doSetID();

    /**
     * Hook that is called on change of ID.
     * @note Format is <code>mixed function (\BLW\Interface\Event $Event)</code>.
     * @throws \BLW\Model\InvalidArgumentException If <code>$Function</code> is not callable.
     * @param callable $Function Function to call after ID has changed.
     * @return \BLW\Interfaces\Object $this
     */
    public function onSetID($Function);

    /**
     * Hook that is called just before an object is serialized.
     * @return \BLW\Object $this
     */
    public function doSerialize();

    /**
     * Hook that is called just before an object is serialized.
     * @note Format is <code>mixed function (\BLW\Interface\Event $Event)</code>.
     * @throws \BLW\Model\InvalidArgumentException If <code>$Function</code> is not callable.
     * @param callable $Function Function to call before object is serialized.
     * @return \BLW\Interfaces\Object $this
     */
    public function onSerialize($Function);

    /**
     * Hook that is called just after an object is unserialized.
     * @return \BLW\Interfaces\Object $this
     */
    public function doUnSerialize();

    /**
     * Hook that is called just after an object is unserialized.
     * @note Format is <code>mixed function (\BLW\Interface\Event $Event)</code>.
     * @throws \BLW\Model\InvalidArgumentException If <code>$Function</code> is not callable.
     * @param callable $Function Function to call after Object has been unserialized.
     * @return \BLW\Object $this
     */
    public function onUnSerialize($Function);

    /**
     * Property methods.
     * @param string $name Method interacted with.
     * @param array $arguments Arguments passed to method
     */
    public function __call($name, $arguments);

    /**
     * All objects must have a string representation.
     * @note Default is the serialized form of the object.
     * @return string String value of object.
     */
    public function __toString();
}