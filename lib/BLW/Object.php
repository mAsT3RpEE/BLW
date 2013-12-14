<?php
/**
 * Object.php | Nov 29, 2013
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

/**
 * Core BLW object.
 * 
 * <h3>About</h3>
 * 
 * <p>This is the core BLW object. All other Objects must extent this class
 * or implement it's interface.</p>
 * 
 * <hr>
 * @package BLW\Core
 * @api BLW
 * @version 1.0.0
 * @since 0.1.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * 
 * @link http://mast3rpee.tk/projects/BLW/ mAsT3RpEE's Zone > Projects > BLW
 */
class Object extends \SplDoublyLinkedList implements \BLW\ObjectInterface
{
    /**
     * @var array $DefaultOptions Default options used by class if not set in constructor.
     * 
     * @api BLW
     * @since 0.1.0
     * 
     * @see \BLW\Object::___construct() __construct()
     */
    public static $DefaultOptions = array(
    );
    
    /**
     * @var \BLW\ObjectInterface $base Pointer to main BLW Object.
     */
    public static $base = NULL;
    
    /**
     * @var \BLW\ObjectInterface $self Pointer to current context.
     */
    public static $self = NULL;
    
    /**
     * @var bool $Initialized Used to store class information status.
     */
    protected static $Initialized = false;
    
    /**
     * @var string $ID Id of the current object amongst it's siblings.
     * @see \BLW\Object::GetID() GetID()
     * @see \BLW\Object::SetID() SetID()
     */
    private $ID = '';
    
    /**
     * @var \BLW\ObjectInterface $Parent Pointer to current object Parent.
     */
    private $Parent = NULL;
    
    /**
     * @var int $Current Index of last child to use with hooks.
     */
    private $Current = 0;
    
    /**
     * @var stfing $OldID Previous ID of Object.
     */
    private $OldID = '';
    
    /**
     * @var int $Status Current status flag of the class.
     */
    protected $Status = 0;
    
    /**
     * @var \stdClass $Options Constructor Options
     */
    public $Options = NULL;
    
    /**
     * @var array $Hooks Predifined object Hooks.
     * @see \BLW\Object::on()
     */
    protected $Hooks = array(
    	'Create'        => NULL
        ,'SetID'        => NULL
        ,'Add'          => NULL
        ,'Update'       => NULL
        ,'Delete'       => NULL
        ,'Serialize'    => NULL
        ,'Unserialize'  => NULL
    );
    
    /**
     * Constructor
     * @param mixed $Options
     * @throws \BLW\BLW\InvalidArgumentException
     * <ul>
     * <li>If <code>$Options</code> is of an invalid type.</li>
     * <li>If <code>$Options</code> contains an invalid value.</li>
     * </ul>
     * @return void
     */
    final public function __construct($Options)
    {
        // Options
        if(!static::ValidateOptions($Options)) {
            throw new \BLW\InvalidArgumentException(0);
            return;
        }
        
        if($Options instanceof \BLW\ObjectInterface) {
            $this->Options = $Options->GetOptions();
        }
        
        else {
            $this->Options = static::BuildOptions($Options);
        }
        
        // Class ID
        if(isset($this->Options->ID)) {
            
            // Set ID
            $this->ID = static::SanitizeLabel($this->Options->ID);
            
            unset($this->Options->ID);
            
            // Validate
            if(empty($this->ID)) {
                // Invalid
                $this->Status &= self::INVALID_OPTION;
                
                throw new \BLW\InvalidArgumentException(0, '%header% Invalid ID $Options[`ID`].');
                return;
            }
        }
        
        else {
            $this->ID = static::Nounce();
        }
        
        // Parent
        $this->Parent = isset($this->Options->Parent)
            ? (is_object($this->Options->Parent)
                ? $this->Options->Parent
                : NULL
            )
            : NULL
        ;
        
        // OnCreate Hook
        $this->onCreate();
        
        // Change global blw_self to this
        Object::$self = &$this;
    }
    
    /**
     * Validates options passed to Object::create().
     * @see \BLW\Object::__construct()
     * @param mixed $Options Options to validate
     * @return bool Return <code>true</code> if options are valid <code>false</code> otherwise.
     */
    public static function ValidateOptions($Options)
    {
        return is_array($Options) || $Options instanceof \BLW\ObjectInterface;
    }
    
