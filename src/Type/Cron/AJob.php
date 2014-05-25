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
 * @version GIT: 0.2.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type\Cron;


use DateTime;
use DateInterval;
use Psr\Log\LoggerInterface;
use BLW\Type\IDataMapper;
use BLW\Type\ADataMapper;
use BLW\Type\Command\IInput;
use BLW\Type\Command\IOutput;
use BLW\Type\Command\ICommand;
use BLW\Type\AMediatableWrapper;
use BLW\Type\IMediator;

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
 * | #NextRun:    _NextRun                             |
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
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property \DateInterval $Interval [dynamic] Invokes getInterval() and setInterval().
 * @property \DateTime $LastRun [readonly] $_LastRun
 * @property \DateTime $NextRun [dynamic] $_NextRun invokes schedule().
 */
abstract class AJob extends \BLW\Type\AMediatableWrapper implements \BLW\Type\Cron\IJob
{

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
# LoggerAwareInterface
#############################################################################################

    /**
     * Sets a logger.
     *
     * @param \Psr\Log\LoggerInterface $logger
     *            New Logger.
     * @return integer Returns a <code>DataMapper</code> status code.
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
     * @return boolean <code>TRUE</code> if job should be run. <code>FALSE</code> otherwise.
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
     * @return integer Returns a <code>DataMapper</code> status code.
     */
    public function setInterval($Interval)
    {
        // Test $Interval
        switch (true) {

            // Invalid $Interval
            case ! $Interval instanceof DateInterval:
                return IDataMapper::INVALID;

            // Interval greater than 1 min
            case $Interval->y > 0:
            case $Interval->m > 0:
            case $Interval->d > 0:
            case $Interval->h > 0:
            case $Interval->i > 0:

                // Update Interval
                $this->_Interval = $Interval;

                // Done
                return IDataMapper::UPDATED;

            // Interval less than 1 min
            default:
                return IDataMapper::INVALID;
        }
    }

    /**
     * Schedule job to run at a specific time.
     *
     * @param \DateTime $Time
     *            Time to run.
     * @return integer Returns a <code>DataMapper</code> status code.
     */
    public function schedule($Time)
    {
        // Is time valid?
        if ($Time instanceof DateTime) {

            // Update next run
            $this->_NextRun = $Time;

            // Done
            return IDataMapper::UPDATED;

        } else {

            // Error
            return IDataMapper::INVALID;
        }
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
     * @param integer $flags
     *            Run flags.
     * @return mixed Result of command.
     */
    public function run(IInput $Input, IOutput $Output, $flags = ICommand::RUN_FLAGS)
    {
        // Update time
        $this->_LastRun = new DateTime();

        // Set Command mediator
        if ($this->_Mediator instanceof IMediator) {
            $this->_Component->setMediator($this->_Mediator);

        } else {
            $this->_Component->clearMediator();
        }

        // Run component
        $return = $this->_Component->run($Input, $Output, $flags);

        // Clear Command mediator
        $this->_Component->clearMediator();

        // Update next run to last run rounded to 5 min
        $Diff                = $this->_LastRun->getTimestamp() % 300;
        $Diff                = $Diff > 200
            ? 300 - $Diff
            : 0   - $Diff;

        $this->_NextRun->setTimestamp($this->_LastRun->getTimestamp() + $Diff);

        // UpdateInterval
        $this->_NextRun->add($this->Interval);

        // Done
        return $return;
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
            // IJob
            case 'Interval':
                return $this->getInterval();
            case 'LastRun':
                return $this->_LastRun;
            case 'NextRun':
                return $this->_NextRun;
            // IComponentMapable
            default:
                // Map unkown to component
                if (isset($this->_Component->{$name})) {
                    return $this->_Component->{$name};

                // Undefined property
                } else {
                    trigger_error(sprintf('Undefined property: %s::$%s', get_class($this), $name), E_USER_NOTICE);
                }
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
            // IJob
            case 'Interval':
                return $this->getInterval() !== null;
            case 'LastRun':
                return $this->_LastRun !== null;
            case 'NextRun':
                return $this->_NextRun !== null;
            // IComponentMapable
            default:
                return isset($this->_Component->{$name});
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
                $result = IDataMapper::READONLY;
                break;
            case 'Parent':
                $result = $this->setParent($value);
                break;
            // IMediatable
            case 'Mediator':
                $result = $this->setMediator($value);
                break;
            case 'MediatorID':
                $result = IDataMapper::READONLY;
                break;
            // IJob
            case 'Interval':
                $result = $this->setInterval($value);
                break;
            case 'LastRun':
                $result = IDataMapper::READONLY;
                break;
            case 'NextRun':
                $result = $this->schedule($value);
                break;
            // IComponentMapable
            default:
                // Try to set component property
                try {
                    $this->_Component->{$name} = $value;
                    $result = IDataMapper::UPDATED;
                }

                // Error
                catch (\Exception $e) {
                    $result = IDataMapper::UNDEFINED;
                }
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
            // IComponentMapable
            default:
                unset($this->_Component->{$name});
        }
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
