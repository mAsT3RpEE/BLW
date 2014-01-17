<?php
/**
 * CronJob.php | Jan 07, 2014
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
namespace BLW\Interfaces;

/**
 * Core Adapter pattern class.
 *
 * <h4>Notice:</h4>
 *
 * <p>All Adaptor objects must either extend this class or
 * implement the <code>\BLW\Interfaces\Adaptor</code> interface.
 *
 * <hr>
 * @package BLW\Core
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 */
interface CronJob extends \BLW\Interfaces\ApplicationCommand
{
    /**
     * Sets the next run and interval of cron job.
     * @param \DateTime $NewDate
     * @param int $Interval repeat interval in hours
     * @return \BLW\Interfaces\CronJob $this
     */
    public function Schedule(\DateTime $NewDate, $Interval = 0);

    /**
     * Calculates time to run cron job next.
     * @return \DateTime Time of next run.
     */
    public function GetNextRun();

    /**
     * Returns current $_Interval.
     * @return int Interval.
     */
    public function GetInterval();

    /**
     * Decides whether the cron job is scheduled to run or not.
     * @return bool Returns <conde>TRUE</conde> if cron job should run.
     */
    public function isTriggered();
}

return true;