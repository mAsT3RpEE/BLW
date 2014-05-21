<?php
/**
 * CommandTest.php | Mar 31, 2014
 *
 * @filesource
 * @license MIT
 * @copyright Copyright (c) 2013-2018, mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * @package BLW\Command
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type\Command;



use BLW\Model\Config\Generic as GenericConfig;

use BLW\Model\Command\Input\Generic as GenericInput;
use BLW\Model\Command\Output\Generic as GenericOutput;
use BLW\Model\Stream\Handle as HandleStream;
use BLW\Model\Mediator\Symfony as SymfonyMediator;
use BLW\Model\InvalidArgumentException;

/**
 * Test for base Command class
 * @package BLW\Command
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\Command\ACommand
 */
class CommandTest extends \PHPUnit_Framework_TestCase
{
    const INPUT  = "data:text/plain,line 1\nline 2\nline 3\nline 4";
    const OUTPUT = "php://memory";

    /**
     * @var \BLW\Type\Command\IInput
     */
    protected $Input = NULL;

    /**
     * @var \BLW\Type\Command\IOutput
     */
    protected $Output = NULL;

    /**
     * @var \BLW\Type\IMediator
     */
    protected $Mediator = NULL;

    /**
     * @var array
     */
    protected $Arguments = array();

    /**
     * @var \Closure
     */
    protected $Action = NULL;

    /**
     * @var \BLW\Type\IConfig
     */
    protected $Config = NULL;

    /**
     * @var \BLW\Type\Command\ACommand
     */
    protected $Command = NULL;

    public function mock_doRun()
    {
        $this->Arguments  = func_get_args();

        $this->Input->readline(1024);
        $this->Output->write('foo', IOutput::STDOUT);
        $this->Output->write('foo', IOutput::STDERR);

        return -1;
    }

    protected function setUp()
    {
        $this->Input    = new GenericInput(new HandleStream(fopen(self::INPUT, 'r')));
        $this->Output   = new GenericOutput(new HandleStream(fopen(self::OUTPUT, 'r')), new HandleStream(fopen(self::OUTPUT, 'r')));
        $this->Mediator = new SymfonyMediator;
        $this->Action   = array($this, 'mock_doRun');
        $this->Config   = new GenericConfig(array('Timeout' => 10));
        $this->Command  = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\ACommand', array($this->Action, $this->Config, $this->Mediator, 'MockCommand'));

        # Set up doRun
        $this->Command
            ->expects($this->any())
            ->method('doRun')
            ->will($this->returnCallback($this->Action));
    }

    protected function tearDown()
    {
        $this->Command  = NULL;
        $this->Input    = NULL;
        $this->Output   = NULL;
        $this->Mediator = NULL;
        $this->Action   = NULL;
        $this->Config   = NULL;
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        $this->Command = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\ACommand', array($this->Action, $this->Config, $this->Mediator, 'MockCommand'));

        # Check properties
        $this->assertAttributeSame($this->Action, '_Command', $this->Command, 'ICommand::__construct() Failed to set $_Command');
        $this->assertAttributeSame($this->Config, '_Config', $this->Command, 'ICommand::__construct() Failed to set $_Config');
        $this->assertSame('MockCommand', $this->Command->getID(), 'ICommand::__construct() Failed to set $_ID');
        $this->assertAttributeSame($this->Mediator, '_Mediator', $this->Command, 'ICommand::__construct() Failed to set $_Mediator');

        # No ID
        $this->Command  = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\ACommand', array($this->Action, $this->Config, $this->Mediator));

        $this->assertRegExp('!BLW_.*!', $this->Command->getID(), 'ICommand::__construct() Failed to set $_ID');

        # Invalid ID
        try {
            $this->getMockForAbstractClass('\\BLW\\Type\\Command\\ACommand', array($this->Action, $this->Config, $this->Mediator, array()));
            $this->fail('Failed to generate error with invalid $ID');
        } catch (\PHPUnit_Framework_Error_Notice $e) {}

        # Invalid config
        try {
            $this->getMockForAbstractClass('\\BLW\\Type\\Command\\ACommand', array($this->Action, new GenericConfig(), $this->Mediator));

        } catch (InvalidArgumentException $e) {}

    }

    /**
     * @depends test_construct
     * @covers ::doNotify
     */
    public function test_doNotify()
    {
        # Set up function.
        $Called    = 0;
        $Arguments = array();

        $this->Command->_on('Notify', function () use (&$Called, &$Arguments) {
            $Called++;
            $Arguments = func_get_args();
        });

        # Call notify
        $this->Command->doNotify(-1, array('foo' => 1));

        $this->assertEquals(1, $Called, 'ICommand::doNotify() Failed to trigger callback');
        $this->assertNotEmpty($Arguments, 'ICommand::doNotify() Caused an exceptional behaviour');
        $this->assertInstanceOf('\\BLW\\Model\\Command\\Event', $Arguments[0], 'ICommand::doNotify() Created and invalid event');
        $this->assertEquals(1, $Arguments[0]->foo, 'ICommand::doNotify() Created and invalid event');

        # No mediator
        $this->Command->clearMediator();
        $this->assertFalse($this->Command->doNotify(-1), 'ICommand::doNotify() Should return false');

        # Invalid arguments
        try {
            $this->Command->doNotify(NULL);
            $this->fail('Failed to generate exception with invalid arguments');
        } catch (InvalidArgumentException $e) {}
    }

