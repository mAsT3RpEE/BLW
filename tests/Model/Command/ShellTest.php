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
namespace BLW\Model\Command;

use ReflectionProperty;
use ReflectionMethod;

use BLW\Type\Command\IOutput;

use BLW\Model\InvalidArgumentException;
use BLW\Model\Config\Generic as GenericConfig;
use BLW\Model\Command\Shell as Command;
use BLW\Model\Command\Input\Generic as GenericInput;
use BLW\Model\Command\Output\Generic as GenericOutput;
use BLW\Model\Command\Argument\Generic as GenericArgument;
use BLW\Model\Command\Option\Generic as GenericOption;
use BLW\Model\Stream\Handle as HandleStream;
use BLW\Model\Mediator\Symfony as SymfonyMediator;
use JMS\Serializer\Tests\Fixtures\Input;

/**
 * Test for BLW ShellCommand object
 * @package BLW\Command
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
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
        	 'Timeout'     => 60
            ,'Callback'    => array($this, 'mock_Callback')
            ,'CWD'         => NULL
            ,'Environment' => NULL
            ,'Extras'      => array()
        ));

        $this->Command = new Command('php', $this->Config, $this->Mediator, 'ShellCommand');

        $this->Input->Options[] = new GenericOption('r', "sleep(10); print 'foo'; exit;");
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
        $this->assertNotEmpty($this->Command->getFactoryMethods(), 'ShellCommand::getFactoryMethods() returned an ivalid result');
        $this->assertContainsOnlyInstancesOf('ReflectionMethod', $this->Command->getFactoryMethods(), 'ShellCommand::getFactoryMethods() returned an invalid result');
    }

    /**
     * @covers ::createCommandLine
     */
    public function test_createCommandLine()
    {
        $Expected = 'php -x foo foo.php';

        $this->Input->Options[0]  = new GenericOption('x', 'foo');
        $this->Input->Arguments[] = new GenericArgument('foo.php');

        $this->assertEquals($Expected, $this->Command->createCommandLine('php', $this->Input), 'ShellCommand::createCommandLine() returned an invalid result');
    }

    /**
     * @covers ::createDescriptors
     */
    public function test_createDescriptors()
    {
        $Expected = array(
        	 Command::STDIN  => array('pipe', 'r')
        	,Command::STDOUT => array('pipe', 'w')
        	,Command::STDERR => array('pipe', 'w')
	   );

        $this->assertEquals($Expected, $this->Command->createDescriptors(), 'ShellCommand::createCommandLine() returned an invalid result');
    }

    public function generateInvalidArgs()
    {
        $NoTimeout     = clone $this->Config;
        $NoCWD         = clone $this->Config;
        $NoEnvironment = clone $this->Config;
        $NoExtras      = clone $this->Config;
        $BadExtras     = clone $this->Config;

        unset($NoTimeout['Timeout']);
        unset($NoCWD['CWD']);
        unset($NoEnvironment['Environment']);
        unset($NoExtras['Extras']);

        $BadExtras['Extras'] = 0;

        return array(
            array('ping', $NoTimeout, $this->Mediator, 'CallbackCommand'),
            array('ping', $NoCWD, $this->Mediator, 'CallbackCommand'),
            array('ping', $NoEnvironment, $this->Mediator, 'CallbackCommand'),
            array('ping', $NoExtras, $this->Mediator, 'CallbackCommand'),
            array('ping', $BadExtras, $this->Mediator, 'CallbackCommand'),
        );
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Check properties
        $this->assertAttributeSame('php', '_Command', $this->Command, 'ICommand::__construct() Failed to set $_Command');
        $this->assertAttributeSame($this->Config, '_Config', $this->Command, 'ICommand::__construct() Failed to set $_Config');
        $this->assertSame('ShellCommand', $this->Command->getID(), 'ICommand::__construct() Failed to set $_ID');
        $this->assertSame($this->Mediator, $this->Command->getMediator(), 'ICommand::__construct() Failed to set $_Mediator');

        # Null ID
        $this->Command = new Command('php', $this->Config, $this->Mediator);

        $this->assertRegExp('!BLW_[0-9a-z]+!', $this->Command->getID(), 'ICommand::__construct() Failed to set $_ID');

        # Invalid ID
        try {
            $this->Command = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\ACommand', array('ping', $this->Config, $this->Mediator, array()));
            $this->fail('Failed to generate error with invalid $ID');

        } catch (\PHPUnit_Framework_Error_Notice $e) {

        }

        # Invalid arguments
        for ($i = $this->generateInvalidArgs(); list($k, list($Command, $Config, $Mediator, $ID)) = each($i);) {

            try {
                new Command($Command, $Config, $Mediator, $ID);
                $this->fail('Failed to generate exception with invalid arguments');

            } catch (InvalidArgumentException $e) {

            }
        }
    }

    /**
     * @covers ::isSystemCallInterupt
     */
    public function test_isSystemCallInterupt()
    {
        $this->assertTrue($this->Command->isSystemCallInterupt(array('message' => ' Interrupted system call')), 'ShellCommand::isSystemCallInterupt() returned an invalid value');
        $this->assertFalse($this->Command->isSystemCallInterupt(array('message' => 'okay system call')), 'ShellCommand::isSystemCallInterupt() returned an invalid value');
    }

    /**
     * @depends test_isSystemCallInterupt
     * @covers ::transferStreams
     * @covers ::_transferStdIn
     * @covers ::_transferStdOut
     * @covers ::_transferStdErr
     * @covers ::getStatus
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

        $this->Input->setMediator($this->Mediator);
        $this->Output->setMediator($this->Mediator);
        $this->Command->transferStreams($this->Input, $this->Output, 10);

        $this->assertEquals('test output', $this->Output->stdOut->getContents(), 'ShellCommand::_transferStreams() Failed to transfer STDOUT');
        $this->assertEquals('test error', $this->Output->stdErr->getContents(), 'ShellCommand::_transferStreams() Failed to transfer STDERR');
        $this->assertEquals($this->Input->stdIn->getContents(), file_get_contents($Input), 'ShellCommand::_transferStreams() Failed to trasfer STDIN');

        foreach($Pipes as $fp) {
            fclose($fp);
        }

        sleep(1); @unlink($Input);
    }

    /**
     * @depends test_createCommandLine
     * @depends test_createDescriptors
     * @depends test_transferStreams
     * @covers ::doRun
     * @covers ::open
     * @covers ::close
     * @covers ::transferStreams
     * @covers ::getStatus
     */
    public function test_doRun()
    {
        $Expected = 'foo';

        $this->assertLessThanOrEqual(0, $this->Command->doRun($this->Input, $this->Output), 'ShellCommand::doRun() returned an invalid result');
        $this->assertEquals($Expected, $this->Output->stdOut->getContents(), 'ShellCommand::doRun() Failed to process command output');
        $this->assertEmpty($this->Output->stdErr->getContents(), 'ShellCommand::doRun() Failed to process command error');
    }

    /**
     * @depends test_doRun
     * @covers ::__destruct
     * @covers ::close
     */
    public function test_destruct()
    {
        $this->assertTrue($this->Command->open($this->Input), 'ShellCommand::open() should return TRUE');
        $this->Command->__destruct();

        sleep(5);

        $Status = @proc_get_status($this->readAttribute($this->Command, '_fp'));

        if ($Status) {
            $this->assertFalse($Status['running'], 'ShellCommand::$_fp should not be a resource');
        }
    }

    /**
     * @depends test_doRun
     * @covers ::getStatus
     */
    public function test_getStatus()
    {
        $this->assertTrue($this->Command->open($this->Input), 'ShellCommand::open() should return TRUE');
        $this->assertTrue($this->Command->getStatus('running'), 'ShellCommand::getStatus() should return TRUE');

        # Invalid arguments
        try {
            $this->Command->getStatus(null);
            $this->fail('Failed to generate exception with invalid arguments');

        } catch (InvalidArgumentException $e) {

        }
    }
}