    /**
     * Builds Options for current object.
     * @see \BLW\Object::__construct()
     * @param mixed $Options Options to build
     * @return \stdClass Returns built options.
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
    public static function SanitizeLabel($Label)
    {
        $clean = trim(substr(@strval($Label), 0, 40));
        $clean = str_replace(array(' ', "\t", "\n", '-'), '_', $clean);
        
        return $clean;
    }
    
    /**
     * Validates that a label is valid.
     * @param string $Label String to validate
     * @return bool Return true if label is valid.
     */
    public static function ValidateLabel($Label)
    {
        return $Label === static::SanitizeLabel($Label) && !empty($Label);
    }
    
    /**
     * Creates a valid Object ID / Label / Name.
     * @note Raises warning if Input is not scaler.
     * @param string|int $Input ID can be biased to help regenerate ID's.
     * @return string Returns the new ID. Returns NULL on errors.
     */
    public static function Nounce($Input = NULL)
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
     * @param array $Data Optional initialization data.
     * @return array Returns the options generated. Used by child classes.
     */
    public static function init(array $Data = array())
    {
        // Initialize self
        if(get_called_class() == __CLASS__) {
            
            if(!self::$Initialized || isset($Data['hard_init'])) {
                
                self::$DefaultOptions   = array_replace(self::$DefaultOptions, $Data);
                self::$Initialized      = true;
                
                unset(self::$DefaultOptions['hard_init']);
                
                self::$base = self::create();
                self::$self = &self::$base;
            }
            
            // Return Options
            return self::$DefaultOptions;
        }
        
        else {
            // Initialize children
            return static::initChild($Data);
        }
    }
    
    /**
     * Initializes a child class for subsequent use.
     * @api BLW
     * @since 1.0.0
     * @param array $Options Initialization options. (Automatically adds blw_cfg())
     * @return array Returns Options used / generated during init.
     */
    public static function initChild(array $Data = array())
    {
        if(!static::$Initialized || isset($Data['hard_init'])) {
            // Call Parent init
            $ParentOptions = self::init();
        
            // Initialize self
            static::$DefaultOptions = array_replace($ParentOptions, static::$DefaultOptions, $Data);
            static::$Initialized    = true;
            
            unset(static::$DefaultOptions['hard_init']);
        }
        
        return static::$DefaultOptions;
    }
    
    /**
     * Creates a new instance of the object.
     * @param array $Options Options to use in initializing class.
     * @return \BLW\Object $thisInterface Returns a new instance of the class.
     */
    public static function create($Options = array())
    {
        return new static($Options);
    }
    
    /**
     * Generic hook handler function.
     * @note Format is <code>mixed function (\BLW\ObjectInterface $o)</code>.
     * @param unknown $Hook
     * @param \Closure $Function
     */
    public function on($Hook, \Closure $Function = NULL)
    {
        if(!is_string($Hook)) {
            throw new \BLW\InvalidArgumentException(0);
        }
        
        elseif(is_null($Funtion)) {
            if(is_callable($this->Hooks[$Hook])) {
                $this->Hooks[$Hook]($this);
            }
        }
        
        elseif(is_callable($Function)) {
            $this->Hooks[$Hook] = $Funtion;
        }
        
        else {
            $this->Status &= static::INVALID_CALLBACK;
            throw new \BLW\InvalidClassException();
        }
        
        return $this;
    }
    
    /**
     * Hook that is called when a new instance is created.
     * @note Format is <code>mixed function (\BLW\ObjectInterface $o)</code>.
     * @param \Closure $Function Function to call after object has been created.
     * @return \BLW\Object $this
     */
    public function onCreate(\Closure $Funtion = NULL)
    {
        if(is_null($Funtion)) {
            if(is_callable($this->Hooks['Create'])) {
                $this->Hooks['Create']($this);
            }
        }
        
        elseif(is_callable($Function)) {
            $this->Hooks['Create'] = $Funtion;
        }
        
        else {
            $this->Status &= static::INVALID_CALLBACK;
            throw new \BLW\InvalidClassException();
        }
        
        return $this;
    }
    
    /**
     * @ignore
     * @param int $Index
     */
    private static function InvalidIndex($Index)
    {
        $debug = debug_backtrace();
        
        trigger_error(sprintf('Undefined index: `%s` in %s on line %d.', @strval($Index), $debug[2]['file'], $debug[2]['line']));
    }
    
    /**
     * @ignore
     * @param mixed $Value
     */
    private static function InvalidValue($Value)
    {
        $debug = debug_backtrace();
        
        trigger_error(sprintf('Invalid value: ( %s ) in %s on line %d.', @print_r($Value), $debug[2]['file'], $debug[2]['line']));
    }
    
