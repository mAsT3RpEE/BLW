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
namespace BLW\Type; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

/**
 * Core BLW Object Class.
 *
 * <h3>About</h3>
 *
 * <p>All Objects must extend this class or
 * implement the <code>\BLW\Interface\Object</code> interface.</p>
 *
 * <h4>Note:</h4>
 *
 * <p>Whenever <code>$DefaultOptions</code> is used you must
 * also define <code>$_Initialized</code></p>
 *
 * <p>Object must implement dynamic properties</p>
 * <hr>
 * @package BLW\Core
 * @api BLW
 * @since 0.1.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 */
abstract class Object extends \SplDoublyLinkedList implements \BLW\Interfaces\Object
{
    /**
     * @var array $DefaultOptions Default options used by class if not set in constructor.
     * @api BLW
     * @since 0.1.0
     * @see \BLW\Type\Object::___construct() __construct()
     */
    public static $DefaultOptions = array(
    );

    /**
     * @var bool $Initialized Used to store class information status.
     */
    protected static $_Initialized = false;

    /**
     * @var \BLW\Interfaces\Mediator $Mediator Mediator associated with the class.
     * @api BLW
     * @since 1.0.0
     * @see \BLW\Type\Object::GetMediator() GetMediator()
     * @see \BLW\Type\Object::SetMediator() SetMediator()
     */
    protected static $_Mediator = NULL;

    /**
     * @var string $Mediator_ID ID used to track events.
     */
    private $_MediatorID = NULL;

    /**
     * @var array $Decorators List of decorators associated with the object.
     * @api BLW
     * @since 1.0.0
     * @see \BLW\Type\Object::AddDecorator() AddDecorator()
     * @see \BLW\Type\Object::RemDecorator() RemDecorator()
     */
    private $_Decorators = array();

    /**
     * @var string $ID Id of the current object amongst it's siblings.
     * @see \BLW\Type\Object::GetID() GetID()
     * @see \BLW\Type\Object::SetID() SetID()
     */
    private $_ID = '';

    /**
     * @var \BLW\Interfaces\Object $Parent Pointer to current object Parent.
     */
    private $_Parent = NULL;

    /**
     * @var string $OldID Previous ID of Object.
     */
    private $_OldID = '';

    /**
     * @var int $Status Current status flag of the class.
     */
    protected $_Status = 0;

    /**
     * @var int $_Current Index of last child to use with hooks.
     */
    protected $_Current = 0;

    /**
     * @var \stdClass $Options Constructor Options
     */
    public $Options = NULL;

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
    final public function __construct($Options = array())
    {
        // Options
        if(!static::ValidateOptions($Options)) {
            throw new \BLW\Model\InvalidArgumentException(0);
            return;
        }

        if($Options instanceof \BLW\Interfaces\Object) {
            $this->Options = $Options->GetOptions();
        }

        else {
            $this->Options = static::BuildOptions($Options);
        }

        // Class ID
        if(isset($this->Options->ID)) {

            // Set ID
            $this->_ID = static::SanitizeLabel($this->Options->ID);

            unset($this->Options->ID);

            // Validate
            if(empty($this->_ID)) {
                // Invalid
                $this->_Status &= self::INVALID_OPTION;
                throw new \BLW\Model\InvalidArgumentException(0, '%header% Invalid ID $Options[`ID`].');
                return;
            }
        }

        else {
            $this->_ID = static::BuildLabel();
        }

        // Parent
        if(isset($this->Options->Parent)) {
            $this->_Parent = is_object($this->Options->Parent)
                ? $this->Options->Parent
                : NULL
            ;

            unset($this->Options->Parent);
        }

        // MediatorID
        $this->_MediatorID = $this->_MediatorID ?: spl_object_hash($this);

        // Change global blw_self to this
        \BLW::$Self = $this;

        // OnCreate Hook
        static::doCreate();
    }

    /**
     * Validates options passed to Object::__construct().
     * @see \BLW\Type\Object::__construct()
     * @param mixed $Options Options to validate
     * @return bool Return <code>true</code> if options are valid <code>false</code> otherwise.
     */
    public static function ValidateOptions($Options)
    {
        return is_array($Options) || $Options instanceof \BLW\Interfaces\Object;
    }

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
    public static function BuildOptions($Options)
    {
        if(is_array($Options)) {
            return (object)(array_replace(static::$DefaultOptions, $Options));
        }

        return new \stdClass;
    }

