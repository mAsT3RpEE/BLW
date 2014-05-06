<?php
/**
 * IJob.php | Apr 7, 2014
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
 * @version GIT 0.2.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type\Cron;

use DateTime;
use DateInterval;

use BLW\Type\Command\IInput;
use BLW\Type\Command\IOutput;
use BLW\Type\Command\ICommand;


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
 * Interface for cron jobs.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +-------------+       +--------------------+
 * | JOB                                               |<------| WRAPPER     |<--+---| SERIALIZABLE       |
 * +---------------------------------------------------+       | =========== |   |   | ================== |
 * | _Interval:  DateInterval                          |       | Command     |   |   | Serializable       |
 * | _LastRun:   DateTime                              |       +-------------+   |   +--------------------+
 * | _NextRun:   DateTime                              |<------| MEDIATABLE  |   +---| COMPONENT MAPABLE  |
 * | #Interval:  getInterval()                         |       +-------------+   |   +--------------------+
 * |             setInterval()                         |                         +---| ITERABLE           |
 * | #LastRun:   _LastRun                              |                             +--------------------+
 * | #Next:      _NextRun                              |
 * |             schedule()                            |
 * +---------------------------------------------------+
 * | isExpired(): bool                                 |
 * |                                                   |
 * | $Now:  DateTime                                   |
 * +---------------------------------------------------+
 * | getInterval(): _Interval                          |
 * +---------------------------------------------------+
 * | setInterval(): IDataMapper::STATUS                |
 * |                                                   |
 * | $Interval:  DateInterval                          |
 * +---------------------------------------------------+
 * | schedule(): IDataMapper::STATUS                   |
 * |                                                   |
 * | $Time:  DateTime                                  |
 * +---------------------------------------------------+
 * | run(): mixed                                      |
 * |                                                   |
 * | $Input:   Command\IInput                          |
 * | $Output:  Command\IOutput                         |
 * | $flags:   ICommand::RUN_FLAGS                     |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\Cron
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property \DateInterval $_Interval [protected] Interval between job runs.
 * @property \DateTime $_LastRun [protected] Last run of cron job.
 * @property \DateTime $_NextRun [protected] Time of next job run.
 * @property \DateInterval $Interval [dynamic] Invokes getInterval() and setInterval().
 * @property \DateTime $LastRun [readonly] $_LastRun
 * @property \DateTime $NextRun [dynamic] $_NextRun invokes schedule().
 */
Interface IJob extends \BLW\Type\IWrapper, \BLW\Type\IMediatable, \Psr\Log\LoggerAwareInterface
{

    /**
     * Tests if job is scheduled to be run.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param \DateTime $Now
     *            [optional] Time to test against. Use <code>null</code> for current time.
     * @return bool <code>TRUE</code> if job should be run. <code>FALSE</code> otherwise.
     */
    public function isExpired(DateTime $Now = null);

    /**
     * Returns the current interval between job runs.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return \DateInterval $_Interval
     */
    public function getInterval();

    /**
     * Update the interval between job runs.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param \DateInterval $Interval
     *            New interval.
     * @return \DateInterval Old interval. <code>null</code> if none existed.
     */
    public function setInterval($Interval);

    /**
     * Schedule job to run at a specific time.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param \DateTime $Time
     *            Time to run.
     */
    public function schedule($Time);

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
     * @api BLW
     * @since 1.0.0
     * @see \BLW\Type\Command\ICommand::run() ICommand::run()
     *
     * @param \BLW\Type\Command\IInput $Input
     *            Input object to read data from.
     * @param \BLW\Type\Command\IOutput $Output
     *            Output object to write data to.
     * @param int $flags
     *            Flags used to run commands.
     * @return mixed The result of <code>IJob::run()</code>
     */
    public function run(IInput $Input, IOutput $Output, $flags = ICommand::RUN_FLAGS);
}

return true;
