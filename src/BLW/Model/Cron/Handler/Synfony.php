<?php
/**
 * Symfony.php | Jan 07, 2014
 *
 * Copyright (c) 2013-2018 mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 *
 * @filesource
 * @copyright mAsT3RpEE's Zone
 * @license MIT
 */

/**
 *	@package BLW\Cron
 *	@version 1.0.0
 *	@author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model\Cron\Handler; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

use BLW;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Symfony version of a cron handler.
 * @package BLW\Cron
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class Symfony extends \BLW\Type\Iterator
{
    /**
     * @var array $DefaultOptions Default options used by class if not set in constructor.
     * @api BLW
     * @since 0.1.0
     * @see \BLW\Type\Object::___construct() __construct()
     */
    public static $DefaultOptions = array(
        'Logger' => NULL
    );

    /**
     * Determines if value is a valid value for the iterator.
     * @param mixed $value Value to test.
     * @return bool Returns <code>TRUE</code> if valid <code>FALSE</code> otherwise.
     */
    public function ValidateValue($value) {
        return $value instanceof \BLW\Interfaces\CronJob;
    }

    /**
     * Hook that is called when a new instance is created.
     * @return \BLW\Interfaces\Iterator $this
     */
    public static function doCreate()
    {
        // Call parent
        $self = parent::doCreate();

        // Next run
        if (!isset($self->NextRun)) {
            $self->NextRun = NULL;
        }

        // Logger
        if (!$self->Options->Logger instanceof \BLW\Interfaces\Logger) {
            $self->Options->Logger = BLW::Logger('cron');
        }

        return $self;
    }

    /**
     * Hook that is called when a child is added.
     * @return \BLW\Interfaces\Iterator $this
     */
    public function doAdd()
    {
        $this->Schedule($this[$this->_Current]);

        parent::doAdd();

        return $this;
    }

    /**
     * Hook that is called when a child is changed.
     * @return \BLW\Interfaces\Object $this
     */
    public function doUpdate()
    {
        $this->NextRun = new \DateTime('+1 year');

        for ($i = count($this) - 1; $i > 0; $i--) if (($Date = $this[$i]->GetNextRun()) < $this->NextRun) {
            $this->NextRun = $Date;
        }

        parent::doUpdate();

        return $this;
    }

    /**
     * Hook that is called when a child is deleted.
     * @return \BLW\Interfaces\Object $this
     */
    public function doDelete()
    {
        $this->NextRun = new \DateTime('+1 year');

        for ($i = count($this) - 1; $i > 0; $i--) if (($Date = $this[$i]->GetNextRun()) < $this->NextRun) {
            $this->NextRun = $Date;
        }

        parent::doDelete();

        return $this;
    }

    /**
     * Schedule a job to run at a specific time / interval.
     * @param \BLW\Interfaces\CronJob $Job Job to add to schedule.
     * @return void
     */
    private function Schedule(\BLW\Interfaces\CronJob $Job)
    {
        // Modify next run
        if (($Date = $Job->GetNextRun()) < self::NextRun()) {
            $this->Options->Logger->info('Scheduling next run at: '.$Date->format(\DateTime::W3C));
            $this->NextRun = $Date;
        }
    }

    /**
     * Calculates time of next cron job.
     * @return \DateTime Time of next run.
     */
    final public function NextRun()
    {
        if (!$this->NextRun instanceof \DateTime) {
            $this->NextRun = new \DateTime('+1 year');

            for ($i = count($this) - 1; $i > 0; $i--) if (($Date = $this[$i]) < $this->NextRun) {
                $this->NextRun = $Date;
            }
        }

        return $this->NextRun;
    }

    /**
     * Decides whether a cron job is scheduled to run or not.
     * @return bool Returns tru if there is a cron job to run or not.
     */
    final public function isTriggered()
    {
        return self::NextRun() < new \DateTime('+30 sec');
    }

    /**
     * Schedules cron jobs to execute on script exit;
     * @return \BLW\Interfaces\Iterator $this
     */
    public function Run()
    {
        $this->Options->Logger->info('Scheduling cron');

        if (self::isTriggered()) {
            // Reset Next Run
            $this->NextRun = NULL;

            // Execute cron jobs
            foreach ($this as $Job) if ($Job->isTriggered()) {

                $this->Options->Logger->info('Scheduling job: '. $Job->GetID());

                register_shutdown_function(array($this, 'Execute'), $Job);

                /* NOTE:
                 * Run only one cron job at a time.
                 * Sorry but BLW must run on slow / shared / free servers.
                 */
                break;
            }
        }

        else {
            // Show waiting command
            register_shutdown_function(array($this, 'Wait'), date_diff(new \DateTime(), $this->NextRun));
        }

        return $this;
    }

    /**
     * Runs cron and shows Waiting
     * @param \DateInterval $Period
     * @return int Status code of job.
     */
    private function Wait(\DateInterval $Period)
    {
        if (($Application = BLW::GetView()) instanceof \BLW\Interfaces\Application) {

            $this->Options->Logger->debug('Waiting for next cron job: '.$Period->format('%H:%I:%S'));

            $Command = \BLW\Model\Cron\WaitCommand::GetInstance(array('Period' => $Period));

            $Application->push($Command);
            $Application->configure(array(
                 'Input' => new ArrayInput(array('command' => $Command->GetID()))
            ));

            return $Application->start();
        }

        else {
            trigger_error(sprintf('%s:Wait(): current view is not an application.', get_class($this)), E_USER_WARNING);
            return 0;
        }
    }

    /**
     * Execute a cron job.
     * @param \BLW\Interfaces\CronJob $Job Job to execute.
     * @return int Status code of job.
     */
    private function Execute(\BLW\Interfaces\CronJob &$Job)
    {
        if (($Application = BLW::GetView()) instanceof \BLW\Interfaces\Application) {

            $this->Options->Logger->info('Executing cron job: '.$Job->GetID());

            // Run Cron job
            $Application->push($Job);
            $Application->configure(array(
                 'Input' => new ArrayInput(array('command' => $Job->GetID()))
            ));

            $Status   = $Application->start();
            $Interval = $Job->GetInterval();

            if ($Status == 0) {
                // Remove obsolete jobs after run
                if ($Interval == 0) {
                    for ($i = count($this)-1; $i >= 0; $i--) if ($this[$i] == $Job) {
                        unset($this[$i]);
                        $i = count($this);
                    }
                }

                // Reschedule jobs at new time.
                else {
                    $NewDate = $Job->NextRun()->add(new \DateInterval(sprintf('PT%dH', $Interval)));

                    $Job->SetNextRun($NewDate, $Interval);
                }
            }

            return $Status;
        }

        else {
            trigger_error(sprintf('%s:Execute(): current view is not an application.', get_class($this)), E_USER_WARNING);
            return 0;
        }
    }
}