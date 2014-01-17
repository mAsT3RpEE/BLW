<?php
/**
 * Iterator.php | Dec 26, 2013
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

use BLW;

/**
 * Core Iterator pattern class.
 *
 * <h4>Notice:</h4>
 *
 * <p>All Iterators must either implement this class or
 * implement the <code>\BLW\Interfaces\Iterator</code> interface.
 *
 * <hr>
 * @package BLW\Core
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 */
abstract class Iterator extends \BLW\Type\Object implements \BLW\Interfaces\Iterator
{
    /**
     * Generates invalid index notice.
     * @ignore
     * @param int $Index
     * @return void
     */
    private static function InvalidIndex($Index)
    {
        $debug = debug_backtrace();
        trigger_error(sprintf('Undefined index: ( %s ) in %s on line %d.', @strval($Index), $debug[2]['file'], $debug[2]['line']), E_USER_NOTICE);
    }

    /**
     * Generates invalid value warning.
     * @ignore
     * @param mixed $Value
     * @return void
     */
    private static function InvalidValue($Value)
    {
        $debug = debug_backtrace();
        trigger_error(sprintf('Invalid value: ( %s ) in %s on line %d.', @print_r($Value, true), $debug[2]['file'], $debug[2]['line']), E_USER_WARNING);
    }

    /**
     * Returns the child with current ID.
     * @note Changes the current context to the child.
     * @param string $ID Object ID of child to return.
     * @return \BLW\Interfaces\Object Returns <code>NULL</code> if child does not exits.
     */
    final public function& child($ID)
    {
        foreach ($this as $k => $o) if($this->ValidateValue($o)) {
            if($o->GetID() == $ID) {
                return BLW::$Self = \SplDoublyLinkedList::offsetGet($k);
            }
        }

        return BLW::$Self = NULL;
    }

    /**
     * Calls an anonymous function on each child of the function.
     * @note Function format: <code>mixed function (\BLW\Model\Event\ObjectItem $Object)</code>
     * @param callable $Function Function to call.
     * @return \BLW\Interfaces\Object $this
     */
    final public function each($Function)
    {
        $return = array();

        if(is_callable($Function)) {

            foreach ($this as $i => $o) {
                $return[$i] = $Function(new \BLW\Model\Event\ObjectItem($o, $i));
            }
        }

        else {
            throw new \BLW\Model\InvalidArgumentException(0);
        }

        return $return;
    }

    /**
     * Call an anonymous function on object and all its descendants.
     * @note Function format: <code>mixed function (\BLW\Model\Event\ObjectItem $Object)</code>
     * @param callable $Function Funtion to call.
     * @return \BLW\Interfaces\Object $this
     */
    final public function walk($Function)
    {
        static $isChild = false;

        $return = array();

        if(!$isChild) {
            $return[-1] = $Function(new \BLW\Model\Event\ObjectItem($this, -1));
            $isChild    = true;
        }

        if(is_callable($Function)) {

            foreach ($this as $i => $o) {
                $return[$i] = $Function(new \BLW\Model\Event\ObjectItem($o, $i));

                if($o instanceof \BLW\Interfaces\Iterator) {
                    $return[$o->GetID()] = $o->walk($Function);
                }
            }
        }

        $child = false;

        return $return;
    }

    /**
     * Hook that is called when a child is added.
     * @return \BLW\Interfaces\Object $this
     */
    public function doAdd()
    {
        // Add parent
        $Object = \SplDoublyLinkedList::offsetGet($this->_Current)
            ->SetParent($this)
        ;

        // Trigger event
        $this->_do('Add', new \BLW\Model\Event\ObjectItem($Object, $this->_Current));

        return $this;

    }

    /**
     * Hook that is called when a child is added.
     * @note Format is <code>mixed function (\BLW\Interfaces\Event $Event)</code>.
     * @param callable $Function Function to call after ID has changed.
     * @return \BLW\Interfaces\Object $this
     */
    public function onAdd($Function)
    {
        if(is_callable($Function)) {
            $this->_on('Add', $Function);
        }

        else {
            $this->_Status &= static::INVALID_CALLBACK;
            throw new \BLW\Model\InvalidClassException($this->_Status);
        }

        return $this;
    }

    /**
     * Hook that is called when a child is changed.
     * @return \BLW\Interfaces\Object $this
     */
    public function doUpdate()
    {
        // Add parent
        $Object = \SplDoublyLinkedList::offsetGet($this->_Current)
            ->SetParent($this)
        ;

        // Trigger event
        $this->_do('Update', new \BLW\Model\Event\ObjectItem($Object, $this->_Current));

        return $this;
    }

