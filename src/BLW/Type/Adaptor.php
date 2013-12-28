<?php
/**
 * Adaptor.php | Dec 27, 2013
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
 * Core Adapter pattern class.
 *
 * <h4>Notice:</h4>
 *
 * <p>All Adaptor objects must either extend this class or
 * implement the <code>\BLW\Interfaces\Adaptor</code> interface.
 *
 * <hr>
 * @package BLW\Core
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 */
abstract class Adaptor implements \BLW\Interfaces\Adaptor
{
    /**
     * @var string TARGET_CLASS Used by GetInstance to generate instance of class
     */
    protected static $_Class = '\\BLW\\Model\\Object';

    /**
     * @var mixed $Subject Subject of the current event.
     */
    private $_Subject = NULL;

    /**
     * Constructor.
     * @throws \BLW\Model\InvalidArgumentException If subject is not an instance of <code>$_Class</code>.
     * @param mixed $Subject The subject of the adaptor.
     * @return void
     */
    public function __construct($Subject)
    {
        if($Subject instanceof static::$_Class) {
            $this->_Subject = $Subject;
        }

        else {
            throw new \BLW\Model\InvalidArgumentException(1);
        }
    }

    /**
     * Creates an instance of the adaptor object.
     * @note Default creates
     * @param ...
     * @return \BLW\Interface\Adaptor
     */
    final public static function GetInstance(/* ... */)
    {
        $Generator = new \ReflectionClass(static::$_Class);
        $Subject   = $Generator->newInstanceArgs(func_get_args());
        return new static($Subject);
    }

    /**
     * Getter for subject property.
     * @return mixed $subject The adaptor subject.
     */
    final public function GetSubject()
    {
        return $this->_Subject;
    }

    /**
     * Import child methods.
     * @throws \BadMethodCallException If call cannot be handled by subject.
     * @param string $name Property interacted with.
     * @return mixed Overloaded method return value or <code>NULL</code> on error.
     */
    final public function __call($name, array $arguments)
    {
        if(is_callable(array($this->_Subject, $name))) {
            return call_user_func_array(array($this->_Subject, $name), $arguments);
        }

        else {
            throw new \BadMethodCallException(sprintf('Call to undefined method `%s::%s()`.', get_class($this), $name));
            return NULL;
        }
    }

    /**
     * Import child properties.
     * @param string $name Property interacted with.
     * @return mixed Overloaded propery or NULL in case of error.
     */
    final public function __get($name)
    {
        if(isset($this->_Subject->{$name})) {
            return $this->_Subject->{$name};
        }

        else {
            trigger_error(sprintf('Undefined property: %s::%s', get_class($this), $name), E_USER_NOTICE);
            return NULL;
        }
    }

    /**
     * Import child properties.
     * @param string $name Property interacted with.
     * @param mixed $value Value to set property to.
     * @return void
     */
    final public function __set($name, $value)
    {
        try {
            @$this->_Subject->{$name} = $value;
        }

        catch (\Exception $e) {
            trigger_error(sprintf('Undefined property: %s::%s', get_class($this), $name), E_USER_NOTICE);
        }
    }

    /**
     * Import child properties.
     * @param string $name Property interacted with.
     * @return bool Returns true if the dyanamic property exists.
     */
    final public function __isset($name)
    {
        return @isset($this->_Subject->{$name});
    }

    /**
     * Dynamic properties.
     * @param string $name Property interacted with.
     * @return mixed Overloaded propery
     */
    final public function __unset($name)
    {
        unset($this->_Subject->{$name});
    }

    /**
     * ArrayAccesss Interface.
     * @param mixed $offset Offset to test.
     * @return bool Returns <code>true</code> if offset exists and <code>false</code> if not.
     */
    final public function offsetExists ($offset)
    {
        try {
            return $this->_Subject->offsetExists($offset);
        }

        catch(\Exception $e) {
            trigger_error(sprintf('%s::offsetExists() doesnt exist.', get_class($this->_Subject)));
            return false;
        }
    }

    /**
     * ArrayAccesss Interface.
     * @param mixed $offset Offset to get.
     * @return mixed Returns <code>NULL</code> if not found
     */
    final public function offsetGet ($offset)
    {
        try {
            return $this->_Subject->offsetGet($offset);
        }

        catch(\Exception $e) {
            trigger_error(sprintf('%s::offsetGet() doesnt exist.', get_class($this->_Subject)));
            return NULL;
        }
    }

    /**
     * ArrayAccesss Interface.
     * @param int|string $offset Offset to test.
     * @param mixed $value Value to set to current offset.
     * @return void
     */
    final public function offsetSet ($offset, $value)
    {
        try {
            return $this->_Subject->offsetSet($offset, $value);
        }

        catch(\Exception $e) {
            trigger_error(sprintf('%s::offsetSet() doesnt exist.', get_class($this->_Subject)));
            return NULL;
        }
    }

    /**
     * ArrayAccesss Interface.
     * @param mixed $offset Offset to unset.
     * @return void
     */
    final public function offsetUnset ($offset)
    {
        try {
            return $this->_Subject->offsetUnset($offset);
        }

        catch(\Exception $e) {
            trigger_error(sprintf('%s::offsetUnset() doesnt exist.', get_class($this->_Subject)));
            return NULL;
        }
    }

    /**
     * All objects must have a string representation.
     * @note Default is the serialized form of the object.
     * @return string String value of object.
     */
    public function __toString()
    {
        return $this->serialize();
    }

    /**
     * Serializable Interface.
     * @return string Serialized data
     */
    public function serialize()
    {
        return serialize(get_object_vars($this));
    }

    /**
     * Serializable Interface.
     * @param string $serialized Serialized object.
     * @return void
     */
    public function unserialize($serialized)
    {
        foreach(unserialize($serialized) as $k => $v) {
            $this->{$k} = $v;
        }
    }
}

return true;