    /**
     * @depend test_doNotify
     * @covers ::onNotify
     */
    public function test_onNotify()
    {
        $Called = 0;
        $Type   = 0;

        $this->Command->onNotify(function ($Event) use (&$Called, &$Type) {$Called++; $Type = $Event->Type;});
        $this->Command->doNotify(-1);

        $this->assertEquals(1, $Called, 'ICommand::onNotify() Failed to register callback');
        $this->assertEquals(-1, $Type, 'ICommand::onNotify() Failed to register callback');

        # No mediator
        $this->Command->clearMediator();
        $this->assertFalse($this->Command->onNotify(function () {}), 'ICommand::doNotify() Should return false');

        # Invalid arguments
        try {
            $this->Command->onNotify(NULL);
            $this->fail('Failed to generate exception with invalid arguments');
        } catch (InvalidArgumentException $e) {}
    }

    /**
     * @depends test_doNotify
     * @covers ::run
     */
    public function test_run()
    {
        $this->assertEquals(-1, $this->Command->run($this->Input, $this->Output), 'ICommand::run() failed to call doRun()');
    }

    /**
     * @depends test_run
     * @covers ::onInput
     */
    public function test_onInput()
    {
        $Called = 0;

        $this->Command->onInput(function () use (&$Called) {$Called++;});
        $this->Command->run($this->Input, $this->Output);

        $this->assertEquals(1, $Called, 'ICommand::onInput() Failed to register callback');

        # No mediator
        $this->Command->clearMediator();
        $this->assertFalse($this->Command->onInput(function () {}), 'ICommand::doNotify() Should return false');

        # Invalid arguments
        try {
            $this->Command->onInput(NULL);
            $this->fail('Failed to generate exception with invalid arguments');
        } catch (InvalidArgumentException $e) {}
    }


    /**
     * @depends test_run
     * @covers ::onOutput
     */
    public function test_onOutput()
    {
        $Called = 0;

        $this->Command->onOutput(function () use (&$Called) {$Called++;});
        $this->Command->run($this->Input, $this->Output);

        $this->assertEquals(1, $Called, 'ICommand::onInput() Failed to register callback');

        # No mediator
        $this->Command->clearMediator();
        $this->assertFalse($this->Command->onOutput(function () {}), 'ICommand::doNotify() Should return false');

        # Invalid arguments
        try {
            $this->Command->onOutput(NULL);
            $this->fail('Failed to generate exception with invalid arguments');
        } catch (InvalidArgumentException $e) {}
    }

    /**
     * @depends test_run
     * @covers ::onError
     */
    public function test_onError()
    {
        $Called = 0;

        $this->Command->onError(function () use (&$Called) {$Called++;});
        $this->Command->run($this->Input, $this->Output);

        $this->assertEquals(1, $Called, 'ICommand::onInput() Failed to register callback');

        # No mediator
        $this->Command->clearMediator();
        $this->assertFalse($this->Command->onError(function () {}), 'ICommand::doNotify() Should return false');

        # Invalid arguments
        try {
            $this->Command->onError(NULL);
            $this->fail('Failed to generate exception with invalid arguments');
        } catch (InvalidArgumentException $e) {}
    }

