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
namespace BLW\Tests\Model\Cron\Handler;

use BLW;
use CronJob;
use BLW\Model\Cron\Handler\Symfony as CronHandler;
use BLW\Frontend\Console\Symfony;

require_once __DIR__ . '/../../../Config/Cron/Job/Symfony.php';

/**
 * Tests BLW Library Symfony ApplicationCronJob type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class SymfonyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\Cron\Handler\Symfony
     */
    private $Handler = NULL;

    /**
     * @var \BLW\Model\Cron\Job\Symfony
     */
    private $CronJob = NULL;

    public function setUp()
    {
        $this->Handler     = CronHandler::GetInstance();
        $this->CronJob     = CronJob::GetInstance();
    }

    public function tearDown()
    {
        $this->Handler       = NULL;
        $this->CronJob       = NULL;
    }

    public function test_add()
    {
        $this->assertFalse($this->Handler->isTriggered());

        $this->CronJob->Schedule(new \DateTime(), 1);

        $this->Handler[] = $this->CronJob;
        $this->assertTrue($this->Handler->isTriggered());
        $this->Handler->push($this->CronJob);
        $this->assertTrue($this->Handler->isTriggered());
        $this->Handler->unshift($this->CronJob);
        $this->assertTrue($this->Handler->isTriggered());

        unset($this->Handler[0]);
        unset($this->Handler[0]);
        unset($this->Handler[0]);

        $this->assertFalse($this->Handler->isTriggered());

        $this->CronJob->Schedule(new \DateTime('tomorrow'), 1);

        $this->Handler[] = $this->CronJob;

        $this->assertFalse($this->Handler->isTriggered());
        $this->assertEquals(new \DateTime('tomorrow'), $this->Handler->NextRun());
    }

    /**
     * @depends test_add
     */
    public function test_serialize()
    {
        $Serialized = unserialize(serialize($this->Handler));
        $Serialized->Options->Logger = $this->Handler->Options->Logger;

        $this->assertEquals($this->Handler, $Serialized);
    }

    public function test_Wait()
    {
        $Command     = \BLW\Model\Cron\WaitCommand::GetInstance(array('Period' => date_diff(new \DateTime(), $this->Handler->NextRun())));
        $Application = \BLW\Frontend\Console\Symfony::GetInstance();
        $Application->push($Command);
        $Tester      = new \Symfony\Component\Console\Tester\CommandTester($Command);

        $Tester->execute(
             array('command'    => $Command->GetID())
            ,array('verbosity' => 3)
        );

        $Output = $Tester->getDisplay();

        $this->assertRegExp('/No cron jobs: waiting \d+ day.+, \d+ hour.+, \d+ min.+, \d+ sec.+/', $Output);
    }
}