    /**
     * Fetches the current ID of the object.
     * @return string Returns the ID of the current class.
     */
    final public function GetID()
    {
        return $this->ID;
    }
    
    /**
     * Returns options used by class.
     * @internal Can be overloaded to add more options, etc
     * @return \stdClass Returns Options used by the object.
     */
    public function GetOptions()
    {
        return $this->Options;
    }
    
    /**
     * Changes the ID of the current object.
     * @param string $ID New ID to give Object
     * @return \BLW\Object $this
     */
    final public function SetID($ID)
    {
        if(!static::ValidateLabel($ID)) {
            throw new \BLW\InvalidArgumentException(0);
        }
        
        $this->OldID = $this->ID;
        $this->ID    = $ID;
        
        $this->onSetID();
    }
    
    /**
     * Hook that is called on change of ID.
     * @note Format is <code>mixed function (string $ID, string $OldID, \BLW\ObjectInterface $o)</code>.
     * @throws \BLW\InvalidArgumentException If <code>$Function</code> is not a valid callback.
     * @param \Closure $Function Function to call after ID has changed.
     * @return \BLW\Object $this
     */
    public function onSetID(\Closure $Function)
    {
        if(is_null($Funtion)) {
            if(is_callable($this->Hooks['SetID'])) {
                $this->Hooks['SetID']($this->ID, $this->OldID, $this);
            }
        }
        
        elseif(is_callable($Function)) {
            $this->Hooks['SetID'] = $Funtion;
        }
        
        else {
            $this->Status &= static::INVALID_CALLBACK;
            throw new \BLW\InvalidClassException();
        }
        
        return $this;
    }
    
    /**
     * Retrieves the current parent of the object.
     * @return \BLW\Object $thisInterface
     */
    final public function GetParent()
    {
        return $this->Parent;
    }
    
    /**
     * Sets parent of the current object if NULL.
     * @internal For internal use only.
     * @internal This is a one shot function (Only works once).
     * @param \BLW\ObjectInterface $o
     * @return \BLW\Object $this
     */
    final public function SetParent(\BLW\ObjectInterface &$Parent)
    {
        if(
            $Parent instanceof ObjectInterface
            && !$this->Parent instanceof \BLW\ObjectInterface
            || $this->Parent === Object::$base
        ) {
            $this->Parent = $Parent;
        }
        
        return $this;
    }
    
    /**
     * Clears parent of the current object.
     * @return \BLW\Object $this
     */
    final public function ClearParent()
    {
        $this->Parent = NULL;
        
        return $this;
    }
        
    /**
     * Get the current status flag of the object.
     * @return int Returns the current status flags of the object.
     */
    final public function Status()
    {
        return $this->Status;
    }
    
    /**
     * Clears the status flag of the current object.
     * @return \BLW\Object $this
     */
    final public function ClearStatus()
    {
        $this->Status = 0;
    }
    
    /**
     * Returns the child with current ID.
     * @note Changes the current context to the child.
     * @param string $ID Object ID of child to return.
     * @return \BLW\Object Returns <code>NULL</code> if parent does not exits.
     */
    final public function& child($ID)
    {
        foreach ($this as $k => $o) {
            
            if($o instanceof \BLW\ObjectInterface) {
                if($o->GetID() == $ID) {
                    return Object::$self = parent::offsetGet($k); 
                }
            }
        }
        
        self::InvalidIndex($ID);
        
        return Object::$self = NULL;
    }
    
    /**
     * Returns the parent of the current object.
     * @note Changes the current context to the parent.
     * @return \BLW\Object Returns <code>NULL</code> if parent does not exits.
     */
    final public function& parent()
    {
        return Object::$self = $this->Parent;
    }
    
    /**
     * Calls an anonymous function on each child of the function.
     * @note Function format: <code>mixed funtion ($o, $Index)</code>
     * @param \Closure $Function Function to call.
     * @return \BLW\Object $this
     */
    final public function each(\Closure $Function)
    {
        $return = array();
        
        if(is_callable($Function)) {
            
            foreach ($this as $i => $o) {
                $return[] = $Function($o, $i);
            }
        }
        
        else {
            throw new \BLW\InvalidArgumentException(0);
        }
        
        return $return;
    }
    
