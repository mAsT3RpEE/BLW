<?php
/**
 * Symfony.php | Jan 05, 2014
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
namespace BLW\Type\ApplicationCommand; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

use BLW;

/**
 * Default BLW object.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 */
abstract class Symfony extends \Symfony\Component\Console\Command\Command implements \BLW\Interfaces\ApplicationCommand
{
    /**
     * @var \BLW\Interfaces\Object $Parent Pointer to current object Parent.
     */
    private $_Parent = NULL;

    /**
     * @var \stdClass $Options Constructor Options
     */
    public $Options = NULL;

    /**
     * Constructor
     * @param array $Options Constructor options
     * @throws \BLW\Model\InvalidArgumentException if <code>$Options</code> is not an array or Traversable.
     * @return void
     */
    final public function __construct($Options = array())
    {
        if (is_array($Options)) {
            $this->Options = (object)($Options);
        }

        elseif ($Options instanceof \Traversable) {
            $this->Options = (object)(iterator_to_array($Options));
        }

        else {
            throw new \BLW\Model\InvalidArgumentException(0);
        }

        parent::__construct();
    }

    /**
     * Creates a new instance of the object.
     * @param array $Options Options to use in initializing class.
     * @return \BLW\Interfaces\ApplicationCommand Returns a new instance of the class.
     */
    final public static function GetInstance(/* ... */)
    {
        return func_num_args()
            ? new static(func_get_arg(0))
            : new static()
        ;
    }

    /**
     * Runs the command.
     * @param \Symfony\Component\Console\Input\InputInterface $Input Input Object.
     * @param \Symfony\Component\Console\Output\OutputInterface $Output Output Object.
     * @return int The command exit code.
     */
    final public function Run(/* ... */)
    {
        return call_user_func_array(array('parent', 'run'), func_get_args());
    }

    /**
     * Get the string of the current command.
     * @return string ID / action of command.
     */
    final public function GetID()
    {
        return $this->getName();
    }


    /**
     * Set the string of the current command.
     * @param string $ID Name / Action of command.
     * @return \BLW\Interfaces\ApplicationCommand $this
     */
    final public function SetID($ID)
    {
        $this->setName($ID);

        return $this;
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
     * @return \BLW\Interfaces\ApplicationCommand $this
     */
    final public function SetParent(\BLW\Interfaces\Object $Parent)
    {
        if(!$this->_Parent instanceof \BLW\Interfaces\Object || $this->_Parent === BLW::$Base) {
            $this->_Parent = $Parent;
        }

        return $this;
    }

    /**
     * Clears parent of the current object.
     * @return \BLW\Interfaces\ApplicationCommand $this
     */
    final public function ClearParent()
    {
        $this->_Parent = NULL;
        return $this;
    }

    /**
     * Returns the parent of the current object.
     * @note Changes the current context to the parent.
     * @return \BLW\Interfaces\Object Returns <code>NULL</code> if parent does not exits.
     */
    final public function& parent()
    {
        BLW::$Self = $this->_Parent;
        return BLW::$Self;
    }
}