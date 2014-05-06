<?php
/**
 * Generic.php | Apr 8, 2014
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
namespace BLW\Model\Cron\Handler;

use DateTime;

use BLW\Type\IMediator;
use BLW\Type\Command\IInput;
use BLW\Type\Command\IOutput;
use BLW\Type\Command\ICommand;
use BLW\Type\Cron\IJob;

use BLW\Model\Config\Generic as GenericConfig;
use BLW\Model\Command\Callback as CallbackCommand;

use Psr\Log\LoggerInterface;


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
 * Generic cron handler
 *
 * @package BLW\Cron
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class Generic extends \BLW\Type\Cron\AHandler
{

    /**
     * Constructor
     *
     * @param \BLW\Type\IMediator $Mediator
     *            Cron mediator.
     * @param \Psr\Log\LoggerInterface $Logger
     *            Cron logger.
     * @param bool $isThreadCompatible
     *            Whether multiple instances of cron should run at the same time.
     */
    public function __construct(IMediator $Mediator, LoggerInterface $Logger, $isThreadCompatible = false)
    {
        // Properties
        $this->__isThreadCompatible = $isThreadCompatible;

        // Mediator
        $this->setMediator($Mediator);

        // Logger
        $this->setLogger($Logger);
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
     * <p>If a job takes too long to run that it expires before it
     * finishes executing it will be scheduled endlessly preventing
     * other jobs from running.</p>
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
    public function run(IInput $Input, IOutput $Output, DateTime $Now = null, $flags = ICommand::RUN_FLAGS)
    {
        // Now
        $Now  = $Now ?: new DateTime();
        $Next = new DateTime('next year');

        // Is cron not thread safe?
        if (! $this->_isThreadCompatible) {

            // Enter mutex
            if (! $this->enterMutex()) {

                // Cron already running
                $Command = new CallbackCommand(function ($Input, $Output)
                {
                    // Output notice
                    $Output->write("Cron already running. Exiting.\r\n");

                    // Done
                    return 0;

                }, new GenericConfig(array(
                    'Timeout' => 10
                )));

                return $Command->run($Input, $Output, $flags);
            }
        }

        // Search for an active job
        foreach ($this as $Job)
            if ($Job instanceof IJob) {

                // Is job due to run
                if ($Job->isExpired($Now)) {

                    // Set mediator of job
                    if ($this->_Mediator instanceof IMediator)
                        $Job->setMediator($this->_Mediator);

                    else
                        $Job->clearMediator();

                    // Run the job
                    $return = $Job->run($Input, $Output, $flags);

                    // Clear mediator of job
                    $Job->clearMediator();

                    // Exit mutex
                    if (! $this->_isThreadCompatible)
                        $this->exitMutex();

                        // Done
                    return $return;
                }

                // Save closest next run
                if ($Next > $Job->NextRun)
                    $Next = $Job->NextRun;
            }

            // No jobs run? Waiting command.
        $Command = new CallbackCommand(function ($Input, $Output) use($Next, $Now)
        {

            // Calculate wait period
            $Period = $Next->diff($Now, true)->format('%D days, %H hours %I minutes and %S seconds');

            // Output notice
            $Output->write(sprintf("No jobs. Waiting %s.\r\n", $Period));

            // Done
            return 0;
        }, new GenericConfig(array(
            'Timeout' => 10
        )));

        $return = $Command->run($Input, $Output, $flags);

        // Exit mutex
        if (! $this->_isThreadCompatible)
            $this->exitMutex();

        // Done
        return $return;
    }
}

return true;