    /**
     * Call an anonymous function on object and all its descendants.
     * @note Parents are called before children.
     * @note Function format: <code>mixed funtion ($o, $Index)</code>
     * @param \Closure $Function Funtion to call.
     * @return array Array of return values for each iteration of function.
     */
    final public function walk(\Closure $Function)
    {
        $return = array();
        
        if(is_callable($Function)) {
            
            foreach ($this as $i => $o) {
                $return[] = $Function($o, $i);
                
                if($o instanceof \BLW\ObjectInterface) {
                    $return[$o->GetID()] = $o->walk($Function);
                }
            }
        }
    
        return $return;
    }
    
    /**
     * Hook that is called when a child is added.
     * @note Format is <code>mixed function (\BLW\ObjectInterface $o, int $Index)</code>.
     * @param \Closure $Function Function to call after ID has changed.
     * @return \BLW\Object $this
     */
    public function onAdd(\Closure $Function = NULL)
    {
        if(is_null($Funtion)) {
            // Set Parent
            $this[$this->Current]->SetParent($this);
            
            if(is_callable($this->Hooks['Add'])) {
                $this->Hooks['Add']($this, $this->Current);
            }
        }
        
        elseif(is_callable($Function)) {
            $this->Hooks['Add'] = $Funtion;
        }
        
        else {
            $this->Status &= static::INVALID_CALLBACK;
            throw new \BLW\InvalidClassException();
        }
        
        return $this;
    }
        
    /**
     * Hook that is called when a child is changed.
     * @note Format is <code>mixed function (\BLW\ObjectInterface $o, int $Index)</code>.
     * @param \Closure $Function Function to call after Child has changed.
     * @return \BLW\Object $this
     */
    public function onUpdate(\Closure $Function = NULL)
    {
        if(is_null($Funtion)) {
            // Set Parent
            $this[$this->Current]->SetParent($this);
            
            if(is_callable($this->Hooks['Update'])) {
                $this->Hooks['Update']($this, $this->Current);
            }
        }
        
        elseif(is_callable($Function)) {
            $this->Hooks['Update'] = $Funtion;
        }
        
        else {
            $this->Status &= static::INVALID_CALLBACK;
            throw new \BLW\InvalidClassException();
        }
        
        return $this;
    }
    
    /**
     * Hook that is called when a child is deleted.
     * @note Format is <code>mixed function (\BLW\ObjectInterface $o, int $Index)</code>.
     * @param \Closure $Function Function to call before Object is deleted.
     * @return \BLW\Object $this
     */
    public function onDelete(\Closure $Function = NULL)
    {
        if(is_null($Funtion)) {
            if(is_callable($this->Hooks['Delete'])) {
                $this->Hooks['Delete']($this, $this->Current);
            }
        }
        
        elseif(is_callable($Function)) {
            $this->Hooks['Delete'] = $Funtion;
        }
        
        else {
            $this->Status &= static::INVALID_CALLBACK;
            throw new \BLW\InvalidClassException();
        }
        
        return $this;
    }
    
    /**
     * Hook that is called just before an object is serialized.
     * @note Format is <code>mixed function (\BLW\ObjectInterface $o)</code>.
     * @param \Closure $Function Function to call before object is serialized.
     * @return \BLW\Object $this
     */
    public function onSerialize(\Closure $Function = NULL)
    {
        if(is_null($Funtion)) {
            if(is_callable($this->Hooks['Serialize'])) {
                $this->Hooks['Serialize']($this);
            }
        }
        
        elseif(is_callable($Function)) {
            $this->Hooks['Serialize'] = $Funtion;
        }
        
        else {
            $this->Status &= static::INVALID_CALLBACK;
            throw new \BLW\InvalidClassException();
        }
        
        return $this;
    }
    
    /**
     * Hook that is called just after an object is unserialized.
     * @note Format is <code>mixed function (\BLW\ObjectInterface $o)</code>.
     * @param \Closure $Function Function to call after Object has been unserialized.
     * @return \BLW\Object $this
     */
    public function onUnSerialize(\Closure $Function = NULL)
    {
        if(is_null($Funtion)) {
            if(is_callable($this->Hooks['UnSerialize'])) {
                $this->Hooks['UnSerialize']($this);
            }
        }
        
        elseif(is_callable($Function)) {
            $this->Hooks['UnSerialize'] = $Funtion;
        }
        
        else {
            $this->Status &= static::INVALID_CALLBACK;
            throw new \BLW\InvalidClassException();
        }
        
        return $this;
    }
    
