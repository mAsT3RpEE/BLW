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

use BLW\Type\IConfig;
use BLW\Type\IMediator;
use BLW\Type\Command\IInput;
use BLW\Type\Command\IOutput;

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
 * | $Type:  ICommand::NOTIFY_FLAGS                    |
 * | $Info:  array                                     |
 * +---------------------------------------------------+
 * | onNotify():                                       |
 * |                                                   |
 * | $Callable:  callable                              |
 * +---------------------------------------------------+
 * | onInput():                                        |
 * |                                                   |
 * | $Callable:  _InStream::onInput()                  |
 * +---------------------------------------------------+
 * | onOutput():                                       |
 * |                                                   |
 * | $Callable:  _OutStream::onOutput()                |
 * +---------------------------------------------------+
 * | onError():                                        |
 * |                                                   |
 * | $Callable:  _ErrStream::onError()                 |
 * +---------------------------------------------------+
 * | __toString():  string                             |
 * +---------------------------------------------------+
 * </pre>
 *
 * @package BLW\Command
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property mixed $_Command [protected] Command to run
 * @property \BLW\Type\IConfig $_Config [protected] Command configuration
 * @property aray $_DefaultConfig [protected static] Default configuration for command
 * @property mixed $Command [readonly] $_Command
 * @property \BLW\Type\IConfig $Config [readonly] $_Config
 * @property integer $Timeout [dynamic] $_Command[Timeout]
 */
interface ICommand extends \BLW\Type\IObject, \BLW\Type\IMediatable
{
    // RUN FLAGS
    const RUN_FLAGS = 0x000;

    // NOTIFY FLAGS
    const GENERAL  = 0x0002;
    const RUN      = 0x0004;
    const SHUTDOWN = 0x0008;
    const FINISH   = 0x0010;

    /**
     * Constructor
     *
     * @param mixed $Command
     *            Command to run.
     * @param IConfig $Config
     *            Command configuration.
     * @param IMediator $Mediator
     *            Mediator for command.
     * @param string $ID
     *            Command Name / Label.
     * @return void
     */
    public function __construct($Command, IConfig $Config, IMediator $Mediator = null, $ID = null);

    /**
     * Runs the command.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param \BLW\Type\Command\IInput $Input
     *            Input object to read data from.
     * @param \BLW\Type\Command\IOutput $Output
     *            Output object to write data to.
     * @param integer $flags
     *            Run flags.
     * @return mixed
     *            The result of the command.
     */
    public function run(IInput $Input, IOutput $Output, $flags = ICommand::RUN_FLAGS);

    /**
     * Triggers a command hook.
     *
     * @api BLW
     * @since   1.0.0
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
     * @return void
     */
    public function doNotify($Type = ICommand::GENERAL, array $Info = array());

    /**
     * Registers a callback to execute on command notification.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param callable $Callable
     *            Callback to invoke.
     * @return boolean <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function onNotify($Callable);

    /**
     * Registers a callback to execute on command read from input.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param callable $Callable
     *            Callback to invoke.
     * @return boolean <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function onInput($Callable);

    /**
     * Registers a callback to execute on command write to output.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param callable $Callable
     *            Callback to invoke.
     * @return boolean <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function onOutput($Callable);

    /**
     * Registers a callback to execute on command write to error.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param callable $Callable
     *            Callback to invoke.
     * @return boolean <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function onError($Callable);

    /**
     * All objects must have a string representation.
     *
     * @api BLW
     * @since   1.0.0
     * @link http://www.php.net/manual/en/language.oop5.magic.php#object.tostring __toString()
     *
     * @return string $this
     */
    public function __toString();
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
