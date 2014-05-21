<?php
/**
 * OutputTest.php | Mar 30, 2014
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

use ReflectionProperty;
use BLW\Type\IDataMapper;
use BLW\Model\InvalidArgumentException;
use BLW\Model\Stream\Handle as HandleStream;
use BLW\Model\Mediator\Symfony as SymfonyMediator;


/**
 * Test for command output base class
 * @package BLW\Command
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\Command\AOutput
 */
class OutputTest extends \PHPUnit_Framework_TestCase
{
    const FILE = "php://memory";

    /**
     * @var \BLW\Type\AMediator
     */
    protected $Mediator = NULL;

    /**
     * @var \BLW\Model\Stream\Handle
     */
    protected $stdOut = NULL;

    /**
     * @var \BLW\Model\Stream\Handle
     */
    protected $stdErr = NULL;

    /**
     * @var \BLW\Type\Command\AOutput
     */
    protected $Output = NULL;

    protected function setUp()
    {
        $this->Mediator  = new SymfonyMediator;
        $this->stdOut    = new HandleStream(fopen(self::FILE, 'w'));
        $this->stdErr    = new HandleStream(fopen(self::FILE, 'w'));
        $this->Output    = $this->getMockForAbstractClass('\BLW\Type\Command\AOutput');

        $this->Output->setMediator($this->Mediator);
        $this->Output->setMediatorID('MockOutput');

        $Property        = new ReflectionProperty($this->Output, '_OutStream');
        $Property->setAccessible(true);
        $Property->setValue($this->Output, $this->stdOut);

        $Property        = new ReflectionProperty($this->Output, '_ErrStream');
        $Property->setAccessible(true);
        $Property->setValue($this->Output, $this->stdErr);
    }

    protected function tearDown()
    {
        $this->Output    = NULL;
        $this->stdOut    = NULL;
        $this->stdErr    = NULL;
        $this->Mediator  = NULL;
    }

    /**
     * @covers ::setMediatorID
     */
    public function test_setMediatorID()
    {
        $Property = new ReflectionProperty($this->Output, '_MediatorID');

        $Property->setAccessible(true);

        $this->assertSame(IDataMapper::UPDATED, $this->Output->setMediatorID('MockOutput'), 'IOutput::setMediatorID() Should return IDataMapper::UPDATED');

        $this->assertSame('MockOutput', $Property->getValue($this->Output), 'IOutput::setMediatorID() Failed to set $_MediatorID');

        $this->assertSame(IDataMapper::UPDATED, $this->Output->setMediatorID(new \SplFileInfo(__FILE__)), 'IOutput::setMediatorID() Should return IDataMapper::UPDATED');
        $this->assertSame(__FILE__, $Property->getValue($this->Output), 'IOutput::setMediatorID() Failed to set $_MediatorID');

        # Invalid arguments
        $this->assertSame(IDataMapper::INVALID, $this->Output->setMediatorID(NULL), 'IInput::setMediatorID() Should return IDataMapper::INVALID');
    }

    /**
     * @covers ::write
     */
    public function test_write()
    {
        $Test           = "Test line 1\r\nTest line 2\r\n";
        $Output         = 0;
        $Error          = 0;

        $this->Output->_on('Output', function () use (&$Output) {$Output++;});
        $this->Output->_on('Error', function () use (&$Error) {$Error++;});

        $this->assertSame(strlen($Test), $this->Output->write($Test, IOutput::STDOUT), 'IOutput::write() returned an invalid value');
        $this->assertSame(1, $Output, 'IOutput::write() failed to call onOutput hook');
        $this->assertSame($Test, $this->stdOut->getContents(), 'IOutput::write() Failed to update output stream');
        $this->assertSame(11, $this->readAttribute($this->Output, '_LastLineLength'), 'IOutput::write() Failed to update $_LastLineLength');

        $this->assertSame(strlen($Test), $this->Output->write($Test, IOutput::STDERR), 'IOutput::write() returned an invalid value');
        $this->assertSame(1, $Error, 'IOutput::write() failed to call onError hook');
        $this->assertSame($Test, $this->stdErr->getContents(), 'IOutput::write() Failed to update error stream');

        $this->assertSame(strlen($Test)*2, $this->Output->write($Test, IOutput::STDERR | IOutput::STDOUT), 'IOutput::write() returned an invalid value');
        $this->assertSame(2, $Output, 'IOutput::write() failed to call onOutput hook');
        $this->assertSame(2, $Error, 'IOutput::write() failed to call onError hook');
        $this->assertSame($Test.$Test, $this->stdOut->getContents(), 'IOutput::write() Failed to update output stream');
        $this->assertSame($Test.$Test, $this->stdErr->getContents(), 'IOutput::write() Failed to update error stream');

        # Invalid argument
        try {
            $this->Output->write(NULL, IOutput::STDOUT);
            $this->fail('Failed to generate exception with invalid arguments');
        } catch (InvalidArgumentException $e) {}
    }

