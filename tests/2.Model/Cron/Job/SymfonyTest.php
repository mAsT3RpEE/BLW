<?php
/**
 * SymfonyTest.php | Dec 30, 2013
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
 * @package BLW\Core
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Tests\Model\Cron\Job;

use BLW;
use CronJob;
use BLW\Frontend\Console\Symfony as Application;
use Symfony\Component\Console\Tester\CommandTester as Tester;

require_once __DIR__ . '/../../../Config/Cron/Job/Symfony.php';

/**
 * Tests BLW Library Symfony ApplicationCronJob type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class SymfonyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Frontend\Console\Symfony
     */
    private $Application = NULL;

    /**
     * @var \BLW\Model\Cron\Job\Symfony
     */
    private $CronJob = NULL;

    /**
     * @var \Symfony\Component\Console\Tester\CronJobTester
     */
    private $Tester = NULL;

    public function setUp()
    {
        $this->Application = Application::GetInstance();
        $this->CronJob     = CronJob::GetInstance();
        $this->Application->push($this->CronJob);
        $this->Tester      = new Tester($this->CronJob);
    }

    public function tearDown()
    {
        $this->Application   = NULL;
        $this->CronJob       = NULL;
        $this->Tester        = NULL;
    }

    public function test_Run()
    {
        $this->Tester->execute(
             array('command'   => $this->CronJob->GetID())
            ,array('verbosity' => 3)
        );

        $Output = $this->Tester->getDisplay();

        $this->assertContains('10/10 [============================] 100%', $Output);
    }

    /**
     * @depends test_Run
     */
    public function test_serialize()
    {
        $Serialized = unserialize(serialize($this->CronJob));

        $this->assertEquals($this->CronJob, $Serialized);
    }

    /**
     * @depends test_Run
     */
    public function test_Schedule()
    {
        $Date = new \DateTime();

        $this->CronJob->Schedule($Date, 1);

        $this->assertTrue($this->CronJob->isTriggered());
        $this->assertEquals($Date, $this->CronJob->GetNextRun());
        $this->assertEquals(1, $this->CronJob->GetInterval());
    }
}