    /**
     * @depends test_construct
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertSame(sprintf('[Command:%s:MockCommand]', basename(get_class($this->Command))), @strval($this->Command), '(string) ICommand returned an invalid value');
    }

    /**
     * @depends test_construct
     * @covers ::__get
     */
    public function test_get()
    {
        # Status
        $this->assertSame($this->readAttribute($this->Command, '_Status'), $this->Command->Status, 'ICommand::$Status should equal ICommand::_Status');

        # Serializer
        $this->assertSame($this->Command->getSerializer(), $this->Command->Serializer, 'ICommand::$Serializer should equal ICommand::getSerializer()');

        # Parent
        $this->assertSame($this->Command->getParent(), $this->Command->Parent, 'ICommand::$Parent should equal ICommand::getParent()');

        # ID
        $this->assertSame( $this->Command->getID(), $this->Command->ID, 'ICommand::$ID should equal ICommand::getID()');

        # Mediator
        $this->assertSame($this->Command->getMediator(), $this->Command->Mediator, 'ICommand::$Mediator should equal ICommand::getMediator()');

        # MediatorID
        $this->assertSame($this->Command->getMediatorID(), $this->Command->MediatorID, 'ICommand::$MediatorID should equal ICommand::getMediatorID()');

        # Command
        $this->assertSame($this->Action, $this->Command->Command, 'ICommand::$Command should equal ICommand::$_Command');

        # Config
        $this->assertSame($this->Config, $this->Command->Config, 'ICommand::$Config should equal ICommand::$_Config');

        # Default
        $this->assertInstanceOf('\\BLW\\Type\\IConfig', $this->Command->Default, 'ICommand::$Default should be an instance of IConfig');

        # Undefined
        try {
            $this->Command->undefined;
            $this->fail('Failed to generate notice with undefined property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {}

        $this->assertNull(@$this->Command->undefined, 'ICommand::$undefined should be NULL');
    }


    /**
     * @depends test_construct
     * @covers ::__isset
     */
    public function test_isset()
    {
        # Status
       $this->assertTrue(isset($this->Command->Serializer), 'ICommand::$Status should exist');

        # Serializer
        $this->assertTrue(isset($this->Command->Serializer), 'ICommand::$Serializer should exist');

        # Parent
        $this->assertFalse(isset($this->Command->Parent), 'ICommand::$Parent should not exist');

        # ID
        $this->assertTrue(isset($this->Command->ID), 'ICommand::$ID should exist');

        # Mediator
        $this->assertTrue(isset($this->Command->Mediator), 'ICommand::$Mediator should exist');

        # MediatorID
        $this->assertTrue(isset($this->Command->MediatorID), 'ICommand::$MediatorID should exist');

        # Command
        $this->assertTrue(isset($this->Command->Command), 'ICommand::$Command should exist');

        # Config
        $this->assertTrue(isset($this->Command->Config), 'ICommand::$Config should exist');

        # Default
        $this->assertTrue(isset($this->Command->Default), 'ICommand::$Default should exist');

        # Undefined
        $this->assertFalse(isset($this->Command->undefined), 'ICommand::$undefined should not exist');
    }

    /**
     * @depends test_get
     * @covers ::__set
     */
    public function test_set()
    {
        # Status
        try {
            $this->Command->Status = 0;
            $this->fail('Failed to generate notice on readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Command->Status = 0;

        # Serializer
        try {
            $this->Command->Serializer = 0;
            $this->fail('Failed to generate notice on readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Command->Serializer = 0;

        # Parent
        $Parent                = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Command->Parent = $Parent;

        $this->assertSame($Parent, $this->Command->Parent, 'ICommand::$Parent should equal ICommand::getParent()');

        try {
            $this->Command->Parent = null;
            $this->fail('Failed to generate notice with invalid value');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Command->Parent = null;

        try {
            $this->Command->Parent = $Parent;
            $this->fail('Failed to generate notice with oneshot value');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # ID
        $this->Command->ID = 'foo';

        $this->assertSame($this->Command->ID, 'foo', 'ICommand::$ID should equal `foo');

        try {
            $this->Command->ID = null;
            $this->fail('Failed to generate notice with invalid value');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Command->ID = null;

        # Mediator
        $Mediator                = $this->getMockForAbstractClass('\\BLW\\Type\\IMediator');
        $this->Command->Mediator = $Mediator;

        $this->assertSame($Mediator, $this->Command->getMediator(), 'ICommand::$Mediator failed to call ICommand::setMediator()');

        try {
            $this->Command->Mediator = null;
            $this->fail('Failed to generate notice with invalid value');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Command->Mediator = null;

        # MediatorID
        try {
            $this->Command->MediatorID = 'foo';
            $this->fail('Failed to generate notice with readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Command->MediatorID = 'foo';

        # Command
        try {
            $this->Command->Command = null;
            $this->fail('Failed to generate notice with readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Command->Command = null;

        # Config
        try {
            $this->Command->Config = null;
            $this->fail('Failed to generate notice with readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Command->Config = null;

        # Default
        try {
            $this->Command->Default = null;
            $this->fail('Failed to generate notice with readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Command->Default = null;

        # Undefined
        try {
            $this->Command->undefined = '';
            $this->fail('Failed to generate notice with undefined property');
        } catch (\PHPUnit_Framework_Error $e) {
            $this->assertContains('non-existant property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Command->undefined = '';
    }

    /**
     * @depends test_get
     * @depends test_set
     * @covers ::__unset
     */
    public function test_unset()
    {
        # Parent
        $this->Command->Parent = $this->getMockForAbstractClass('\\BLW\Type\IObject');

        unset($this->Command->Parent);

        $this->assertNull($this->Command->Parent, 'unset(ICommand::$Parent) Did not reset $_Parent');

        # Status
        unset($this->Command->Status);

        $this->assertSame(0, $this->Command->Status, 'unset(ICommand::$Status) Did not reset $_Status');

        # Mediator
        $this->Command->Mediator = $this->getMockForAbstractClass('\\BLW\\Type\\IMediator');

        unset($this->Command->Mediator);

        $this->assertNull($this->Command->Mediator, 'unset(ICommand::$Mediator Did not reset $_Mediator');

        # Undefined
        unset($this->Command->undefined);
    }
}
