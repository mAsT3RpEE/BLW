<?php
/**
 * ICommand.php | Mar 30, 2014
 *
 * @filesource
 * @license MIT
 * @copyright Copyright (c) 2013-2018, mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 *
 * @package BLW\Command
 * @version GIT: 0.2.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type\Command;

use ArrayObject;
use DateTime;
use BLW\Type\IDataMapper;
use BLW\Type\ADataMapper;
use BLW\Type\IConfig;
use BLW\Type\IMediator;
use BLW\Model\InvalidArgumentException;
use BLW\Model\Config\Generic as Config;
use BLW\Model\Command\Event as CommandEvent;

// @codeCoverageIgnoreStart
if (! defined('BLW')) {

    if (strstr($_SERVER['PHP_SELF'], basename(__FILE__))) {
        header("$_SERVER[SERVER_PROTOCOL] 404 Not Found");
        header('Status: 404 Not Found');

        $_SERVER['REDIRECT_STATUS'] = 404;

        echo "<html>\r\n<head><title>404 Not Found</title></head><body bgcolor=\"white\">\r\n<center><h1>404 Not Found</h1></center>\r\n<hr>\r\n<center>nginx/1.5.9</center>\r\n</body>\r\n</html>\r\n";
        exit();
    }

    return false;
}
// @codeCoverageIgnoreEnd


/**
 * Interface for all commands / stored instructions.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +-------------------+       +--------------+
 * | COMMAND                                           |<------| OBJECT            |<--+---| SERIALIZABLE |
 * +---------------------------------------------------+       +-------------------+   |   | ============ |
 * | GENERAL                                           |       | MEDIATABLE        |   |   | Serializable |
 * | RUN                                               |       +-------------------+   |   +--------------+
 * | SHUTDOWN                                          |                               +---| DATAMAPABLE  |
 * | ERROR                                             |                               |   +--------------+
 * |                                                   |                               +---| ITERABLE     |
 * | RUN_FLAGS                                         |                                   +--------------+
 * +---------------------------------------------------+
 * | _Command:        mixed                            |
 * | _Config:         IConfig                          |
 * | _DefaultConfig:  array                            |
 * | #Command:        _Command                         |
 * | #Config:         _Config                          |
 * | #Default:        _DefaultConfig                   |
 * +---------------------------------------------------+
 * | __construct():                                    |
 * |                                                   |
 * | $Command:   mixed                                 |
 * | $Config:    IConfig                               |
 * | $Mediator:  IMediator|null                        |
 * | $ID:        ID|null                               |
 * +---------------------------------------------------+
 * | run(): mixed                                      |
 * |                                                   |
 * | $Input:   ICommandInput                           |
 * | $Output:  ICommandOutput                          |
 * | $flags:   ICommand::RUN_FLAGS                     |
 * +---------------------------------------------------+
 * | doNotify():                                       |
 * |                                                   |
 * | $Info:  array                                     |
 * | $Type:  ICommand::NOTIFY_FLAGS                    |
 * +---------------------------------------------------+
 * | onNotify():                                       |
 * |                                                   |
 * | $Callback:  callable                              |
 * +---------------------------------------------------+
 * | onInput():                                        |
 * |                                                   |
 * | $Callback:  _InStream::onInput()                  |
 * +---------------------------------------------------+
 * | onOutput():                                       |
 * |                                                   |
 * | $Callback:  _OutStream::onOutput()                |
 * +---------------------------------------------------+
 * | onError():                                        |
 * |                                                   |
 * | $Callback:  _ErrStream::onError()                 |
 * +---------------------------------------------------+
 * | __toString(): string                              |
 * +---------------------------------------------------+
 * </pre>
 *
 * @package BLW\Command
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property mixed $Command [readonly] $_Command
 * @property \BLW\Type\IConfig $Config [readonly] $_Config
 * @property integer $Timeout [dynamic] $_Command[Timeout]
 */
abstract class ACommand extends \BLW\Type\AMediatableObject implements \BLW\Type\Command\ICommand
{

#############################################################################################
# Command Trait
#############################################################################################

    /**
     * Command to run.
     *
     * @var mixed $_Command
     */
    protected $_Command = null;

    /**
     * Command configuration.
     *
     * @var \BLW\Type\IConfig $_Config
     */
    protected $_Config = null;

    /**
     * Default configuration for command.
     *
     * @var aray $_DefaultConfig
     */
    protected static $_DefaultConfig = array(
        'Timeout'     => 30,
        'Description' => '',
        'Before'      => null,
        'After'       => null
    );

#############################################################################################




#############################################################################################
# Command Trait
#############################################################################################

