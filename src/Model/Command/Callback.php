<?php
/**
 * Callback.php | Mar 31, 2014
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
namespace BLW\Model\Command;

use ArrayObject;
use BLW\Type\IConfig;
use BLW\Type\IMediator;
use BLW\Type\Command\IInput;
use BLW\Type\Command\IOutput;
use BLW\Model\InvalidArgumentException;
use Jeremeamia\SuperClosure\SerializableClosure;

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
 * Command for handling Callback commands
 *
 * @package BLW\Command
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property mixed $Command [readonly] $_Command
 * @property \BLW\Type\IConfig $Config [readonly] $_Config
 * @property integer $Timeout [dynamic] $_Command[Timeout]
 * @property $Mediator \BLW\Type\IMediator [dynamic] Invokes setMediator() and getMediator().
 * @property $MediatorID string [readonly] Invokes getMediator().
 */
class Callback extends \BLW\Type\Command\ACommand
{

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
     *            Command Name / Label
     */
    public function __construct($Command, IConfig $Config, IMediator $Mediator = null, $ID = null)
    {
        // Make closures serializable
        if ($Command instanceof \Closure) {
            $Command = new SerializableClosure($Command);
        }

        if (is_null($ID)) {
            $ID = $this->createID();
        }

        // Is $Command callable?
        if (! is_callable($Command)) {
            throw new InvalidArgumentException(0);
        }

        // CheckConfig
        elseif (! $Config->offsetExists('Timeout')) {
            throw new InvalidArgumentException(1, '%header% $Config[Timeout] is not set');
        }

        // Set properties
        $this->_Config     = $Config;
        $this->_Command    = $Command;
        $this->_DataMapper = new ArrayObject;

        // ID
        $this->ID          = $ID;

        // Mediator
        if ($Mediator) {
            $this->setMediator($Mediator);
        }
    }

    /**
     * Performs the actual command run.
     *
     * @see \BLW\Type\ACommand::doRun() ACommand::doRun()
     * @see \BLW\Type\ACommand::run() ACommand::run()
     *
     * @param \BLW\Type\Command\IInput $Input
     *            Input object to read data from.
     * @param \BLW\Type\Command\IOutput $Output
     *            Output object to write data to.
     * @return mixed Result of command.
     */
    public function doRun(IInput $Input, IOutput $Output)
    {
        // Call callback
        return call_user_func($this->_Command, $Input, $Output, $this);
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