    /**
     * @depends test_write
     * @covers ::overwrite
     */
    public function test_overwrite()
    {
        $Test   = "Test line1\r\nTest line 2";

        $this->assertSame(strlen($Test), $this->Output->write($Test, IOutput::STDOUT), 'IOutput::write() returned an invalid value');
        $this->assertSame($Test, $this->stdOut->getContents(), 'IOutput::write() Failed to update output stream');

        $this->assertSame(strlen($Test), $this->Output->overwrite($Test, IOutput::STDOUT), 'IOutput::overwrite() returned an invalid value');
        $this->assertSame("$Test\r$Test", $this->stdOut->getContents(), 'IOutput::overwrite() Failed to update output stream');
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertContains(basename(get_class($this->Output)), @strval($this->Output), '(string) IOutput returned and invalid value');
    }

    /**
     * @covers ::__get
     */
    public function test_get()
    {
        # Mediator
        $this->assertSame($this->Mediator, $this->Output->Mediator, 'IOutput::$Mediator should equal $_Mediator');

        # MediatorID
        $this->assertSame('MockOutput', $this->Output->MediatorID, 'IOutput::$MediatorID should equal $_MediatorID');

        # stdOut
        $this->assertSame($this->stdOut, $this->Output->stdOut, 'IOutput::$stdOut should equal $_OutStream');

        # stdErr
        $this->assertSame($this->stdErr, $this->Output->stdErr, 'IOutput::$stdErr should equal $_ErrStream');

        # Undefined
        try {
            $this->Output->undefined;
            $this->fail('Failed to generate notice with undefined property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {}

        $this->assertNull(@$this->Output->undefined, 'IOutput::$undefined Should be NULL');
    }


    /**
     * @covers ::__isset
     */
    public function test_isset()
    {
        # Mediator
        $this->assertTrue(isset($this->Output->Mediator), 'IOutput::$Mediator should exist');

        # MediatorID
        $this->assertTrue(isset($this->Output->MediatorID), 'IOutput::$MediatorID should exist');

        # stdOut
        $this->assertTrue(isset($this->Output->stdOut), 'IOutput::$stdOut should exist');

        # stdErr
        $this->assertTrue(isset($this->Output->stdErr), 'IOutput::$stdErr should exist');

        # Undefined
        $this->assertFalse(isset($this->Output->undefined), 'IOutput::$undefined should not exist');
    }

    /**
     * @depends test_get
     * @depends test_setMediatorID
     * @covers ::__set
     */
    public function test_set()
    {
        # Mediator
        $Expected = $this->getMockForAbstractClass('\\BLW\\Type\\IMediator');

        $this->Output->Mediator = $Expected;
        $this->assertSame($Expected, $this->Output->Mediator, 'IOutput::$Mediator could not be updated');

        try {
            $this->Output->Mediator = NULL;
            $this->fail('Failed to generate notice with invalid value');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Output->Mediator = NULL;

        # MediatorID
        $Expected = 'MediatorID';

        $this->Output->MediatorID = $Expected;
        $this->assertSame($Expected, $this->Output->MediatorID, 'IOutput::$MediatorID could not be updated');

        try {
            $this->Output->MediatorID = NULL;
            $this->fail('Failed to generate notice with invalid value');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Output->MediatorID = NULL;

        # stdOut
        try {
            $this->Output->stdOut = NULL;
            $this->fail('Failed to generate notice with readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Output->stdOut = NULL;

        # stdErr
        try {
            $this->Output->stdErr = NULL;
            $this->fail('Failed to generate notice with readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Output->stdErr = NULL;

        # Undefined
        try {
            $this->Output->undefined = '';
            $this->fail('Failed to generate notice with undefined property');
        } catch (\PHPUnit_Framework_Error $e) {
            $this->assertContains('non-existant property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Output->undefined = '';
    }

    /**
     * @depends test_get
     * @covers ::__unset
     */
    public function test_unset()
    {
        # Mediator
        unset($this->Output->Mediator);
        $this->assertNULL($this->Output->getMediator(), 'unset(IOutput::$Mediator) Failed to unset $_Mediator');

        # MediatorID
        unset($this->Output->MediatorID);
        $this->assertSame('*', $this->Output->getMediatorID(), 'unset(IOutput::$MediatorID) Failed to reset $_MediatoID');

        # stdOut
        try {
            unset($this->Output->stdOut);
            $this->fail('Failed to generate notice with readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Output->__unset('stdOut');

        # stdErr
        try {
            unset($this->Output->stdErr);
            $this->fail('Failed to generate notice with readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Output->__unset('stdErr');

        # Undefined
        unset($this->Output->undefined);
    }
}
