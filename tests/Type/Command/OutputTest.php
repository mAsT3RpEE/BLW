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
namespace BLW\Tests\Type\Command;

use ReflectionProperty;
use ReflectionMethod;
use BLW\Type\IDataMapper;
use BLW\Type\Command\IArgument;
use BLW\Type\Command\IOption;
use BLW\Model\InvalidArgumentException;
use BLW\Model\GenericContainer;
use BLW\Model\GenericFile;
use BLW\Model\Stream\Handle as HandleStream;
use BLW\Model\Mediator\Symfony as SymfonyMediator;
use BLW\Type\Command\IOutput;


/**
 * Test for command output base class
 * @package BLW\Command
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
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
     * @covers \BLW\Type\AMediatable::setMediator
     */
    public function test_setMediator ()
    {
        $Property = new ReflectionProperty($this->Output, '_Mediator');

        $Property->setAccessible(true);

        $this->Output->setMediator($this->Mediator);

        $this->assertInstanceOf('\BLW\Type\IMediator', $Property->getValue($this->Output), 'IOutput::setMediator() Failed to set $_Mediator');
    }

    /**
     * @covers ::setMediatorID
     */
    public function test_setMediatorID ()
    {
        $Property = new ReflectionProperty($this->Output, '_MediatorID');

        $Property->setAccessible(true);

        $this->Output->setMediatorID('MockOutput');

        $this->assertSame('MockOutput', $Property->getValue($this->Output), 'IOutput::setMediatorID() Failed to set $_MediatorID');
    }

    /**
     * @covers ::write
     */
    public function test_write ()
    {
        $Test           = "Test line 1\r\nTest line 2\r\n";
        $Output         = 0;
        $Error          = 0;
        $LastLineLength = function($Output) {
            $Property   = new ReflectionProperty('\\BLW\\Type\\Command\\AOutput', '_LastLineLength');

            $Property->setAccessible(true);

            return $Property->getValue($Output);
        };

        $this->Output->_on('Output', function() use(&$Output) {$Output++;});
        $this->Output->_on('Error', function() use(&$Error) {$Error++;});

        $this->assertSame(strlen($Test), $this->Output->write($Test, IOutput::STDOUT), 'IOutput::write() returned an invalid value');
        $this->assertSame(1, $Output, 'IOutput::write() failed to call onOutput hook');
        $this->assertSame($Test, $this->stdOut->getContents(), 'IOutput::write() Failed to update output stream');
        $this->assertSame(11, $LastLineLength($this->Output), 'IOutput::write() Failed to update $_LastLineLength');

        $this->assertSame(strlen($Test), $this->Output->write($Test, IOutput::STDERR), 'IOutput::write() returned an invalid value');
        $this->assertSame(1, $Error, 'IOutput::write() failed to call onError hook');
        $this->assertSame($Test, $this->stdErr->getContents(), 'IOutput::write() Failed to update error stream');

        $this->assertSame(strlen($Test)*2, $this->Output->write($Test, IOutput::STDERR | IOutput::STDOUT), 'IOutput::write() returned an invalid value');
        $this->assertSame(2, $Output, 'IOutput::write() failed to call onOutput hook');
        $this->assertSame(2, $Error, 'IOutput::write() failed to call onError hook');
        $this->assertSame($Test.$Test, $this->stdOut->getContents(), 'IOutput::write() Failed to update output stream');
        $this->assertSame($Test.$Test, $this->stdErr->getContents(), 'IOutput::write() Failed to update error stream');
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
    public function test_toString ()
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
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {}
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
     * @depends test_setMediator
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
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # MediatorID
        $Expected = 'MediatorID';

        $this->Output->MediatorID = $Expected;
        $this->assertSame($Expected, $this->Output->MediatorID, 'IOutput::$MediatorID could not be updated');

        try {
            $this->Output->MediatorID = NULL;
            $this->fail('Failed to generate notice with invalid value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # stdOut
        try {
            $this->Output->stdOut = NULL;
            $this->fail('Failed to generate notice with readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # stdErr
        try {
            $this->Output->stdErr = NULL;
            $this->fail('Failed to generate notice with readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # Undefined
        try {
            $this->Output->undefined = '';
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
        unset($this->Output->Mediator);
        $this->assertNULL($this->Output->getMediator(), 'unset(IOutput::$Mediator) Failed to unset $_Mediator');

        # MediatorID
        unset($this->Output->MediatorID);
        $this->assertSame('*', $this->Output->getMediatorID(), 'unset(IOutput::$MediatorID) Failed to reset $_MediatoID');

        # stdOut
        try {
            unset($this->Output->stdOut);
            $this->fail('Failed to generate notice with readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # stdErr
        try {
            unset($this->Output->stdErr);
            $this->fail('Failed to generate notice with readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # Undefined
        unset($this->Output->undefined);
    }
}