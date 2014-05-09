<?php
/**
 * IHandler.php | Apr 8, 2014
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
 * @package BLW\Cron
 * @version GIT: 0.2.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type\Cron;

use DateTime;
use BLW\Type\IObjectStorage;
use BLW\Type\Command\IOutput;
use BLW\Type\Command\IInput;
use BLW\Type\Command\ICommand;
use Psr\Log\LoggerAwareInterface;

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
 * Interface for Cron Handlers
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +------------------------+       +-------------------+
 * | HANDLER                                           |<------| OBJECTSTORAGE          |<--+---| SplObjectStorage  |
 * +---------------------------------------------------+       | ====================== |   |   +-------------------+
 * | THREAD_COMPATIBLE:                                |       | Job                    |   |   | SERIALIZABLE      |
 * | CRON_FLAGS:                                       |       +------------------------+   |   | ================= |
 * +---------------------------------------------------+<------| MEDIATABLE             |   +---| Serializable      |
 * | _Config:              IConfig                     |       +------------------------+   |   +-------------------+
 * | _isThreadCompatible:  bool                        |<------| LoggableAwareInterface |   +---| ITERABLE          |
 * +---------------------------------------------------+       +------------------------+       +-------------------+
 * | enterMutex(): bool                                |
 * +---------------------------------------------------+
 * | exitMutex(): bool                                 |
 * +---------------------------------------------------+
 * | run(): mixed                                      |
 * |                                                   |
 * | $Input:   Command\IInput                          |
 * | $Output:  Command\IOutput                         |
 * | $Now:     DateTime                                |
 * | $flags:   ICommand::RUN_FLAGS                     |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\Cron
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property \BLW\Type\IConfig $_Config [protected] Cron configuration.
 * @property boolean $_isThreadCompatible [protected] Whether cron can run in parrallel with other instances.
 */
interface IHandler extends \BLW\Type\IObjectStorage, \BLW\Type\IMediatable, \Psr\Log\LoggerAwareInterface
{
    // CRON FLAGS
    const THREAD_COMPATIBLE = 0x002;
    const CRON_FLAGS        = 0x000;

    /**
     * Enters a mutually exclusive execution enviroment.
     *
     * <h4>Note</h4>
     *
     * <p>Used to prevent thread unsafe cron handlers
     * from executing in parallel.</p>
     *
     * <hr>
     *
     * @api BLW
     * @since   1.0.0
     * @see \BLW\Type\Cron\IHandler::exitMutex()
     *
     * @return boolean <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function enterMutex();

    /**
     * Exits a mutually exclusive execution enviroment.
     *
     * @api BLW
     * @since   1.0.0
     * @see \BLW\Type\Cron\IHandler::enterMutex()
     *
     * @return boolean <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function exitMutex();

    /**
     * Runs cron handler.
     *
     * <h4>Note</h4>
     *
     * <p>If <code>IJob</code> passed to handler is not fully
     * serializable, errors will be generated when handler is
     * serialized.</p>
     *
     * <p>Cron handler uses <code>$Now</code> to select a cron job
     * and execute the 1st job that has expired.</p>
     *
     * <p>Currently we only support running 1 job at a time since
     * BLW library has to run on all servers which requires all our
     * code to run as efficiently as possible. Which is quite
     * hypocritical frankly.</p>
     *
     * <hr>
     *
     * @see \BLW\Type\Command\ICommand::run() ICommand::run()
     *
     * @param \BLW\Type\Command\IInput $Input
     *            Input object to read data from.
     * @param \BLW\Type\Command\IOutput $Output
     *            Output object to write data to.
     * @param \DateTime $Now
     *            [optional] Expected time of execution. Pass <code>null</code> for current time.
     * @param integer $flags
     *            [optional] Flags used to run commands.
     * @return mixed The result of <code>IJob::run()</code>
     */
    public function run(IInput $Input, IOutput $Output, DateTime $Now = null, $flags = ICommand::RUN_FLAGS);
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
