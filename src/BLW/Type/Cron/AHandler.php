<?php
/**
 * AHandler.php | Apr 8, 2014
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

use BLW\Type\IObjectStorage;
use BLW\Type\IDataMapper;
use BLW\Type\IEvent;
use BLW\Type\Command\IOutput;
use BLW\Type\Command\IInput;
use BLW\Type\Command\ICommand;

use BLW\Model\InvalidArgumentException;

use Psr\Log\LoggerInterface;


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
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property \BLW\Type\IConfig $_Config [protected] Cron configuration.
 * @property bool $_isThreadCompatible [protected] Whether cron can run in parrallel with other instances.
 */
abstract class AHandler extends \BLW\Type\AObjectStorage implements \BLW\Type\Cron\IHandler
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
# Handler Trait
#############################################################################################

    /**
     * Resource handle of lockfile.
     *
     * @var resource $_LockHandle
     */
    private $_LockHandle = false;

    /**
     * Whether to run jobs outside mutex or not.
     *
     * @var bool $_isThreadCompatible
     */
    protected $_isThreadCompatible = false;

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

        // Is $EventName a string?
        if (is_scalar($EventName) ?  :is_callable(array(
            $EventName,
            '__toString'
        ))) {

            // Is $Callback callable
            if (is_callable($Callback)) {

                // Register event
                $Mediator = $this->getMediator();
                $ID = $this->getMediatorID();

                if ($Mediator instanceof IMediator) {
                    $Mediator->register("$ID.$EventName", $Callback, @intval($Priority));
                }

                else
                    trigger_error(sprintf('Mediator not set with %s::setMediator()', get_class($this)), E_USER_NOTICE);
            }

            // Callback not callable
            else
                throw new InvalidArgumentException(1);
        }

        // $Event name is not a string
        else
            throw new InvalidArgumentException(0);
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
            $ID = $this->getMediatorID();

            if ($Mediator instanceof IMediator) {
                $Mediator->trigger("$ID.$EventName", $Event);
            }

            else
                trigger_error(sprintf('Mediator not set with %s::setMediator()', get_class($this)), E_USER_NOTICE);
        }

        // $Event name is not a string
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
# Handler Trait
#############################################################################################

    /**
     * Generates lockfile.
     *
     * @return string Path of lockfile
     */
    private function _lockfile()
    {
        return sprintf('%s%s%s.lock', sys_get_temp_dir(), DIRECTORY_SEPARATOR, md5(get_class($this)));
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        // Is there a lockfile?
        if (is_resource($this->_LockHandle)) {

            // Try to unlock file
            flock($this->_LockHandle, LOCK_UN);

            // Close file
            fclose($this->_LockHandle);

            // Try to delete file
            sleep(1);

            unlink($this->_lockfile());
        }
    }

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
     * @since 1.0.0
     * @see \BLW\Type\Cron\IHandler::exitMutex()
     *
     * @return bool <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function enterMutex()
    {
        // Name of lockfile
        $Lockfile = $this->_lockfile();

        // Does lockfile exist? Yes, return talse
        if (is_file($Lockfile) || is_dir($Lockfile))
            return false;

        // Try to open file
        $this->_LockHandle = fopen($Lockfile, 'x');

        if ($this->_LockHandle ? flock($this->_LockHandle, LOCK_EX | LOCK_NB, $Blocked) : false) {

            // Check lock status
            if (! $Blocked)
                return true;
        }

        // Error
        return false;
    }

    /**
     * Exits a mutually exclusive execution enviroment.
     *
     * @api BLW
     * @since 1.0.0
     * @see \BLW\Type\Cron\IHandler::enterMutex()
     *
     * @return bool <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function exitMutex()
    {
        // Does lockfile exist? No return false.
        if (! is_resource($this->_LockHandle))
            return false;

        // Try to unlock file
        if (flock($this->_LockHandle, LOCK_UN)) {

            // Close file
            fclose($this->_LockHandle);

            // Try to delete file
            sleep(1);

            return unlink($this->_lockfile());
        }

        // Error
        return false;
    }

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
     *            Expected time of execution. Pass <code>null</code> for current time.
     * @param int $flags
     *            Flags used to run commands.
     * @return mixed The result of <code>IJob::run()</code>
     */
    abstract public function run(IInput $Input, IOutput $Output, DateTime $Now = null, $flags = ICommand::RUN_FLAGS);

#############################################################################################

}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
