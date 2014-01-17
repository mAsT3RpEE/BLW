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
namespace BLW\Model\Cron\Job; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

/**
 * Symfony version of a cron job.
 * @package BLW\Cron
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
abstract class Symfony extends \BLW\Type\ApplicationCommand\Symfony implements \BLW\Interfaces\CronJob
{
    /**
     * @var \DateTime $_NextRun Time of next Run
     */
    protected $_NextRun  = NULL;

    /**
     * @var int $_Interval Interval in hours from next run to run after that.
     */
    protected $_Interval = 0;

    /**
     * Configures the command.
     * @param array $Options Congigurations options.
     * @return \BLW\Interfaces\CronJob $this.
     */
    final public function configure(array $Options = array())
    {
        return $this
            ->setName('run')
            ->setDescription('Runs a cron job.')
        ;
    }

    /**
     * Sets the next run of cron job.
     * @param \DateTime $NewDate
     * @param int $interval repeat interval in hours
     * @return \BLW\Interfaces\CronJob $this
     */
    final public function Schedule(\DateTime $NewDate, $Interval = 0)
    {
        $this->_NextRun  = $NewDate;
        $this->_Interval = @intval($Interval);

        return $this;
    }

    /**
     * Calculates time to run cron job next.
     * @return \DateTime Time of next run.
     */
    final public function GetNextRun()
    {
        if ($this->_NextRun instanceof \DateTime) {
            return $this->_NextRun;
        }

        else {
            trigger_error(sprintf('%1$s::GetNextRun(): called before %1$s::Schedule().', get_class($this)), E_USER_WARNING);

            return new \DateTime('+10 years');
        }
    }

    /**
     * Returns current $_Interval.
     * @return int Interval.
     */
    final public function GetInterval()
    {
        return $this->_Interval;
    }

    /**
     * Decides whether the cron job is scheduled to run or not.
     * @return bool Returns <conde>TRUE</conde> if cron job should run.
     */
    final public function isTriggered()
    {
        if ($this->_NextRun instanceof \DateTime) {
            return $this->_NextRun < new \DateTime('+30 second');
        }

        else {
            trigger_error(sprintf('%1$s::isTriggered(): called before %1$s::Schedule().', get_class($this)), E_USER_WARNING);

            return false;
        }
    }
}

return true;