    /**
     * Constructor
     *
     * @param mixed $Command
     *            Command to run.
     * @param IConfig $Config
     *            Command configuration.
     *
     * <ul>
     * <li><b>Timeout</b>: <i>int</i> Timeout to wait while receiving input and output.</li>
     * <li><b>After</b>: <i>callable</i> Callback to call before run starts. (Not supported by most commands).</li>
     * <li><b>Before</b>: <i>callable</i> Callback to call after run has completed. (Not supported by most commands).</li>
     * <li><b>Description</b>: <i>string</i> Description of command.</li>
     * </ul>
     *
     * @param IMediator $Mediator
     *            Mediator for command.
     * @param string $ID
     *            Command Name / Label.
     */
    public function __construct($Command, IConfig $Config = null, IMediator $Mediator = null, $ID = null)
    {
        // Set properties
        $this->_Command = $Command;
        $this->_Config  = $Config ?: new Config(self::$_DefaultConfig + array());

        // CheckConfig
        if (! $Config->offsetExists('Timeout')) {
            throw new InvalidArgumentException(1, '%header% $Config[Timeout] is not set');
        }

        // Mediator
        if ($Mediator) {
            $this->setMediator($Mediator);
        }

        // ID
        $this->ID = is_null($ID)
            ? $this->createID()
            : $ID;

        // DataMapper
        $this->_DataMapper = new ArrayObject();
    }

    /**
     * Runs the command.
     *
     * @param \BLW\Type\Command\IInput $Input
     *            Input object to read data from.
     * @param \BLW\Type\Command\IOutput $Output
     *            Output object to write data to.
     * @param integer $flags
     *            Run flags.
     * @return mixed Result of command.
     */
    public function run(IInput $Input, IOutput $Output, $flags = ICommand::RUN_FLAGS)
    {
        // Is mediator set?
        if (($Mediator = $this->getMediator()) instanceof IMediator) {

            // Update Input
            $Input->setMediator($Mediator);
            $Input->setMediatorID($this->getMediatorID());

            // Update Output
            $Output->setMediator($Mediator);
            $Output->setMediatorID($this->getMediatorID());
        }

        // Perform pre run
        $this->doNotify(ICommand::RUN, array(
            'flags' => &$flags
        ));

        // Evaluate flags

        // .....

        // Perform run
        $start  = new DateTime();
        $return = $this->doRun($Input, $Output);

        // Perform post run
        $this->doNotify(ICommand::SHUTDOWN, array(
            'Result' => &$return,
            'Start' => &$start
        ));

        // Reset Input
        $Input->clearMediator();
        $Input->setMediatorID('*');

        // Reset Output
        $Output->clearMediator();
        $Output->setMediatorID('*');

        // Done
        return $return;
    }

    /**
     * Performs the actual command run.
     *
     * @param \BLW\Type\Command\IInput $Input
     *            Input object to read data from.
     * @param \BLW\Type\Command\IOutput $Output
     *            Output object to write data to.
     * @return mixed Result of command.
     */
    abstract public function doRun(IInput $Input, IOutput $Output);

    /**
     * Triggers a command hook.
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Type</code> is not numeric.
     *
     * @param integer $Type
     *            Type of notification:
     *
     * <ul>
     * <li><b>GENERAL</b>: Gerenic notification.</li>
     * <li><b>RUN</b>: Triggered just before command run.</li>
     * <li><b>SHUTDOWN</b>: Triggered after command run.</li>
     * <li><b>ERROR</b>: Triggered on command error.</li>
     * </ul>
     *
     * @param array $Info
     *            Event context.
     * @return boolean <code>TRUE</code> on success. <code>False</code> otherwise.
     */
    public function doNotify($Type = ICommand::GENERAL, array $Info = array())
    {
        // Is $Type an integer?
        if (! is_numeric($Type)) {
            throw new InvalidArgumentException(0);

        // Does command have a mediator?
        } elseif ($this->_Mediator instanceof IMediator) {
                // Trigger hook
                $this->_do('Notify', new CommandEvent($this, $Type, $Info));

                // Done
                return true;

        // No mediator
        } else {
            return false;
        }
    }

    /**
     * Registers a callback to execute on command notification.
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Callback</code> is not callable.
     *
     * @param callable $Callback
     *            Callback to invoke.
     * @return boolean <code>TRUE</code> on success. <code>False</code> otherwise.
     */
    public function onNotify($Callback)
    {
        // Is $Callback uncallable?
        if (! is_callable($Callback)) {

            throw new InvalidArgumentException(0);

        // Does command have a mediator?
        } elseif ($this->_Mediator instanceof IMediator) {

            // Register hook
            $this->_on('Notify', $Callback);

            // Done
            return true;

        // No mediator
        } else {
            return false;
        }
    }

