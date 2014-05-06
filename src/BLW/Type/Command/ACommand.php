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
 * @version GIT 0.2.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type\Command;

use ArrayObject;
use DateTime;

use BLW\Type\IEvent;
use BLW\Type\IDataMapper;
use BLW\Type\IConfig;
use BLW\Type\IMediator;
use BLW\Type\Command\IInput;
use BLW\Type\Command\IOutput;

use BLW\Model\InvalidArgumentException;
use BLW\Model\Config\Generic as Config;
use BLW\Model\Command\Event as CommandEvent;


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
 * | SHUTDOWN                                          |       | ArrayAccess       |   +---| DATAMAPABLE  |
 * | ERROR                                             |       +-------------------+   |   +--------------+
 * |                                                   |                               +---| ITERABLE     |
 * | RUN_FLAGS                                         |                                   +--------------+
 * +---------------------------------------------------+
 * | [Description] string                              |
 * | [Timeout] int                                     |
 * | [Before] Closure|null                             |
 * | [After] Closure|null                              |
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
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property mixed $Command [readonly] $_Command
 * @property \BLW\Type\IConfig $Config [readonly] $_Config
 * @property int $Timeout [dynamic] $_Command[Timeout]
 */
abstract class ACommand extends \BLW\Type\AObject implements \BLW\Type\Command\ICommand
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
# Mediatable Trait
#############################################################################################

    /**
     * Pointer to current mediator.
     *
     * @var \BLW\Type\IMediatable $_Mediator
     */
    protected $_Mediator = null;

    /**
     * Current mediator id of object.
     *
     * @var string $_MediatorID
     */
    protected $_MediatorID = null;

#############################################################################################




#############################################################################################
# Mediatable Trait
#############################################################################################

    /**
     * Get the current mediator of the object.
     *
     * @return \BLW\Type\IMediator Returns <code>null</code> if no mediator is set.
     */
    final public function getMediator()
    {
        return $this->_Mediator;
    }

    /**
     * Set $Mediator dynamic property.
     *
     * @param \BLW\Type\IMediator $Mediator
     *            New mediator.
     * @return int Returns a <code>IDataMapper</code> status code.
     */
    final public function setMediator($Mediator)
    {
        // Is mediator valid
        if ($Mediator instanceof IMediator) {
            $this->_Mediator = $Mediator;

            return IDataMapper::UPDATED;
        }

        // Invalid mediator
        return IDataMapper::INVALID;
    }

    /**
     * Clear $Mediator dynamic property.
     *
     * @return int Returns a <code>IDataMapper</code> status code.
     */
    final public function clearMediator()
    {
        // Clear Mediator
        $this->_Mediator = null;

        // Done
        return IDataMapper::UPDATED;
    }

    /**
     * Generates a unique id used to identify object in mediator.
     *
     * @return string Hash of object.
     */
    public function getMediatorID()
    {
        if (! $this->_MediatorID)
            $this->_MediatorID = $this->ID ?: spl_object_hash($this);

        return $this->_MediatorID;
    }

    /**
     * Registers a function to execute on a mediator event.
     *
     * Registers a function to execute on a mediator event.
     *
     * <h4>Format:</h4>
     *
     * <pre>mixed function (\BLW\Type\IEvent $Event)</pre>
     *
     * <hr>
     *
     * @param string $EventName
     *            Event ID to attach to.
     * @param callable $Callback
     *            Function to call.
     * @param int $Priority
     *            Priotory of $Callback. (Higher priority = Higher Importance)
     * @return void
     */
    final public function _on($EventName, $Callback, $Priority = 0)
    {
        // Parameter validation
        if (is_scalar($EventName) ?  : is_callable(array(
            $EventName,
            '__toString'
        ))) {

            if (is_callable($Callback)) {

                // Register event
                $Mediator = $this->getMediator();
                $ID       = $this->getMediatorID();

                if ($Mediator instanceof IMediator)
                    $Mediator->register("$ID.$EventName", $Callback, @intval($Priority));

                else
                    trigger_error(sprintf('Mediator not set with %s::setMediator()', get_class($this)), E_USER_NOTICE);
            }

            else
                throw new InvalidArgumentException(2);
        }

        else
            throw new InvalidArgumentException(1);
    }

    /**
     * Activates a mediator event.
     *
     * @param string $EventName
     *            Event ID to activate.
     * @param \BLW\Type\IEvent $Event
     *            Event object associated with the event.
     */
    final public function _do($EventName, IEvent $Event = null)
    {
        // Parameter validation
        if (is_scalar($EventName) ?  : is_callable(array(
            $EventName,
            '__toString'
        ))) {

            // Trigger event
            $Mediator = $this->getMediator();
            $ID       = $this->getMediatorID();

            if ($Mediator instanceof IMediator)
                $Mediator->trigger("$ID.$EventName", $Event);

            else
                trigger_error(sprintf('Mediator not set with %s::setMediator()', get_class($this)), E_USER_NOTICE);
        }

        else
            throw new InvalidArgumentException(1);
    }

