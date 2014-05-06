<?php
/**
 * Job.php | Apr 7, 2014
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

use Psr\Log\LoggerInterface;

use BLW\Type\IDataMapper;
use BLW\Type\IEvent;
use BLW\Type\Command\IInput;
use BLW\Type\Command\IOutput;
use BLW\Type\Command\ICommand;
use BLW\Model\InvalidArgumentException;


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
 * +---------------------------------------------------+       +-------------+       +-------------------+
 * | JOB                                               |<------| WRAPPER     |<--+---| SERIALIZABLE      |
 * +---------------------------------------------------+       | =========== |   |   | ================= |
 * | _Interval:   DateInterval                         |       | Command     |   |   | Serializable      |
 * | _LastRun:    DateTime                             |       +-------------+   |   +-------------------+
 * | _NextRun:    DateTime                             |<------| MEDIATABLE  |   +---| COMPONENTMAPABLE  |
 * | #Interval:   getInterval()                        |       +-------------+   |   +-------------------+
 * |              setInterval()                        |                         +---| ITERABLE          |
 * | #LastRun:    _LastRun                             |                             +-------------------+
 * | #Next:       _NextRun                             |
 * |              schedule()                           |
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
 * @property \DateInterval $Interval [dynamic] Invokes getInterval() and setInterval().
 * @property \DateTime $LastRun [readonly] $_LastRun
 * @property \DateTime $NextRun [dynamic] $_NextRun invokes schedule().
 */
abstract class AJob extends \BLW\Type\AWrapper implements \BLW\Type\Cron\IJob
{

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
# LoggerAwareInterface Trait
#############################################################################################

    /**
     * PSR Logger.
     *
     * @var \Psr\Log\LoggerInterface $logger
     */
    protected $logger;

#############################################################################################
# Job Trait
#############################################################################################

    /**
     * Interval between job runs.
     *
     * @var \DateInterval $_Interval
     */
    protected $_Interval = null;

    /**
     * Last run of cron job.
     *
     * @var \DateTime $_LastRun
     */
    protected $_LastRun = null;

    /**
     * Time of next job run.
     *
     * @var \DateTime $_NextRun
     */
    protected $_NextRun = null;

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
            $this->_MediatorID = spl_object_hash($this);

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
     */
    final public function _on($EventName, $Callback, $Priority = 0)
    {
        // Parameter validation

        // Is $EventName a string
        if (is_scalar($EventName) ?  : is_callable(array(
            $EventName,
            '__toString'
        ))) {

            // Is $Callback callable
            if (is_callable($Callback)) {

                // Register event
                $Mediator = $this->getMediator();
                $ID       = $this->getMediatorID();

                if ($Mediator instanceof IMediator) {
                    $Mediator->register("$ID.$EventName", $Callback, @intval($Priority));
                }

                else
                    trigger_error(sprintf('Mediator not set with %s::setMediator()', get_class($this)), E_USER_NOTICE);
            }

            // $Callback is uncallable
            else
                throw new InvalidArgumentException(1);
        }

        // $Event name is not a string
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

        // Is $EventName a string?
        if (is_scalar($EventName) ?  : is_callable(array(
            $EventName,
            '__toString'
        ))) {

            // Trigger event
            $Mediator = $this->getMediator();
            $ID = $this->getMediatorID();

            if ($Mediator instanceof IMediator) {
                $Mediator->trigger("$ID.$EventName", $Event);
            }

            else
                trigger_error(sprintf('Mediator not set with %s::setMediator()', get_class($this)), E_USER_NOTICE);
        }

        // $EventName is not a string
        else
            throw new InvalidArgumentException(0);
    }

#############################################################################################
# LoggerAwareInterface
#############################################################################################

    /**
     * Sets a logger.
     *
     * @param \Psr\Logger\LoggerInterface $logger
     *            New Logger.
     * @return int Returns a <code>DataMapper</code> status code.
     */
    public function setLogger(LoggerInterface $logger)
    {
        // Update Logger
        $this->logger = $logger;

        // Done
        return IDataMapper::UPDATED;
    }

#############################################################################################
# Iterable Trait
#############################################################################################

    /**
     * Get the ID of the object.
     *
     * @return string Current ID.
     */
    public function getID()
    {
        return md5(strval($this));
    }

#############################################################################################
# Job Trait
#############################################################################################

    /**
     * Tests if job is scheduled to be run
     *
     * @param \DateTime $Now
     *            [optional] Time to test against. Use <code>null</code> for current time.
     * @return bool <code>TRUE</code> if job should be run. <code>FALSE</code> otherwise.
     */
    public function isExpired(DateTime $Now = null)
    {
        return $Now >= $this->_NextRun;
    }

    /**
     * Returns the current interval between job runs.
     *
     * @return \DateInterval $_Interval
     */
    public function getInterval()
    {
        return $this->_Interval;
    }

    /**
     * Update the interval between job runs.
     *
     * @param \DateInterval $Interval
     *            New interval.
     * @return int Returns a <code>DataMapper</code> status code.
     */
    public function setInterval($Interval)
    {
        // Tests if $Interval is larger than 1 min
        switch (true) {
            case ! $Interval instanceof DateInterval:
                break;

            case $Interval->y > 0:
            case $Interval->m > 0:
            case $Interval->d > 0:
            case $Interval->h > 0:
            case $Interval->i > 0:

                // Update Interval
                $this->_Interval = $Interval;

                // Done
                return IDataMapper::UPDATED;
        }

        // Error
        return IDataMapper::INVALID;
    }

    /**
     * Schedule job to run at a specific time.
     *
     * @param \DateTime $Time
     *            Time to run.
     * @return int Returns a <code>DataMapper</code> status code.
     */
    public function schedule($Time)
    {
        // Is time valid?
        if ($Time instanceof DateTime) {

            // Update next run
            $this->_NextRun = $Time;

            // Done
            return IDataMapper::UPDATED;
        }

        // Error
        return IDataMapper::INVALID;
    }

    /**
     * Runs the command.
     *
     * @see \BLW\Type\Command\ICommmand::run() ICommand::run()
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
        // Update time
        $this->_LastRun = new DateTime();

        // Set Command mediator
        if ($this->_Mediator instanceof IMediator)
            $this->_Component->setMediator($this->_Mediator);

        else
            $this->_Component->clearMediator();

        // Run component
        $return = $this->_Component->run($Input, $Output, $flags);

        // Clear Command mediator
        $this->_Component->clearMediator();

        // Done
        return $return;
    }

#############################################################################################

}

return true;
