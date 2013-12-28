<?php
/**
 * Event.php | Dec 27, 2013
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
 * Core Event class.
 *
 * <h4>Notice:</h4>
 *
 * <p>All Events must either extend this class or
 * impliment the <code>\BLW\Interfaces\Event</code> interface.
 *
 * <hr>
 * @package BLW\Core
 * @api BLW
 * @since 0.1.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 */
abstract class Event extends \Symfony\Component\EventDispatcher\Event implements \BLW\Interfaces\Event
{
    /**
     * @var mixed $Subject Subject of the current event.
     */
    private $_Subject = NULL;

    /**
     * @var mixed $Subject Subject of the current event.
     */
    private $_Properties = NULL;

    /**
     * Constructor.
     * @param mixed $Subject The subject of the event, usually an object.
     * @param array $Properties Properties to generate in the event.
     * @return void
     */
    public function __construct($Subject, $Properties = array())
    {
        $this->_Subject = $Subject;

        if(is_array($Properties) || $Properties instanceof \Traversable) {
            $this->_Properties = new \ArrayIterator($Properties);
        }

        else {
            // Invalid Properties
            throw new \BLW\Model\InvalidArgumentException(1);
        }
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
     * Property functions methods.
     * @param string $name Property interacted with.
     * @return mixed Overloaded method return value.
     */
    final public function __call($name, array $arguments)
    {
        if(is_callable(@$this->_Properties->{$name})) {
            return call_user_func_array($this->_Properties->{$name}, $arguments);
        }

        elseif(is_callable(array($this->_Properties, $name))) {
            return call_user_func_array(array($this->_Properties, $name), $arguments);
        }

        else {
            throw new \BadMethodCallException(sprintf('Call to undefined method `%s::%s()`.', get_class($this), $name));
            return NULL;
        }
    }

    /**
     * Dynamic properties.
     * @param string $name Property interacted with.
     * @return mixed Overloaded propery
     */
    public function __get($name)
    {
        return $this->_Properties->{$name};
    }

    /**
     * Dynamic properties.
     * @param string $name Property interacted with.
     * @param mixed $value Value to set property to.
     * @return void
     */
    public function __set($name, $value)
    {
        $this->_Properties->{$name} = $value;
    }

    /**
     * Dynamic properties.
     * @param string $name Property interacted with.
     * @return bool Returns true if the dyanamic property exists.
     */
    public function __isset($name)
    {
        return isset($this->_Properties->{$name});
    }

    /**
     * Dynamic properties.
     * @param string $name Property interacted with.
     * @return mixed Overloaded propery
     */
    public function __unset($name)
    {
        unset($this->_Properties->{$name});
    }

    /**
     * ArrayAccesss Interface.
     * @param mixed $offset Offset to test.
     * @return bool Returns <code>true</code> if offset exists and <code>false</code> if not.
     */
    final public function offsetExists ($offset)
    {
        return $this->_Properties->offsetExists($name);
    }

    /**
     * ArrayAccesss Interface.
     * @param mixed $offset Offset to get.
     * @return mixed Returns <code>NULL</code> if not found
     */
    final public function offsetGet ($offset)
    {
        return $this->_Properties->offsetGet($name);
    }

    /**
     * ArrayAccesss Interface.
     * @param mixed $offset Offset to set.
     * @param mixed $value New value of offset.
     * @return void
     */
    final public function offsetSet ($offset, $value)
    {
        return $this->_Properties->offsetSet($name, $value);
    }

    /**
     * ArrayAccesss Interface.
     * @param mixed $offset Offset to unset.
     * @return void
     */
    final public function offsetUnset ($offset)
    {
        return $this->_Properties->offsetUnset($name, $value);
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

    /**
     * Iterator interface.
     * @return mixed Current value pointed to.
     */
    public function current ()
    {
        return $this->_Properties->current();
    }

    /**
     * Iterator interface.
     * @return mixed Returns <code>scalar</code> on success and <code>NULL</code> on failure.
     */
    public function key()
    {
        return $this->_Properties->key();
    }

    /**
     * Iterator interface.
     * @return void
     */
    public function next()
    {
        return $this->_Properties->next();
    }

    /**
     * Iterator interface.
     * @return void
     */
    public function rewind()
    {
        return $this->_Properties->rewind();
    }

    /**
     * Iterator interface.
     * @return void
     */
    public function valid()
    {
        return $this->_Properties->valid();
    }

    /**
     * Countable interface.
     * @return int Number of items.
     */
    public function count()
    {
       return $this->_Properties->count();
    }
}

return true;