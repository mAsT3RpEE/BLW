<?php
/**
 * Singleton.php | Dec 28, 2013
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
namespace BLW\Type;

if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

/**
 * Core Sigleton pattern class.
 * @package BLW\Core
 * @api BLW
 * @since 0.1.0
 * @version 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ mAsT3RpEE's Zone > Projects > BLW
 */
abstract class Singleton extends \BLW\Type\Object implements \BLW\Interfaces\Singleton
{
    /**
     * Array containig class instances.
     * @var \BLW\Interfaces\Singleton $_Instance
     */
    private static $_Instance = array();

    /**
     * Creates a new instance of the object.
     * @api BLW
     * @since 1.0.0
     * @param array $Options Options to use in initializing class.
     * @return \BLW\Interfaces\Singleton Returns a new instance of the class.
     */
    final public static function GetInstance($Options = array())
    {
        $Static = get_called_class();

        if (!isset(self::$_Instance[$Static])) {
            self::$_Instance[$Static] = new static($Options);
        }

        return self::$_Instance[$Static];
    }

    /**
     * Changes the current singleton instance.
     * @param \BLW\Interfaces\Object $NewObject Object to set singleton value to.
     * @return bool Returns <code>TRUE</code> on success and <code>FALSE</code> on failure.
     */
    final public static function SetInstance(\BLW\Interfaces\Singleton $NewObject)
    {
        $Static = get_called_class();
        self::$_Instance[$Static] = $NewObject;
        self::$_Instance[$Static]->doUpdate();
        return true;
    }

    /**
     * Clears the current signleton instance.
     * @return void
     */
    final public static function ClearInstance()
    {
        $Static = get_called_class();

        if (isset(self::$_Instance[$Static])) {
            if (self::$_Instance[$Static] instanceof static) {
                self::$_Instance[$Static]->doDelete();
            }
        }

        self::$_Instance[$Static] = NULL;
    }

    /**
     * Hook that is called when a new instance is created.
     * @see \BLW\Type\Object::doCreate()
     * @return \BLW\Interfaces\Object $this
     */
    public static function doCreate()
    {
        if (isset(self::$_Instance[get_called_class()])) {
            throw new \BLW\Model\InvalidClassException(0, '%header% Only one instance of a singleton may be created');
        }

        return parent::doCreate();
    }

    /**
     * Hook that is called when instance is changed.
     * @see \BLW\Type\Singleton::onUpdate()
     * @return \BLW\Interfaces\Object $this
     */
    public function doUpdate()
    {
        $this->_do('Update', new \BLW\Model\Event\ObjectItem($this, -1));

        return $this;
    }

    /**
     * Hook that is called when instance is changed.
     * @note Format is <code>mixed function (\BLW\Interfaces\Event $Event)</code>.
     * @see \BLW\Type\Singleton::doUpdate()
     * @param callable $Function Function to call on change.
     * @return \BLW\Interfaces\Object $this
     */
    public function onUpdate($Function)
    {
        $this->_on('Update', $Function);

        return $this;
    }

    /**
     * Hook that is called when instance is deleted.
     * @see \BLW\Type\Singleton::onUpdate()
     * @return \BLW\Interfaces\Object $this
     */
    public function doDelete()
    {
        // Trigger event
        $this->_do('Delete', new \BLW\Model\Event\ObjectItem($this, -1));

        return $this;
    }

    /**
     * Hook that is called when instance is deleted.
     * @note Format is <code>mixed function (\BLW\Interfaces\Event $Event)</code>.
     * @see \BLW\Type\Singleton::doUpdate()
     * @param callable $Function Function to call on change.
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
}