    /**
     * Registers a callback to execute on command read from input.
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Callback</code> is not callable.
     *
     * @param callable $Callback
     *            Callback to invoke.
     * @return boolean <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function onInput($Callback)
    {
        // Is $Callback uncallable?
        if (! is_callable($Callback)) {
            throw new InvalidArgumentException(0);

        // Does command have a mediator?
        } elseif ($this->_Mediator instanceof IMediator) {

            // Trigger hook
            $this->_on('Input', $Callback);

            // Done
            return true;

        // No mediator
        } else {
            return false;
        }
    }

    /**
     * Registers a callback to execute on command write to output.
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Callback</code> is not callable.
     *
     * @param callable $Callback
     *            Callback to invoke.
     * @return boolean <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function onOutput($Callback)
    {
        // Is $Callback callable?
        if (! is_callable($Callback)) {
            throw new InvalidArgumentException(0);

        // Does command have a mediator?
        } elseif ($this->_Mediator instanceof IMediator) {

            // Trigger hook
            $this->_on('Output', $Callback);

            // Done
            return true;

        // Else
        } else {
            return false;
        }
    }

    /**
     * Registers a callback to execute on command write to error.
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Callback</code> is not callable.
     *
     * @param callable $Callback
     *            Callback to invoke.
     * @return boolean <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function onError($Callback)
    {
        // Is $Callback callable?
        if (! is_callable($Callback)) {
            throw new InvalidArgumentException(0);

        // Does command have a mediator?
        } elseif ($this->_Mediator instanceof IMediator) {

            // Trigger hook
            $this->_on('Error', $Callback);

            // Done
            return true;

        // No mediator
        } else {
            return false;
        }
    }

    /**
     * All objects must have a string representation.
     *
     * @link http://www.php.net/manual/en/language.oop5.magic.php#object.tostring __toString()
     *
     * @return string $this
     */
    public function __toString()
    {
        return sprintf('[Command:%s:%s]', basename(get_class($this)), $this->getID());
    }

    /**
     * Dynamic properties.
     *
     * @param string $name Label of property to search for.
     * @return mixed Returns <code>NULL</code> if not found.
     */
    public function __get($name)
    {
        switch ($name) {
            // ISerializable
            case 'Status':
                return $this->_Status;
            case 'Serializer':
                return $this->getSerializer();
            // IIterable
            case 'Parent':
                return $this->_Parent;
            case 'ID':
                return $this->getID();
            // IMediatable
            case 'Mediator':
                return $this->getMediator();
            case 'MediatorID':
                return $this->getMediatorID();
            // ICommand
            case 'Command':
                return $this->_Command;
            case 'Config':
                return $this->_Config;
            case 'Default':
                return new Config(array() + self::$_DefaultConfig);
            // Undefined property
            default:
                trigger_error(sprintf('Undefined property %s::$%s', get_class($this), $name), E_USER_NOTICE);
        }
    }


    /**
     * Dynamic properties.
     *
     * @param string $name Label of property to search for.
     * @return boolean Returns a <code>TRUE</code> if property exists. <code>FALSE</code> otherwise.
     */
    public function __isset($name)
    {
        switch ($name) {
            // ISerializable
            case 'Status':
            case 'Serializer':
                return true;
            // IIterable
            case 'Parent':
                return $this->_Parent !== null;
            case 'ID':
                return $this->getID() !== null;
            // IMediatable
            case 'Mediator':
                return $this->getMediator() !== null;
            case 'MediatorID':
                return $this->getMediatorID() !== null;
            // ICommand
            case 'Command':
                return $this->_Command !== null;
            case 'Config':
                return $this->_Config !== null;
            case 'Default':
                return true;
            // Undefined property
            default:
                false;
        }
    }

    /**
     * Dynamic properties.
     *
     * @param string $name Label of property to set.
     * @param mixed $value Value of property.
     */
    public function __set($name, $value)
    {
        // Try to set property
        switch ($name) {
            // ISerializable
            case 'Status':
            case 'Serializer':
                $result = IDataMapper::READONLY;
                break;
            // IIterable
            case 'ID':
                $result = $this->setID($value);
                break;
            case 'Parent':
                $result = $this->setParent($value);
                break;
            // IMediatable
            case 'Mediator':
                $result = $this->setMediator($value);
                break;
            case 'MediatorID':
            // ICommand
            case 'Command':
            case 'Config':
            case 'Default':
                $result = IDataMapper::READONLY;
                break;
            // Undefined property
            default:
                $result = IDataMapper::UNDEFINED;
        }

        // Check results
        if (list($Message, $Level) = ADataMapper::getErrorInfo($result, get_class($this), $name)) {
            trigger_error($Message, $Level);
        }
    }

    /**
     * Unmap dynamic properties from DataMapper.
     *
     * @param string $name Label of dynamic property. (case sensitive)
     */
    public function __unset($name)
    {
        // Try to set property
        switch ($name) {
            // ISerializable
            case 'Status':
                $this->clearStatus();
                break;
            // IIterable
            case 'Parent':
                $this->clearParent();
                break;
            case 'Mediator':
                $this->clearMediator();
                break;
            // Undefined property
        }
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
