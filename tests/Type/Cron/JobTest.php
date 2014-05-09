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
namespace BLW\Type\Cron;

use ReflectionProperty;
use ReflectionMethod;
use DateTime;
use DateInterval;

use Psr\Log\NullLogger;
use BLW\Type\IDataMapper;

class MockComponent
{
    public $foo = 1;

    public function run(){}
    public function setMediator(){}
    public function clearMediator(){}
    public function __set($name, $value) {throw new \Exception('undefined');}
}

/**
 * Tests BLW Cron Job class.
 * @package BLW\Cron
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
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

        $this->Job->setMediator($this->getMockForAbstractClass('\\BLW\\Type\\IMediator'));

        $Property = new ReflectionProperty($this->Job, '_LastRun');

        $Property->setAccessible(true);
        $Property->setValue($this->Job, new DateTime('yesterday'));

        $Property = new ReflectionProperty($this->Job, '_NextRun');

        $Property->setAccessible(true);
        $Property->setValue($this->Job, new DateTime('today'));

        $Property = new ReflectionProperty($this->Job, '_Interval');

        $Property->setAccessible(true);
        $Property->setValue($this->Job, new DateInterval('PT24H'));

        unset($Property);
    }

    protected function tearDown()
    {
        $this->Job = NULL;
    }

    /**
     * @covers ::getID
     */
    public function test_getID()
    {
        $this->assertInternalType('string', $this->Job->getID(), 'IJob::getID() Returned an invalid value');
        $this->assertNotEmpty($this->Job->getID(), 'IJob::getID() Returned an invalid value');
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

        # Valid arguments
        $this->assertEquals(IDataMapper::UPDATED, $this->Job->setLogger($Expected), 'IJob::setLogger() Should return IDataMapper::UPDATED');
        $this->assertAttributeSame($Expected, 'logger', $this->Job, 'IJob::setLogger() Failed to update $logger');

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

        # Valid arguments
        $this->assertEquals(IDataMapper::UPDATED, $this->Job->setInterval($Expected), 'IJob::setInterval() Should return IDataMapper::UPDATED');
        $this->assertAttributeSame($Expected, '_Interval', $this->Job, 'IJob::setInterval() Failed to update $_Interval');

        # Invalid arguments
        $this->assertEquals(IDataMapper::INVALID, $this->Job->setInterval(NULL), 'IJob::setInterval() Should return IDataMapper::INVALID');
        $this->assertEquals(IDataMapper::INVALID, $this->Job->setInterval(new DateInterval('PT40S')), 'IJob::setInterval() Should return IDataMapper::INVALID');

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
        $Expected = new DateTime;

        # Valid arguments
        $this->assertEquals(IDataMapper::UPDATED, $this->Job->schedule($Expected), 'IJob::schedule() Should return IDataMapper::UPDATED');
        $this->assertAttributeSame($Expected, '_NextRun', $this->Job, 'IJob::schedule() Failed to update $_NextRun');

        # Invalid arguments
        $this->assertEquals(IDataMapper::INVALID, $this->Job->schedule(NULL), 'IJob::setInterval() Should return IDataMapper::INVALID');

        unset($Property);
    }

    /**
     * @covers ::run
     */
    public function test_run()
    {
        $Original = $this->readAttribute($this->Job, '_LastRun');
        $Expected = new DateTime;

        $this->assertInstanceOf('DateTime', $Original, 'IJob::$_LastRun should be an instance of DateTime');

        # Valid arguments
        $this->Job->run($this->getMockForAbstractClass('\\BLW\\Type\\Command\\IInput'), $this->getMockForAbstractClass('\\BLW\\Type\\Command\\IOutput'));
        $this->assertAttributeNotEquals($Original, '_LastRun', $this->Job, 'IJob::run() Failed to update $_LastRun');
        $this->assertEquals($Expected->format('D, d M y H:i'), $this->readAttribute($this->Job, '_LastRun')->format('D, d M y H:i'), 'IJob::run() Failed to correctly update $_LastRun');

        # No mediator
        $Expected = new DateTime;

        $this->Job->clearMediator();
        $this->Job->run($this->getMockForAbstractClass('\\BLW\\Type\\Command\\IInput'), $this->getMockForAbstractClass('\\BLW\\Type\\Command\\IOutput'));
        $this->assertEquals($Expected->format('D, d M y H:i'), $this->readAttribute($this->Job, '_LastRun')->format('D, d M y H:i'), 'IJob::run() Failed to correctly update $_LastRun');

        # Invalid arguments

        //.....
    }

    /**
     * @covers ::__get
     */
    public function test_get()
    {
        # Status
        $this->assertAttributeSame($this->Job->Status, '_Status', $this->Job, 'IJob::$Status should equal IJob::_Status');

        # Serializer
        $this->assertSame($this->Job->getSerializer(), $this->Job->Serializer, 'IJob::$Serializer should equal IJob::getSerializer()');

        # Parent
        $this->assertSame($this->Job->getParent(), $this->Job->Parent, 'IJob::$Parent should equal IJob::getParent()');

        # ID
        $this->assertSame($this->Job->getID(), $this->Job->ID, 'IJob::$ID should equal IJob::getID()');

        # Mediator
        $this->assertSame($this->Job->getMediator(), $this->Job->Mediator, 'IJob::$Mediator should equal IJob::getMediator()');

        # MediatorID
        $this->assertSame($this->Job->getMediatorID(), $this->Job->MediatorID, 'IJob::$MediatorID should equal IJob::getMediatorID()');

        # Interval
        $this->assertSame($this->Job->getInterval(), $this->Job->Interval, 'IJob::$Interval should equal IJob::getInterval()');

        # NextRun
        $Property = new ReflectionProperty($this->Job, '_NextRun');
        $Expected = new DateTime;

        $Property->setAccessible(true);
        $Property->setValue($this->Job, $Expected);

        $this->assertSame($Expected, $this->Job->NextRun, 'IJob::$NextRun should equal IJob::$_NextRun');

        # LastRun
        $Property = new ReflectionProperty($this->Job, '_LastRun');
        $Expected = new DateTime;

        $Property->setAccessible(true);
        $Property->setValue($this->Job, $Expected);

        $this->assertSame($Expected, $this->Job->LastRun, 'IJob::$LastRun should equal IJob::$_LastRun');

        # Component property
        $this->assertSame(1, $this->Job->foo, 'IJob::$foo should equal IJob::$_Component->foo');

        # Undefined
        try {
            $this->Job->undefined;
            $this->fail('Failed to generate notice with undefined property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {}

        $this->assertNull(@$this->Job->undefined, 'IJob::$undefined should be NULL');
    }


    /**
     * @covers ::__isset
     */
    public function test_isset()
    {
        # Status
       $this->assertTrue(isset($this->Job->Serializer), 'IJob::$Status should exist');

        # Serializer
        $this->assertTrue(isset($this->Job->Serializer), 'IJob::$Serializer should exist');

        # Parent
        $this->assertFalse(isset($this->Job->Parent), 'IJob::$Parent should not exist');

        # ID
        $this->assertTrue(isset($this->Job->ID), 'IJob::$ID should exist');

        # Mediator
        $this->assertTrue(isset($this->Job->Mediator), 'IJob::$Mediator should exist');

        # MediatorID
        $this->assertTrue(isset($this->Job->MediatorID), 'IJob::$MediatorID should exist');

        # Interval
        $this->assertTrue(isset($this->Job->Interval), 'IJob::$Interval should exist');

        # LastRun
        $this->assertTrue(isset($this->Job->LastRun), 'IJob::$Lastrun should exist');

        # NextRun
        $this->assertTrue(isset($this->Job->NextRun), 'IJob::$Next should exist');

        # Component property
        $this->assertTrue(isset($this->Job->foo), 'IJob::$foo should exist');

        # Undefined
        $this->assertFalse(isset($this->Job->undefined), 'IJob::$undefined should not exist');
    }

    /**
     * @depends test_get
     * @covers ::__set
     */
    public function test_set()
    {
        # Status
        try {
            $this->Job->Status = 0;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Job->Status = 0;

        # Serializer
        try {
            $this->Job->Serializer = 0;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Job->Serializer = 0;

        # Parent
        $Parent            = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Job->Parent = $Parent;

        $this->assertSame($Parent, $this->Job->Parent, 'IJob::$Parent should equal IJob::getParent');

        try {
            $this->Job->Parent = null;
            $this->fail('Failed to generate notice with invalid value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Job->Parent = null;

        try {
            $this->Job->Parent = $Parent;
            $this->fail('Failed to generate notice with oneshot value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # ID
        try {
            $this->Job->ID = 'foo';
            $this->fail('Failed to generate notice with invalid value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Job->ID = 'foo';

        # Mediator
        $Mediator            = $this->getMockForAbstractClass('\\BLW\\Type\\IMediator');
        $this->Job->Mediator = $Mediator;

        $this->assertSame($Mediator, $this->Job->getMediator(), 'IJob::$Mediator failed to call IJob::setMediator()');

        try {
            $this->Job->Mediator = null;
            $this->fail('Failed to generate notice with invalid value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Job->Mediator = null;

        # MediatorID
        try {
            $this->Job->MediatorID = 'foo';
            $this->fail('Failed to generate notice with readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Job->MediatorID = 'foo';

        # Interval
        $Expected            = new DateInterval('P2D');
        $this->Job->Interval = $Expected;

        $this->assertSame($Expected, $this->Job->getInterval(), 'IJob::$Interval failed to update $_Interval');

        try {
            $this->Job->Interval = null;
            $this->fail('Failed to generate notice with readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        try {
            $this->Job->Interval = new DateInterval('PT30S');
            $this->fail('Failed to generate notice with readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Job->Interval = null;

        # LastRun
        try {
            $this->Job->LastRun = null;
            $this->fail('Failed to generate notice with readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Job->LastRun = null;

        # NextRun
        $Expected           = new DateTime;
        $this->Job->NextRun = $Expected;

        $this->assertSame($Expected, $this->Job->NextRun, 'IJob::$NextRun failed to update $_NextRun');

        try {
            $this->Job->NextRun = null;
            $this->fail('Failed to generate notice with readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Job->NextRun = null;

        # Component property
        $this->Job->foo = 100;

        $this->assertSame(100, $this->Job->foo, 'IJob::$foo failed to update IJob::$_Command->foo');

        # Undefined
        try {
            $this->Job->undefined = '';
            $this->fail('Failed to generate notice with undefined property');
        }

        catch (\PHPUnit_Framework_Error $e) {
            $this->assertContains('non-existant property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Job->undefined = '';
    }

    /**
     * @depends test_get
     * @depends test_set
     * @covers ::__unset
     */
    public function test_unset()
    {
        # Parent
        $this->Job->Parent = $this->getMockForAbstractClass('\\BLW\Type\IObject');

        unset($this->Job->Parent);

        $this->assertNull($this->Job->Parent, 'unset(IJob::$Parent) Did not reset $_Parent');

        # Status
        unset($this->Job->Status);

        $this->assertSame(0, $this->Job->Status, 'unset(IJob::$Status) Did not reset $_Status');

        # Mediator
        $this->Job->Mediator = $this->getMockForAbstractClass('\\BLW\\Type\\IMediator');

        unset($this->Job->Mediator);

        $this->assertNull($this->Job->Mediator, 'unset(IJob::$Mediator Did not reset $_Mediator');

        # Undefined
        unset($this->Job->undefined);
    }
}