    /**
     * Hook that is called when a child is changed.
     * @note Format is <code>mixed function (\BLW\Interfaces\Event $Event)</code>.
     * @param callable $Function Function to call after ID has changed.
     * @return \BLW\Interfaces\Object $this
     */
    public function onUpdate($Function)
    {
        if(is_callable($Function)) {
            $this->_on('Update', $Function);
        }

        else {
            $this->_Status &= static::INVALID_CALLBACK;
            throw new \BLW\Model\InvalidClassException($this->_Status);
        }

        return $this;
    }

    /**
     * Hook that is called when a child is deleted.
     * @return \BLW\Interfaces\Object $this
     */
    public function doDelete()
    {
        // Trigger event
        $this->_do('Delete', new \BLW\Model\Event\ObjectItem(\SplDoublyLinkedList::offsetGet($this->_Current), $this->_Current));

        return $this;
    }

    /**
     * Hook that is called when a child is deleted.
     * @note Format is <code>mixed function (\BLW\Interfaces\Event $Event)</code>.
     * @param callable $Function Function to call after ID has changed.
     * @return \BLW\Interfaces\Object $this
     */
    public function onDelete($Function)
    {
        if(is_callable($Function)) {
            $this->_on('Delete', $Function);
        }

        else {
            $this->_Status &= static::INVALID_CALLBACK;
            throw new \BLW\Model\InvalidClassException($this->_Status);
        }

        return $this;
    }

    /**
     * Determines if value is a valid value for the iterator.
     * @param mixed $value Value to test.
     * @return bool Returns <code>TRUE</code> if valid <code>FALSE</code> otherwise.
     */
    public function ValidateValue($value) {
        return $value instanceof \BLW\Interfaces\Object
            || $value instanceof \BLW\Interfaces\Adaptor
            || $value instanceof \BLW\Interfaces\ActiveRecord
        ;
    }

    /**
     * Push a node at the end of the list.
     * @see \SplDoublyLinkedList::push()
     * @param mixed $value Value to push.
     * @return \BLW\Interfaces\Object $this
     */
    final public function push($value)
    {
        if($this->ValidateValue($value)) {
            \SplDoublyLinkedList::push($value);
            $this->_Current = $this->count() - 1;
            $this->doAdd();
        }

        else {
            self::InvalidValue($value);
        }

        return $this;
    }

    /**
     * Pops a node from the end of the list
     * @see \SplDoublyLinkedList::pop()
     * @return mixed Popped Object.
     */
    final public function pop()
    {
        $this->_Current = $this->count() - 1;
        $this->doDelete();
        return \SplDoublyLinkedList::pop();
    }

    /**
     * Shifts a node from the end of the list
     * @see \SplDoublyLinkedList::shift()
     * @return mixed Shifted Object.
     */
    final public function shift()
    {
        $this->_Current = 0;
        $this->doDelete();
        return \SplDoublyLinkedList::shift();
    }

    /**
     * Prepend a node at the beginning of the list
     * @see \SplDoublyLinkedList::unshift()
     * @param mixed $value Value to push.
     * @return \BLW\Interfaces\Object $this
     */
    final public function unshift($value)
    {
        if($this->ValidateValue($value)) {
            \SplDoublyLinkedList::unshift($value);
            $this->_Current = 0;
            $this->doAdd();
        }

        else {
            self::InvalidValue($value);
        }

        return $this;
    }

    /**
     * ArrayAccess Interface
     * @see \SplDoublyLinkedList::offsetSet()
     * @param int $index Index being set
     * @param \BLW\Interface\Object $newval Object to replace current value.
     * @return void
     */
    final public function offsetSet($index, $newval)
    {
        if($this->ValidateValue($newval)) {
            \SplDoublyLinkedList::offsetSet($index, $newval);

            if (is_null($index)) {
                $this->_Current = $this->count() - 1;
                $this->doAdd();
            }

            else {
                $this->Current = $index;
                $this->doUpdate();
            }
        }

        else {
            self::InvalidValue($Value);
        }
    }

    /**
     * ArrayAccess Interface
     * @see \SplDoublyLinkedList::offsetUnset()
     * @param int $index Index being set
     * @param \BLW\Interface\Object $newval Object to replace current value.
     * @return void
     */
    final public function offsetUnset($index)
    {
        if(\SplDoublyLinkedList::offsetExists($index)) {
            $this->Current = $index;
            $this->doDelete();
        }

        parent::offsetUnset($index);
    }

    /**
     * Seekable Interface
     * @see http://php.net/manual/class.seekableiterator.php Language Reference
     * @param int $position The position to seek to.
     * @return void
     */
    public function seek($position)
    {
        if(is_int($position)) {

            if($position >= 0 && $position < \SplDoublyLinkedList::count()) {
                \SplDoublyLinkedList::rewind();

                for($i=0;$i!=$position;$i++) {
                    \SplDoublyLinkedList::next();
                }
            }

            else {
                throw new \OutOfBoundsException(sprintf('Invalid seek position ( %d ).', $position));
            }
        }

        else {
            throw new \BLW\Model\InvalidArgumentException(0);
        }
    }
}