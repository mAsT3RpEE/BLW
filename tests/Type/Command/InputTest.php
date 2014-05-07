<?php
/**
 * InputTest.php | Mar 30, 2014
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
namespace BLW\Tests\Type\Command;

use ReflectionProperty;
use ReflectionMethod;
use BLW\Type\IDataMapper;
use BLW\Type\Command\IArgument;
use BLW\Type\Command\IOption;
use BLW\Model\InvalidArgumentException;
use BLW\Model\GenericContainer;
use BLW\Model\GenericFile;
use BLW\Model\Stream\File as FileStream;
use BLW\Model\Mediator\Symfony as SymfonyMediator;


/**
 * Test for command input base class
 * @package BLW\Command
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\Command\AInput
 */
class InputTest extends \PHPUnit_Framework_TestCase
{
    const FILE = "data:text/plain,line 1\r\nline 2\nline 3";

    /**
     * @var \BLW\Type\AMediator
     */
    protected $Mediator = NULL;

        /**
     * @var \BLW\Model\GenericContainer
     */
    protected $Arguments = NULL;

    /**
     * @var \BLW\Model\GenericContainer
     */
    protected $Options = NULL;

    /**
     * @var \BLW\Model\Stream\File
     */
    protected $Stream = NULL;

    /**
     * @var \BLW\Type\Command\AInput
     */
    protected $Input = NULL;

    protected function setUp()
    {
        $this->Mediator  = new SymfonyMediator;
        $this->Arguments = new GenericContainer(IArgument::CLASSNAME);
        $this->Options   = new GenericContainer(IOption::CLASSNAME);
        $this->Stream    = new FileStream(new GenericFile(self::FILE));
        $this->Input     = $this->getMockForAbstractClass('\BLW\Type\Command\AInput');

        $this->Input->setMediator($this->Mediator);
        $this->Input->setMediatorID('MockInput');

        $Property        = new ReflectionProperty($this->Input, '_Arguments');
        $Property->setAccessible(true);
        $Property->setValue($this->Input, $this->Arguments);

        $Property        = new ReflectionProperty($this->Input, '_Options');
        $Property->setAccessible(true);
        $Property->setValue($this->Input, $this->Options);

        $Property        = new ReflectionProperty($this->Input, '_InStream');
        $Property->setAccessible(true);
        $Property->setValue($this->Input, $this->Stream);
    }

    protected function tearDown()
    {
        $this->Input     = NULL;
        $this->Arguments = NULL;
        $this->Options   = NULL;
        $this->Stream    = NULL;
        $this->Mediator  = NULL;
    }