    /**
     * Sanitize object ID / Label / Name.
     * @note Raises warning if label is not a string and returns empty string.
     * @param string $Label String to sanitize.
     * @return string Returns the sanitized label.
     */
    final public static function SanitizeLabel($Label)
    {
        $clean = trim(substr(@strval($Label), 0, 40));
        $clean = str_replace(array(' ', "\t", "\n", '-'), '_', $clean);

        return $clean;
    }

    /**
     * Validates that a label is valid.
     * @param string $Label String to validate
     * @return bool Return <code>true</code> if label is valid.
     */
    public static function ValidateLabel($Label)
    {
        return !empty($Label) && $Label === static::SanitizeLabel($Label);
    }

    /**
     * Creates a valid Object ID / Label / Name.
     * @note Raises warning if Input is not scaler.
     * @param string|int $Input Input can be biased to help regenerate ID's.
     * @return string Returns the new ID. Returns <code>NULL</code> on errors.
     */
    public static function BuildLabel($Input = NULL)
    {
        return 'BLW_' . spl_object_hash(is_null($Input)
            ? (object)microtime()
            : (is_object($Input)
                ? $Input
                : (object)$Input
            )
        );
    }

    /**
     * Initializes Class for subsequent use.
     * @api BLW
     * @since 0.1.0
     * @param array $Data Optional initialization data.
     * @return array Returns the options generated. Used by child classes.
     */
    public static function Initialize(array $Data = array())
    {
        $class = get_called_class();

        if($class == __CLASS__) {

            // Initialize self
            if(!self::$_Initialized || isset($Data['hard_init'])) {
                self::$DefaultOptions   = array_replace(self::$DefaultOptions, $Data);
                self::$_Initialized     = true;
                self::$_Mediator        = \BLW\Model\SymfonyMediator::GetInstance();

                unset(self::$DefaultOptions['hard_init']);
            }

            // Return Options
            return self::$DefaultOptions;
        }

        else {
            self::InitializeChild($class, $Data);
        }

        // Return Options
        return $class::$DefaultOptions;
    }

    /**
     * @ignore
     */
    private static function InitializeChild($class, $Data = array())
    {
        // Initialize children
        if(!$class::$_Initialized || isset($Data['hard_init'])) {
            $Parent                 = get_parent_class($class);
            $class::$DefaultOptions = array_replace($Parent::Initialize(), $class::$DefaultOptions, $Data);
            $class::$_Initialized   = true;

            unset($class::$DefaultOptions['hard_init']);
        }
    }

    /**
     * Creates a new instance of the object.
     * @api BLW
     * @since 0.1.0
     * @param array $Options Options to use in initializing class.
     * @return \BLW\Interfaces\Object Returns a new instance of the class.
     */
    public static function GetInstance($Options = array())
    {
        return new static($Options);
    }

    /**
     * Fetches the current ID of the object.
     * @api BLW
     * @since 0.1.0
     * @return string Returns the ID of the current class.
     */
    final public function GetID()
    {
        return $this->_ID;
    }

    /**
     * Changes the ID of the current object.
     * @param string $ID New ID to give Object
     * @return \BLW\Interfaces\Object $this
     */
    final public function SetID($ID)
    {
        if(!static::ValidateLabel($ID)) {
            throw new \BLW\Model\InvalidArgumentException(0);
        }

        $this->_OldID = $this->_ID;
        $this->_ID    = $ID;

        $this->doSetID();
    }

    /**
     * Returns options used by class.
     * @internal Can be overloaded to add more options, etc
     * @return \stdClass Returns Options used by the object.
     */
    public function GetOptions()
    {
        $Options         = clone $this->Options;
        $Options->Parent = $this->_Parent;
        $Options->ID     = $this->_ID;
        return $Options;
    }

    /**
     * Retrieves the current parent of the object.
     * @return \BLW\Interfaces\Object Returns <code>NULL</code> if no parent is set.
     */
    final public function GetParent()
    {
        return $this->_Parent;
    }

