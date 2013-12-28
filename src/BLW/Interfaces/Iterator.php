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
namespace BLW\Interfaces; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

/**
 * Core Iterator pattern interface.
 *
 * <h4>Notice:</h4>
 *
 * <p>All Iterators must either implement this interface or
 * extend the <code>\BLW\Type\Iterator</code> class.
 *
 * <hr>
 * @package BLW\Core
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 */
interface Iterator extends \BLW\Interfaces\Object, \ArrayAccess, \SeekableIterator, \Countable
{
    /**
     * Returns the child with current ID.
     * @note Changes the current context to the child.
     * @param string $ID Object ID of child to return.
     * @return \BLW\Interfaces\Object Returns <code>NULL</code> if child does not exits.
     */
    public function& child($ID);

    /**
     * Calls an anonymous function on each child of the function.
     * @note Function format: <code>mixed function (\BLW\Model\Event\ObjectItem $Object)</code>
     * @param callable $Function Function to call.
     * @return \BLW\Interfaces\Object $this
     */
    public function each($Function);

    /**
     * Call an anonymous function on object and all its descendants.
     * @note Function format: <code>mixed function (\BLW\Model\Event\ObjectItem $Object)</code>
     * @param callable $Function Funtion to call.
     * @return \BLW\Interfaces\Object $this
     */
    public function walk($Function);

    /**
     * Hook that is called when a child is added.
     * @return \BLW\Interfaces\Object $this
     */
    public function doAdd();

    /**
     * Hook that is called when a child is added.
     * @note Format is <code>mixed function (\BLW\Model\Event\ObjectItem $Event)</code>.
     * @param callable $Function Function to call after ID has changed.
     * @return \BLW\Interfaces\Object $this
     */
    public function onAdd($Function);

    /**
     * Hook that is called when a child is changed.
     * @return \BLW\Interfaces\Object $this
     */
    public function doUpdate();

    /**
     * Hook that is called when a child is changed.
     * @note Format is <code>mixed function (\BLW\Model\Event\ObjectItem $Event)</code>.
     * @param callable $Function Function to call after ID has changed.
     * @return \BLW\Interfaces\Object $this
     */
    public function onUpdate($Function);

    /**
     * Hook that is called when a child is deleted.
     * @return \BLW\Interfaces\Object $this
     */
    public function doDelete();

    /**
     * Hook that is called when a child is deleted.
     * @note Format is <code>mixed function (\BLW\Model\Event\ObjectItem $Event)</code>.
     * @param callable $Function Function to call after ID has changed.
     * @return \BLW\Interfaces\Object $this
     */
    public function onDelete($Function);

    /**
     * Push a node at the end of the list.
     * @param mixed $value Value to push.
     * @return \BLW\Interfaces\Object $this
     */
    public function push($value);

    /**
     * Pops a node from the end of the list
     * @return mixed Popped Object.
     */
    public function pop();

    /**
     * Shifts a node from the end of the list
     * @return mixed Shifted Object.
     */
    public function shift();

    /**
     * Prepend a node at the beginning of the list
     * @param mixed $value Value to push.
     * @return \BLW\Interfaces\Object $this
     */
    public function unshift($value);
}