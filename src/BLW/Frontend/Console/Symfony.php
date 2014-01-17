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
namespace BLW\Frontend\Console; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

/**
 * Default BLW object.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 */
class Symfony extends \BLW\Type\Adaptor implements \BLW\Interfaces\Application, \BLW\Interfaces\View
{
    /**
     * @var string $_Class Used by GetInstance to generate instance of class
     */
    protected static $_Class = '\\Symfony\\Component\\Console\\Application';

    /**
     * @var bool Whether application has been stopped by stop command.
     */
    private $_isStopped = false;

    /**
     * @var array $DefaultOptions Default options used by class if not set in configure.
     * @api BLW
     * @since 0.1.0
     * @see \BLW\Model\Application\SymfonyConsole::configure() SymfonyConsole::configure()
     */
    public static $DefaultOptions = array(
        'AppName'   => 'BLW Library'
        ,'Version'  => BLW
        ,'Input'    => NULL
        ,'Output'   => NULL
    );

    /**
     * @var bool $Initialized Used to store class information status.
     */
    protected static $_Initialized = false;

    /**
     * @var \stdClass $Options Configuration Options
     */
    public $Options = NULL;

    /**
     * Initializes Class for subsequent use.
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

                unset(self::$DefaultOptions['hard_init']);
            }

            // Return Options
            return self::$DefaultOptions;
        }

        else {
            // Initialize children
            if(!$class::$_Initialized || isset($Data['hard_init'])) {
                $Parent                 = get_parent_class($class);
                $class::$DefaultOptions = array_replace($Parent::Initialize(), $class::$DefaultOptions, $Data);
                $class::$_Initialized   = true;

                unset($class::$DefaultOptions['hard_init']);
            }
        }
    }

    /**
     * Configures the application.
     * @param array|\Traversable $Options Congigurations options.
     * @return \BLW\Interfaces\Application $this.
     */
    final public function configure($Options = array())
    {
        if (is_array($Options) || $Options instanceof \Traversable) {

            if (!$this->Options instanceof \stdClass) {
                $this->Options = (object) array_replace(static::$DefaultOptions, (is_array($Options)? $Options : iterator_to_array($Options)));

                $this->GetSubject()->setDispatcher(\BLW\Model\Object::GetMediator());
            }

            else {
                foreach ($Options as $k => $Option) {
                    $this->Options->{$k} = $Option;
                }
            }
        }

        else {
            throw new \BLW\Model\InvalidArgumentException(0);
        }

        $this->doConfigure();

        return $this;
    }

    /**
     * Function that is called after main configuratin
     * @return \BLW\Interfaces\Application $this.
     */
    public function doConfigure()
    {
        return $this;
    }

    /**
     * Adds a command to the Application.
     * @param \BLW\Interfaces\Command $Command Command to add to application.
     * @return \BLW\Interfaces\Application $this.
     */
    final public function push(\BLW\Interfaces\ApplicationCommand $Command)
    {
        $this->GetSubject()->add($Command);

        return $this;
    }

    /**
     * Start the application.
     * @return \BLW\Interfaces\Application $this.
     */
    final public function start()
    {
        $this->_isStopped = false;

        $this->GetSubject()->run($this->Options->Input, $this->Options->Output);

        return $this;
    }

    /**
     * Render output
     * @return \BLW\Interfaces\View $this
     */
    final public function Render()
    {
        return $this->start();
    }

    /**
     * Stop the application.
     * @return \BLW\Interfaces\Application $this.
     */
    final public function stop()
    {
        $this->_isStopped = true;

        return $this;
    }

    /**
     * Whether application has been stopped by stop method.
     * @return boolean Value of $_isStopped.
     */
    final public function isStopped()
    {
        return !!$this->_isStopped;
    }

    /**
     * Hook that is called just before an object is serialized.
     * @return \BLW\Interfaces\Adaptor $this
     */
    public function doSerialize() {return $this;}

    /**
     * Hook that is called just after an object is unserialized.
     * @return \BLW\Interfaces\Adaptor $this
     */
    public function doUnSerialize() {return $this;}
}