    /**
     * @covers ::setArgument
     */
    public function test_setArgument ()
    {
        # Valid arguments
        $this->assertEquals(IDataMapper::UPDATED, $this->Input->setArgument(0, $this->getMockForAbstractClass(IArgument::CLASSNAME)), 'IInput::setArgument() is supposed to return IDataMapper::UPDATED');
        $this->assertCount(1, $this->Arguments, 'IInput::setArgument() Failed to update $_Arguments');

        # Invalid arguments
        try {
        	$this->Input->setArgument('foo', $this->getMockForAbstractClass(IArgument::CLASSNAME));
        	$this->fail('Failed to generate exception with invalid arguments');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @depends test_setArgument
     * @covers ::getArgument
     */
    public function test_getArgument ()
    {
        # Valid index
        $this->assertEquals(IDataMapper::UPDATED, $this->Input->setArgument(0, $this->getMockForAbstractClass(IArgument::CLASSNAME)), 'IInput::setArgument() is supposed to return IDataMapper::UPDATED');
        $this->assertInstanceOf(IArgument::CLASSNAME, $this->Input->getArgument(0), 'IInput::getArgument() Failed to retrieve argument 0');

        # Invalid index
        $this->assertFalse($this->Input->getArgument('undefined'), 'IInput::getArgument() Should return false');
    }

    /**
     * @covers ::setOption
     */
    public function test_setOption ()
    {
        # Valid arguments
        $this->assertEquals(IDataMapper::UPDATED, $this->Input->setOption('a', $this->getMockForAbstractClass(IOption::CLASSNAME)), 'IInput::setOption() is supposed to return IDataMapper::UPDATED');
        $this->assertCount(1, $this->Options, 'IInput::setOption() Failed to update $_Options');

        # Invalid arguments
        try {
        	$this->Input->setOption(NULL, $this->getMockForAbstractClass(IOption::CLASSNAME));
        	$this->fail('Failed to generate exception with invalid Options');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @depends test_setOption
     * @covers ::getOption
     */
    public function test_getOption ()
    {
        # Valid index
        $this->assertEquals(IDataMapper::UPDATED, $this->Input->setOption('a', $this->getMockForAbstractClass(IOption::CLASSNAME)), 'IInput::setOption() is supposed to return IDataMapper::UPDATED');
        $this->assertInstanceOf(IOption::CLASSNAME, $this->Input->getOption('a'), 'IInput::getOption() Failed to retrieve Option 0');

        # Invalid index
        $this->assertFalse($this->Input->getOption('undefined'), 'IInput::getOption() Should return false');
    }

    /**
     * @covers \BLW\Type\AMediatable::setMediator
     */
    public function test_setMediator ()
    {
        $Property = new ReflectionProperty($this->Input, '_Mediator');

        $Property->setAccessible(true);

        $this->Input->setMediator($this->Mediator);

        $this->assertInstanceOf('\BLW\Type\IMediator', $Property->getValue($this->Input), 'IInput::setMediator() Failed to set $_Mediator');
    }

    /**
     * @covers ::setMediatorID
     */
    public function test_setMediatorID ()
    {
        $Property = new ReflectionProperty($this->Input, '_MediatorID');

        $Property->setAccessible(true);

        $this->Input->setMediatorID('MockInput');

        $this->assertSame('MockInput', $Property->getValue($this->Input), 'IInput::setMediatorID() Failed to set $_MediatorID');
    }

    /**
     * @covers ::read
     */
    public function test_read ()
    {
        $called = 0;

        $this->Input->_on('Input', function() use(&$called) {$called++;});

        $this->assertSame(substr(self::FILE, 16, 16), $this->Input->read(16), 'IInput::read() returned an invalid value');
        $this->assertSame(1, $called, 'IInput::read() failed to call onInput hook');
        $this->assertSame(substr(self::FILE, 32), $this->Input->read(1024), 'IInput::read() returned an invalid value');
        $this->assertSame(2, $called, 'IInput::read() failed to call onInput hook');
        $this->assertFalse($this->Input->read(1024), 'IInput::read() returned an invalid value');
        $this->assertSame(3, $called, 'IInput::read() failed to call onInput hook');
    }

    /**
     * @covers ::readline
     */
    public function test_readline ()
    {
        $called = 0;

        $this->Input->_on('Input', function() use(&$called) {$called++;});

        $this->assertSame(substr(self::FILE, 16, 8), $this->Input->readline(1024), 'IInput::readline() returned an invalid value');
        $this->assertSame(1, $called, 'IInput::readline() failed to call onInput hook');
        $this->assertSame(substr(self::FILE, 24, 7), $this->Input->readline(1024), 'IInput::readline() returned an invalid value');
        $this->assertSame(2, $called, 'IInput::read() failed to call onInput hook');
        $this->assertSame(substr(self::FILE, 31), $this->Input->readline(1024), 'IInput::readline() returned an invalid value');
        $this->assertSame(3, $called, 'IInput::read() failed to call onInput hook');
        $this->assertFalse($this->Input->readline(1024), 'IInput::readline() returned an invalid value');
        $this->assertSame(4, $called, 'IInput::read() failed to call onInput hook');
    }

    /**
     * @covers ::__toString
     */
    public function test_toString ()
    {
        $this->assertSame(substr(self::FILE, 16), @strval($this->Input), '(string) IInput returned and invalid value');
    }

    /**
     * @covers ::__get
     */
    public function test_get()
    {
        # Mediator
        $this->assertSame($this->Mediator, $this->Input->Mediator, 'IInput::$Mediator should equal $_Mediator');

        # MediatorID
        $this->assertSame('MockInput', $this->Input->MediatorID, 'IInput::$MediatorID should equal $_MediatorID');

        # Arguments
        $this->assertSame($this->Arguments, $this->Input->Arguments, 'IInput::$Arguments should equal $_Arguments');

        # Options
        $this->assertSame($this->Options, $this->Input->Options, 'IInput::$Options should equal $_Options');

        # stdIn
        $this->assertSame($this->Stream, $this->Input->stdIn, 'IInput::$stdIn should equal $_InStream');

        # Undefined
        try {
        	$this->Input->undefined;
        	$this->fail('Failed to generate notice with undefined property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {}
    }


    /**
     * @covers ::__isset
     */
    public function test_isset()
    {
        # Mediator
        $this->assertTrue(isset($this->Input->Mediator), 'IInput::$Mediator should exist');

        # MediatorID
        $this->assertTrue(isset($this->Input->MediatorID), 'IInput::$MediatorID should exist');

        # Arguments
        $this->assertTrue(isset($this->Input->Arguments), 'IInput::$Arguments should exist');

        # Options
        $this->assertTrue(isset($this->Input->Options), 'IInput::$Options should exist');

        # stdIn
        $this->assertTrue(isset($this->Input->stdIn), 'IInput::$stdIn should exist');

        # Undefined
        $this->assertFalse(isset($this->Input->undefined), 'IInput::$undefined should not exist');
    }

    /**
     * @depends test_get
     * @depends test_setMediator
     * @depends test_setMediatorID
     * @covers ::__set
     */
    public function test_set()
    {
        # Mediator
        $Expected = $this->getMockForAbstractClass('\\BLW\\Type\\IMediator');

        $this->Input->Mediator = $Expected;
        $this->assertSame($Expected, $this->Input->Mediator, 'IInput::$Mediator could not be updated');

        try {
            $this->Input->Mediator = NULL;
            $this->fail('Failed to generate notice with invalid value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # MediatorID
        $Expected = 'MediatorID';

        $this->Input->MediatorID = $Expected;
        $this->assertSame($Expected, $this->Input->MediatorID, 'IInput::$MediatorID could not be updated');

        try {
            $this->Input->MediatorID = NULL;
            $this->fail('Failed to generate notice with invalid value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # Arguments
        try {
            $this->Input->Arguments = NULL;
            $this->fail('Failed to generate notice with readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # Options
        try {
            $this->Input->Options = NULL;
            $this->fail('Failed to generate notice with readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # stdIn
        try {
            $this->Input->stdIn = NULL;
            $this->fail('Failed to generate notice with readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # Undefined
        try {
            $this->Input->undefined = '';
            $this->fail('Failed to generate notice with undefined property');
        }

        catch (\PHPUnit_Framework_Error $e) {
            $this->assertContains('non-existant property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }
    }

    /**
     * @depends test_get
     * @covers ::__unset
     */
    public function test_unset()
    {
        # Mediator
        unset($this->Input->Mediator);
        $this->assertNULL($this->Input->getMediator(), 'unset(IInput::$Mediator) Failed to unset $_Mediator');

        # MediatorID
        unset($this->Input->MediatorID);
        $this->assertSame('*', $this->Input->getMediatorID(), 'unset(IInput::$MediatorID) Failed to reset $_MediatoID');

        # Arguments
        try {
            unset($this->Input->Arguments);
            $this->fail('Failed to generate notice with readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # Options
        try {
            unset($this->Input->Options);
            $this->fail('Failed to generate notice with readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # stdIn
        try {
            unset($this->Input->stdIn);
            $this->fail('Failed to generate notice with readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # Undefined
        unset($this->Input->undefined);
    }
}