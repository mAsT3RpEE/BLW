<?php
/**
 * JobTest.php | Apr 7, 2014
 *
 * @filesource
 * @license MIT
 * @copyright Copyright (c) 2013-2018, mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * @package BLW\Cron
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Tests\Type\Cron;

use ReflectionProperty;
use ReflectionMethod;
use DateTime;
use DateInterval;

use Psr\Log\NullLogger;
use BLW\Type\IDataMapper;


class MockComponent
{
    public function run(){}
    public function setMediator(){}
    public function clearMediator(){}
}

/**
 * Tests BLW Cron Job class.
 * @package BLW\Cron
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\Cron\AJob
 */
class JobTest  extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\Cron\AJob
     */
    protected $Job = NULL;

    protected function setUp()
    {
        $this->Job = $this->getMockForAbstractClass('\\BLW\\Type\\Cron\\AJob', array(new MockComponent));

        $Property = new ReflectionProperty($this->Job, '_LastRun');

        $Property->setAccessible(true);
        $Property->setValue($this->Job, new DateTime('yesterday'));

        $Property = new ReflectionProperty($this->Job, '_NextRun');

        $Property->setAccessible(true);
        $Property->setValue($this->Job, new DateTime('today'));

        unset($Property);
    }

    protected function tearDown()
    {
        $this->Job = NULL;
    }

    /**
     * @covers ::isExpired
     */
    public function test_isExpired()
    {
        $Now          = new DateTime;
        $Job          = $this->Job;
        $setNextRun   = function ($Date) use($Job) {
            $Property = new ReflectionProperty($Job, '_NextRun');

            $Property->setAccessible(true);
            $Property->setValue($Job, $Date);
        };

        # Expired time
        $this->assertTrue($this->Job->isExpired($Now), 'IJob::isExpired() Should return true');

        # Unexpired time
        $setNextRun(new DateTime('tomorrow'));
        $this->assertFalse($this->Job->isExpired($Now), 'IJob::isExpired() Should return false');

        # Current time
        $setNextRun($Now);
        $this->assertTrue($this->Job->isExpired($Now), 'IJob::isExpired() Should return false');
    }

    /**
     * @covers ::setLogger
     */
    public function test_setLogger()
    {
        $Expected = new NullLogger;
        $Property = new ReflectionProperty($this->Job, 'logger');

        $Property->setAccessible(true);

        # Valid arguments
        $this->assertEquals(IDataMapper::UPDATED, $this->Job->setLogger($Expected), 'IJob::setLogger() Should return IDataMapper::UPDATED');
        $this->assertSame($Expected, $Property->getValue($this->Job), 'IJob::setLogger() Failed to update $logger');

        # Invalid arguments
        try {
            $this->Job->setLogger(NULL);
            $this->fail('Failed to generate error with invalid arguments');
        }

        catch(\PHPUnit_Framework_Error $e) {}

        unset($Property);
    }

    /**
     * @covers ::setInterval
     */
    public function test_setInterval()
    {
        $Expected = new DateInterval('P1D');
        $Property = new ReflectionProperty($this->Job, '_Interval');

        $Property->setAccessible(true);

        # Valid arguments
        $this->assertEquals(IDataMapper::UPDATED, $this->Job->setInterval($Expected), 'IJob::setInterval() Should return IDataMapper::UPDATED');
        $this->assertSame($Expected, $Property->getValue($this->Job), 'IJob::setInterval() Failed to update $logger');

        # Invalid arguments
        $this->assertEquals(IDataMapper::INVALID, $this->Job->setInterval(NULL), 'IJob::setInterval() Should return IDataMapper::INVALID');

        unset($Property);
    }

    /**
     * @depends test_setInterval
     * @covers ::getInterval
     */
    public function test_getInterval()
    {
        $Expected = new DateInterval('P1D');
        $this->assertEquals(IDataMapper::UPDATED, $this->Job->setInterval($Expected), 'IJob::setInterval() Should return IDataMapper::UPDATED');
        $this->assertSame($Expected, $this->Job->getInterval(), 'IJob::getInterval() Should return $_Interval');
    }

    /**
     * @covers ::schedule
     */
    public function test_schedule()
    {
        $Expected = new DateInterval('P1D');
        $Property = new ReflectionProperty($this->Job, '_Interval');

        $Property->setAccessible(true);

        # Valid arguments
        $this->assertEquals(IDataMapper::UPDATED, $this->Job->setInterval($Expected), 'IJob::setInterval() Should return IDataMapper::UPDATED');
        $this->assertSame($Expected, $Property->getValue($this->Job), 'IJob::setInterval() Failed to update $logger');

        # Invalid arguments
        $this->assertEquals(IDataMapper::INVALID, $this->Job->setInterval(NULL), 'IJob::setInterval() Should return IDataMapper::INVALID');

        unset($Property);
    }

    /**
     * @covers ::run
     */
    public function test_run()
    {
        $Property = new ReflectionProperty($this->Job, '_LastRun');

        $Property->setAccessible(true);

        $Original = $Property->getValue($this->Job);
        $Expected = new DateTime;

        $this->assertInstanceOf('DateTime', $Original, 'IJob::$_LastRun should be an instance of DateTime');

        # Valid arguments
        $this->Job->run($this->getMockForAbstractClass('\\BLW\\Type\\Command\\IInput'), $this->getMockForAbstractClass('\\BLW\\Type\\Command\\IOutput'));
        $this->assertNotEquals($Original, $Property->getValue($this->Job), 'IJob::run() Failed to update $_LastRun');
        $this->assertEquals($Expected->format('D, d M y H:i'), $Property->getValue($this->Job)->format('D, d M y H:i'), 'IJob::run() Failed to correctly update $_LastRun');

        # Invalid arguments

        //.....
    }
}