    /**
     * Sets parent of the current object if NULL.
     * @internal For internal use only.
     * @internal This is a one shot function (Only works once).
     * @param \BLW\Interfaces\Object $Parent Parent of current object.
     * @return \BLW\Interfaces\Object $this
     */
    final public function SetParent(\BLW\Interfaces\Object $Parent)
    {
        if(!$this->_Parent instanceof \BLW\Interfaces\Object || $this->_Parent === \BLW::$Base) {
            $this->_Parent = $Parent;
        }

        return $this;
    }

    /**
     * Clears parent of the current object.
     * @return \BLW\Interfaces\Object $this
     */
    final public function ClearParent()
    {
        $this->_Parent = NULL;

        return $this;
    }

    /**
     * Get the current status flag of the object.
     * @return int Returns the current status flags of the object.
     */
    final public function Status()
    {
        return $this->_Status;
    }

    /**
     * Clears the status flag of the current object.
     * @return \BLW\Interfaces\Object $this
     */
    final public function ClearStatus()
    {
        $this->_Status = 0;
    }

    /**
     * Returns the parent of the current object.
     * @note Changes the current context to the parent.
     * @return \BLW\Interfaces\Object Returns <code>NULL</code> if parent does not exits.
     */
    final public function& parent()
    {
        \BLW::$Self = $this->_Parent;
        return \BLW::$Self;
    }

    /**
     * Loads and object data from an <code>obj.xxx.min.php</code> file.
     * @api BLW
     * @since 0.1.0
     * @param string $Data Custom Data to reinstate class. (Used for info that cannot be serialized)
     * @return \BLW\Interfaces\Object $this
     */
    public function Load(array $Data = array())
    {
        ;;;;;

        return $this;
    }

    /**
     * Saves an element to an obj.xxx.min.php file.
     * @api BLW
     * @since 0.1.0
     * @throws \BLW\Model\InvalidArgumentException If <code>$File</code> is not a string.
     * @throws \BLW\FileError If unable to create / write to file.
     * @param string $File File to save the object to.
     * @return \BLW\Interfaces\Object $this
     */
    final public function Save($File = NULL, array $Data = array())
    {
        if(!is_string($File)) {
            throw new \BLW\Model\InvalidArgumentException(0);
            return $this;
        }

        $File = strpos($File, '.php') > 0
            ? $File
            : sprintf('%s/obj.%s.%s-min.php', __DIR__, get_class($this), $this->GetID())
        ;

        $Contents = sprintf(
            '<?php '
            .'namespace %s;'
            ."if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return NULL;}"
            .'\\'.get_class($this).'::Initialize();'
            .'return unserialize(%s)->Load(%s);'
            ,__NAMESPACE__
            ,var_export(serialize($this), true)
            ,var_export($Data, true)
        );

        if(@!file_put_contents($File, $Contents)) {
            throw new \BLW\Model\FileException($File, 'Unable to save object.');
            return $this;
        }

        return $this;
    }

    /**
     * Gets mediator object associated with the class.
     * @return \BLW\Interfaces\Mediator Returns <code>NULL</code> if no mediator exists.
     */
    final public static function GetMediator()
    {
        return static::$_Mediator instanceof \BLW\Interfaces\Mediator
            ? static::$_Mediator
            : self::$_Mediator
        ;
    }

    /**
     * Sets mediator object associated with the class.
     * @note Mediators are assiciated classwide instead of per instance using <code>Initialize</code> method.
     * @param \BLW\Interfaces\Mediator $Mediator Mediator to associate with the class.
     * @return void
     */
    final public static function SetMediator(\BLW\Interfaces\Mediator $Mediator)
    {
        static::$_Mediator = $Mediator;
    }

    /**
     * Activates a mediator event.
     * @param string $Name Event ID to activate.
     * @param \BLW\Interfaces\Event $Event Event object associated with the event.
     * @return \BLW\Interfaces\Object $this
     */
    public function _do($Name, \BLW\Interfaces\Event $Event)
    {
        // NOTE: Sometimes $this->_Decorators is not an array during serialization
        if(is_array($this->_Decorators)) {

            foreach ($this->_Decorators as $Decorator) {
                $Decorator->DecorateDo($Name, $Event, $this);
            }
        }

        static::GetMediator()->Trigger( $this->_MediatorID . '.' . $Name, $Event);
    }