    /**
     * Loads and object data from an <code>obj.xxx.min.php</code> file.
     * @api BLW
     * @since 0.1.0
     * @param string $Data Custom Data to reinstate class. (Used for info that cannot be serialized)
     * @return \BLW\Object $this
     */
    public function Load(array $Data = array())
    {
        ;;;;;
        
        return $this;
    }
    
    /**
     * Saves an element to an obj.xxx.min.php file
     * @api BLW
     * @since 0.1.0
     * @throws \BLW\InvalidArgumentException If <code>$File</code> is not a string.
     * @throws \BLW\FileError If unable to create / write to file.
     * @param string $File File to save the object to.
     * @return \BLW\Object $this
     */
    final public function Save($File = NULL, array $Data = array())
    {
        if(!is_string($File)) {
            throw new \BLW\InvalidArgumentException(0);
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
            .'\\'.get_class($this).'::init();'
            .'return unserialize(%s)->Load(%s);'
            ,__NAMESPACE__
            ,var_export(serialize($this), true)
            ,var_export($Data, true)
        );
        
        if(@!file_put_contents($File, $Contents)) {
            throw new \BLW\FileException($File, 'Unable to save object.');
            return $this;
        }
        
        return $this;
    }
    
    /**
     * @ignore
     */
    final public function offsetSet($index, $newval)
    {
        if($newval instanceof \BLW\ObjectInterface) {
            
            parent::offsetSet($index, $newval);
            $this->Current = $index;
            $this->onUpdate();
        }
        
        else {
            self::InvalidValue($Value);
        }
    }
    
    /**
     * @ignore
     */
    final public function offsetUnset($index)
    {
        if($newval instanceof \BLW\ObjectInterface) {
            
            if(parent::offsetExists($index)) {    
                $this->Current = $index;
                $this->onDelete();
            }
            
            parent::offsetUnset($index);
        }
        
        else {
            self::InvalidValue($Value);
        }
    }
    
    /**
     * @ignore
     */
    final public function pop()
    {
        $this->Current = $this->count() - 1;
        $this->onDelete();
        return parent::pop();
    }
    
    /**
     * @ignore
     */
    final public function shift()
    {
        $this->Current = 0;
        $this->onDelete();
        return parent::shift();
    }
    
    /**
     * @ignore
     */
    final public function push($newval)
    {
        if($newval instanceof \BLW\ObjectInterface) {
            parent::push($newval);
            $this->Current = count($this) - 1;
            $this->onAdd();
        }
        
        else {
            self::InvalidValue($Value);
        }
        
        return $this;
    }
    
    /**
     * @ignore
     */
    public function seek($Position)
    {
        if(is_int($Position)) {
            if($Position >= 0 && $Position < $this->count()) {
                parent::rewind();
                
                for($i=0;$i!=$Position;$i++) {
                    parent::next();
                }
                
                return;
            }
            
            throw new \OutOfBoundsException(sprintf('Invalid seek position ( %d ).', $Position));
            return;
        }
        
        throw new \BLW\InvalidArgumentException(0);
        return;
    }
    
    /**
     * @ignore
     */ 
    final public function __call($Key, $Params)
    {
        if(!isset($this->{$Key})) {
            throw new \BadMethodCallException(sprintf('Call to undefined method `%s::%s()`.', get_class($this), $Key));
            return;
        }
        
        elseif(!is_callable($this->{$Key})) {
            throw new \BadMethodCallException(sprintf('%s::%s: Contains invalid method ( %s ).', get_class($this), $Key, print_r($this->{$Key}, true)));
            return;
        }
        
        return call_user_func_array($this->{$Key}, $Params);
    }
    
    /**
     * @ignore
     */ 
    public function __clone()
    {
        $this->ClearParent();
    }
    
    /**
     * @ignore
     */ 
    final public function serialize($Parent = NULL)
    {
        if($Parent instanceof static) {
            parent::rewind();
            
            $this->ID      = $Parent->GetID();
            $this->Options = $Parent->GetOptions();
            
            $this->onSerialize();
            parent::push(get_object_vars($this));
            
            return parent::serialize();
        }
        
        $New = clone $this;
        
        return $New->serialize($this);
    }
    
    /**
     * @ignore
     */ 
    final public function unserialize($serialized)
    {
        parent::unserialize($serialized);
        
        foreach (parent::pop() as $k => $v) {
            $this->{$k} = $v;
        }
        
        foreach ($this as $o) {
            $o->SetParent($this);
        }
        
        $this->onUnSerialize();
    }
}

return ;