#############################################################################################
# ArrayAccess Trait
#############################################################################################

    /**
     * Returns whether the requested index exists
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetexists.php ArrayAccess::offsetExists()
     *
     * @param mixed $index
     *            The index being checked.
     * @return bool <code>TRUE</code> if the requested index exists, <code>FALSE</code> otherwise.
     */
    public function offsetExists($index)
    {
        return $this->_Config->offsetExists($index);
    }

    /**
     * Returns the value at the specified index
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetget.php ArrayAccess::offsetGet()
     *
     * @param mixed $index
     *            The index with the value.
     * @return mixed The value at the specified index or <code>FALSE</code>.
     */
    public function offsetGet($index)
    {
        return $this->_Config->offsetGet($index);
    }

    /**
     * Sets the value at the specified index to newval
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetset.php ArrayAccess::offsetSet()
     *
     * @param mixed $index
     *            The index being set.
     * @param mixed $newval
     *            The new value for the index.
     */
    public function offsetSet($index, $newval)
    {
        return $this->_Config->offsetSet($index, $newval);
    }

    /**
     * Unsets the value at the specified index
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetunset.php ArrayAccess::offsetUnset()
     *
     * @param mixed $index
     *            The index being unset.
     */
    public function offsetUnset($index)
    {
        return $this->_Config->offsetUnset($index);
    }

    /**
     * Get the number of public properties in the ArrayObject
     *
     * @link http://www.php.net/manual/en/countable.count.php Countable::count()
     *
     * @return int The number of public properties in the ArrayObject.
     */
    public function count()
    {
        return $this->_Config->count();
    }

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
        $this->_Config  = $Config ?: new Config(clone self::$_DefaultConfig);

        // CheckConfig
        if (! $Config->offsetExists('Timeout'))
            throw new InvalidArgumentException(1, '%header% $Config[Timeout] is not set');

        // Mediator
        if ($Mediator)
            $this->setMediator($Mediator);

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
     * @param int $flags
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
     * @param int $Type
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
     * @return bool <code>TRUE</code> on success. <code>False</code> otherwise.
     */
    public function doNotify($Type = ICommand::GENERAL, array $Info = array())
    {
        // Is $Type an integer?
        if (is_numeric($Type)) {

            // Does command have a mediator?
            if ($this->_Mediator instanceof IMediator) {
                // Trigger hook
                $this->_do('Notify', new CommandEvent($this, $Type, $Info));

                // Done
                return true;
            }
        }

        // Invalid $Callback
        else
            throw new InvalidArgumentException(0);

        // Error
        return false;
    }

    /**
     * Registers a callback to execute on command notification.
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Callback</code> is not callable.
     *
     * @param callable $Callback
     *            Callback to invoke.
     * @return bool <code>TRUE</code> on success. <code>False</code> otherwise.
     */
    public function onNotify($Callback)
    {
        // Is $Callback callable?
        if (is_callable($Callback)) {

            // Does command have a mediator?
            if ($this->_Mediator instanceof IMediator) {
                // Register hook
                $this->_on('Notify', $Callback);

                // Done
                return true;
            }
        }

        // Invalid $Callback
        else
            throw new InvalidArgumentException(0);

        // Error
        return false;
    }

    /**
     * Registers a callback to execute on command read from input.
     *
     * @param callable $Callback
     *            Callback to invoke.
     */
    public function onInput($Callback)
    {
        // Is $Callback callable?
        if (is_callable($Callback)) {

            // Does command have a mediator?
            if ($this->_Mediator instanceof IMediator) {
                // Trigger hook
                $this->_on('Input', $Callback);

                // Done
                return true;
            }
        }

        // Invalid $Callback
        else
            throw new InvalidArgumentException(0);

            // Error
        return false;
    }

    /**
     * Registers a callback to execute on command write to output.
     *
     * @param callable $Callback
     *            Callback to invoke.
     */
    public function onOutput($Callback)
    {
        // Is $Callback callable?
        if (is_callable($Callback)) {

            // Does command have a mediator?
            if ($this->_Mediator instanceof IMediator) {
                // Trigger hook
                $this->_on('Output', $Callback);

                // Done
                return true;
            }
        }

        // Invalid $Callback
        else
            throw new InvalidArgumentException(0);

        // Error
        return false;
    }

    /**
     * Registers a callback to execute on command write to error.
     *
     * @param callable $Callback
     *            Callback to invoke.
     * @return void
     */
    public function onError($Callback)
    {
        // Is $Callback callable?
        if (is_callable($Callback)) {

            // Does command have a mediator?
            if ($this->_Mediator instanceof IMediator) {
                // Trigger hook
                $this->_on('Error', $Callback);

                // Done
                return true;
            }
        }

        // Invalid $Callback
        else
            throw new InvalidArgumentException(0);

        // Error
        return false;
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
}

return true;