    /**
     * Registers a function to execute on a mediator event.
     * @note Format is <code>mixed function (\BLW\Model\Event\SetID $Event)</code>.
     * @param string $Name Event ID to attach to.
     * @param callable $Action Function to call.
     * @param int $Priority Priotory of $Action. (Higher priority = Higher Importance)
     * @return \BLW\Interfaces\Object $this
     */
    public function _on($Name, $Action, $Priority = 0)
    {
        if (!is_callable($Action)) {
            throw new \BLW\Model\InvalidClassException(2);
            return $this;
        }

        foreach ($this->_Decorators as $Decorator) {
            $Decorator->DecorateOn($Name, $Action, $this);
        }

        static::GetMediator()->Register( $this->_MediatorID . '.' . $Name, $Action, @intval($Priority));

        return $this;
    }

    /**
     * Notifies a base class that a new decorator has been added.
     * @param \BLW\Interfaces\Decorator $Decorator Decorator object to add.
     * @return \BLW\Interfaces\Object $this
     */
    public function AddDecorator(\BLW\Interfaces\Decorator $Decorator)
    {
        $this->_Decorators[] = $Decorator;
        return $this;
    }

    /**
     * Notifies a base class that a new decorator has been removed.
     * @param \BLW\Interfaces\Decorator $Decorator Decorator object to add.
     * @return \BLW\Interfaces\Object $this
     */
    public function RemDecorator(\BLW\Interfaces\Decorator $Decorator)
    {
        if (($key = array_search($Decorator, $this->_Decorators, true)) !== false) {
            unset($this->_Decorators[$key]);
        }

        return $this;
    }

    /**
     * Hook that is called when a new instance is created.
     * @return \BLW\Interfaces\Object $this
     */
    public static function doCreate()
    {
        static::GetMediator()->Trigger(get_called_class() . '.Create', new \BLW\Model\Event\General(\BLW::$Self));

        return \BLW::$Self;
    }

    /**
     * Hook that is called when a new instance is created.
     * @api BLW
     * @since 0.1.0
     * @note Format is <code>mixed function (\BLW\Interfaces\Event $Event)</code>.
     * @param callable $Function Function to call after object has been created.
     * @return \BLW\Interfaces\Object $this
     */
    public static function onCreate($Function)
    {
        if(is_callable($Function)) {
            static::GetMediator()->Register(get_called_class() . '.Create', $Function);
        }

        else {
            $this->_Status &= static::INVALID_CALLBACK;
            throw new \BLW\Model\InvalidClassException($this->_Status);
        }

        return \BLW::$Self;
    }

    /**
     * Hook that is called on change of ID.
     * @return \BLW\Interfaces\Object $this
     */
    public function doSetID()
    {
        $this->_do('SetID', new \BLW\Model\Event\General($this));

        return $this;
    }

    /**
     * Hook that is called on change of ID.
     * @api BLW
     * @since 0.1.0
     * @note Format is <code>mixed function (\BLW\Interfaces\Event $Event)</code>.
     * @throws \BLW\Model\InvalidArgumentException If <code>$Function</code> is not a valid callback.
     * @param callable $Function Function to call after ID has changed.
     * @return \BLW\Interfaces\Object $this
     */
    public function onSetID($Function)
    {
        if(is_callable($Function)) {
            $this->_on('SetID', $Function);
        }

        else {
            $this->_Status &= static::INVALID_CALLBACK;
            throw new \BLW\Model\InvalidClassException($this->_Status);
        }

        return $this;
    }

    /**
     * Hook that is called just before an object is serialized.
     * @return \BLW\Interfaces\Object $this
     */
    public function doSerialize()
    {
        $this->_do('Serialize', new \BLW\Model\Event\General($this));

        return $this;
    }

