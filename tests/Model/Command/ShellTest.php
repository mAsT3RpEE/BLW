<?php
/**
 * ShellTest.php | Apr 1, 2014
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
namespace BLW\Tests\Model\Command;

use ReflectionProperty;
use ReflectionMethod;

use BLW\Type\Command\IOutput;

use BLW\Model\Config\Generic as GenericConfig;

use BLW\Model\Command\Shell as Command;
use BLW\Model\Command\Input\Generic as GenericInput;
use BLW\Model\Command\Output\Generic as GenericOutput;
use BLW\Model\Command\Argument\Generic as GenericArgument;
use BLW\Model\Command\Option\Generic as GenericOption;
use BLW\Model\Stream\Handle as HandleStream;
use BLW\Model\Mediator\Symfony as SymfonyMediator;

/**
 * Test for BLW ShellCommand object
 * @package BLW\Command
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Command\Shell
 */
class ShellTest extends \PHPUnit_Framework_TestCase
{
    const INPUT  = 'data:text/plain,<?php print_r($_ENV); ?>';
    const OUTPUT = 'php://memory';

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
     * @var \BLW\Type\IConfig
     */
    protected $Config = NULL;

    /**
     * @var \BLW\Model\Command\Shell
     */
    protected $Command = NULL;

    public function mock_Callback($Event)
    {
        $this->assertInstanceOf('\\BLW\\Type\\IEvent', $Event, 'ShellCommand produced an invalid event');
        //$this->assertNotSame(false, $Event->Size, 'Something unexpected happened');
    }

    protected function setUp()
    {
        $this->Input    = new GenericInput(new HandleStream(fopen(self::INPUT, 'r')));
        $this->Output   = new GenericOutput(new HandleStream(fopen(self::OUTPUT, 'w')), new HandleStream(fopen(self::OUTPUT, 'w')));
        $this->Mediator = new SymfonyMediator;
        $this->Config   = new GenericConfig(array(
        	 'Timeout'     => 10
            ,'Callback'    => array($this, 'mock_Callback')
            ,'CWD'         => NULL
            ,'Environment' => NULL
            ,'Extras'      => array()
        ));

        $this->Command = new Command('php', $this->Config, $this->Mediator, 'ShellCommand');

        $this->Input->Options[] = new GenericOption('l', true);
    }

    protected function tearDown()
    {
        $this->Command  = NULL;
        $this->Input    = NULL;
        $this->Output   = NULL;
        $this->Mediator = NULL;
        $this->Config   = NULL;
        $this->Command  = NULL;
    }

    /**
     * @covers ::getFactoryMethods
     */
    public function test_getFactoryMethods()
    {
        $this->assertGreaterThan(1, count($this->Command->getFactoryMethods()), 'ShellCommand::getFactoryMethods() returned an invalid result');
    }

    /**
     * @covers ::createCommandLine
     */
    public function test_createCommandLine()
    {
        $Expected = 'php -l foo.php';

        $this->Input->Arguments[] = new GenericArgument('foo.php');

        $this->assertEquals($Expected, $this->Command->createCommandLine('php', $this->Input), 'ShellCommand::createCommandLine() returned an invalid result');
    }

    /**
     * @covers ::createCommandLine
     */
    public function test_createDescriptors()
    {
        $Expected = array(
        	 Command::STDIN  => array('pipe', 'r')
        	,Command::STDOUT => array('pipe', 'w')
        	,Command::STDERR => array('pipe', 'w')
	   );

        $this->assertEquals($Expected, $this->Command->createDescriptiors(), 'ShellCommand::createCommandLine() returned an invalid result');
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Check properties
        $Property = new ReflectionProperty($this->Command, '_Command');

        $Property->setAccessible(true);

        $this->assertSame('php', $Property->getValue($this->Command), 'ICommand::__construct() Failed to set $_Command');

        $Property = new ReflectionProperty($this->Command, '_Config');

        $Property->setAccessible(true);

        $this->assertSame($this->Config, $Property->getValue($this->Command), 'ICommand::__construct() Failed to set $_Config');
        $this->assertSame('ShellCommand', $this->Command->getID(), 'ICommand::__construct() Failed to set $_ID');
        $this->assertSame($this->Mediator, $this->Command->getMediator(), 'ICommand::__construct() Failed to set $_Mediator');

        # Invalid ID
        try {
            $this->Command = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\ACommand', array('ping', $this->Config, $this->Mediator, array()));
            $this->fail('Failed to generate error with invalid $ID');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {}
    }

    /**
     * @covers ::isSystemCallInterupt
     */
    public function test_isSystemCallInterupt()
    {
        $this->assertTrue($this->Command->isSystemCallInterupt(array('message' => 'interrupted system call')), 'ShellCommand::isSystemCallInterupt() returned an invalid value');
        $this->assertFalse($this->Command->isSystemCallInterupt(array('message' => 'okay system call')), 'ShellCommand::isSystemCallInterupt() returned an invalid value');
    }

    /**
     * @depends test_isSystemCallInterupt
     * @covers ::transferStreams
     */
    public function test_transferStreams()
    {
        $Input = tempnam(sys_get_temp_dir(), 'input');
        $Pipes = array(
             Command::STDIN  => fopen($Input, 'w')
            ,Command::STDOUT => fopen('data:text/plain,test output', 'r')
            ,Command::STDERR => fopen('data:text/plain,test error', 'r')
        );

        $Property = new ReflectionProperty($this->Command, '_Pipes');

        $Property->setAccessible(true);
        $Property->setValue($this->Command, $Pipes);

        $this->Command->transferStreams($this->Input, $this->Output, 1, $Pipes);

        $this->assertEquals('test output', $this->Output->stdOut->getContents(), 'ShellCommand::_transferStreams() Failed to transfer STDOUT');
        $this->assertEquals('test error', $this->Output->stdErr->getContents(), 'ShellCommand::_transferStreams() Failed to transfer STDERR');
        $this->assertEquals($this->Input->stdIn->getContents(), file_get_contents($Input), 'ShellCommand::_transferStreams() Failed to trasfer STDIN');

        sleep(1); @unlink($Input);
    }

    /**
     * @depends test_createCommandLine
     * @depends test_createDescriptors
     * @depends test_transferStreams
     * @covers ::doRun
     */
    public function test_doRun()
    {
        $Expected = "No syntax errors detected in -\n";

        $this->assertEquals(0, $this->Command->doRun($this->Input, $this->Output), 'ShellCommand::doRun() returned an invalid result');
        $this->assertEquals($Expected, $this->Output->stdOut->getContents(), 'ShellCommand::doRun() Failed to process command output');
        $this->assertEmpty($this->Output->stdErr->getContents(), 'ShellCommand::doRun() Failed to process command error');
    }
}