    /**
     * Hook that is called just before an object is serialized.
     * @api BLW
     * @since 0.1.0
     * @note Format is <code>mixed function (\BLW\Interfaces\Event $Event)</code>.
     * @param \Closure $Function Function to call before object is serialized.
     * @return \BLW\Interfaces\Object $this
     */
    public function onSerialize($Function)
    {
        if(is_callable($Function)) {
            $this->_on('Serialize', $Function);
        }

        else {
            $this->_Status &= static::INVALID_CALLBACK;
            throw new \BLW\Model\InvalidClassException($this->_Status);
        }

        return $this;
    }

    /**
     * Hook that is called just after an object is unserialized.
     * @return \BLW\Interfaces\Object $this
     */
    public function doUnSerialize()
    {
        $this->_do('UnSerialize', new \BLW\Model\Event\General($this));

        return $this;
    }

    /**
     * Hook that is called just after an object is unserialized.
     * @api BLW
     * @since 0.1.0
     * @note Format is <code>mixed function (\BLW\Interfaces\Event $Event)</code>.
     * @param \Closure $Function Function to call after Object has been unserialized.
     * @return \BLW\Interfaces\Object $this
     */
    public function onUnSerialize($Function)
    {
        if(is_callable($Function)) {
            $this->_on('UnSerialize', $Function);
        }

        else {
            $this->_Status &= static::INVALID_CALLBACK;
            throw new \BLW\Model\InvalidClassException($this->_Status);
        }

        return $this;
    }

    /**
     * Property methods.
     * @param string $name Method interacted with.
     * @param array $arguments Arguments passed to method
     */
    final public function __call($name, $arguments)
    {
        if(!isset($this->{$name})) {
            throw new \BadMethodCallException(sprintf('Call to undefined method: `%s::%s()`.', get_class($this), $name));
            return NULL;
        }

        elseif(!is_callable($this->{$name})) {
            throw new \BadMethodCallException(sprintf('%s::%s: Is not callable.', get_class($this), $name));
            return NULL;
        }

        return call_user_func_array($this->{$name}, $Params);
    }

    /**
     * All objects must have a string representation.
     * @note Default is the serialized form of the object.
     * @return string String value of object.
     */
	public function __toString()
	{
	    $String = $this->serialize();

	    foreach ($this->_Decorators as $Decorator) {
            $String = $Decorator->DecorateToString($String, $this);
        }

        return $String;
	}

    /**
     * Serializable interface.
     * @param \BLW\Interfaces\Iterator $Parent For internal use only.
     * @return string Serialized object.
     */
    final public function serialize(\BLW\Type\Object $Parent = NULL)
    {
        if($Parent instanceof static) {
            \SplDoublyLinkedList::rewind();

            $this->_ID     = $Parent->GetID();
            $this->Options = $Parent->GetOptions();

            $this->doSerialize();

            unset($this->Options->ID, $this->Options->Parent);

            $this->_Decorators = array();

            if(version_compare(PHP_VERSION, '5.4.0', '>=')) {
                \SplDoublyLinkedList::push(get_object_vars($this));
                return \SplDoublyLinkedList::serialize();
            }

            else {
                $data = iterator_to_array($this);
                array_push($data, get_object_vars($this));
                return serialize($data);
            }
        }

        $New = clone $this;

        return $New->serialize($this);
    }

    /**
     * Serializable interface.
     * @param string $serialized Serialized form of object
     * @return void
     */
    final public function unserialize($serialized)
    {
        if(version_compare(PHP_VERSION, '5.4.0', '>=')) {
            \SplDoublyLinkedList::unserialize($serialized);
        }

        else {

            if (is_array($Data = unserialize($serialized))) {

                foreach ($Data as $data) {
                    \SplDoublyLinkedList::push($data);
                }

                unset($Data);
            }

            else {
                throw new \RuntimeException('Unable to unserialize object');
                return;
            }
        }

        foreach (\SplDoublyLinkedList::pop() as $k => $v) {
            $this->{$k} = $v;
        }

        foreach ($this as $o) if ($o instanceof \BLW\Interfaces\Object) {
            $o->SetParent($this);
        }

        $this->doUnSerialize();
    }

    /**
     * @ignore
     */
    public function __clone()
    {
        $this->ClearParent();
    }
}

return true;