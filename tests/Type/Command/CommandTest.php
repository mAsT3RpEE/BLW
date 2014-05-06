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
namespace BLW\Tests\Type\Command;

use ReflectionProperty;
use ReflectionMethod;

use BLW\Type\Command\IOutput;

use BLW\Model\Config\Generic as GenericConfig;

use BLW\Model\Command\Input\Generic as GenericInput;
use BLW\Model\Command\Output\Generic as GenericOutput;
use BLW\Model\Stream\Handle as HandleStream;
use BLW\Model\Mediator\Symfony as SymfonyMediator;

/**
 * Test for base Command class
 * @package BLW\Command
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\ACommand
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
     * @var \BLW\Type\ACommand
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
        # Check properties
        $Property = new ReflectionProperty($this->Command, '_Command');

        $Property->setAccessible(true);

        $this->assertSame($this->Action, $Property->getValue($this->Command), 'ICommand::__construct() Failed to set $_Command');

        $Property = new ReflectionProperty($this->Command, '_Config');

        $Property->setAccessible(true);

        $this->assertSame($this->Config, $Property->getValue($this->Command), 'ICommand::__construct() Failed to set $_Config');
        $this->assertSame('MockCommand', $this->Command->getID(), 'ICommand::__construct() Failed to set $_ID');
        $this->assertSame($this->Mediator, $this->Command->getMediator(), 'ICommand::__construct() Failed to set $_Mediator');

        # Invalid ID
        try {
            $this->Command = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\ACommand', array($this->Action, $this->Config, $this->Mediator, array()));
            $this->fail('Failed to generate error with invalid $ID');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {}
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

        $this->Command->_on('Notify', function() use (&$Called, &$Arguments) {
            $Called++;
            $Arguments = func_get_args();
        });

        # Call notify
        $this->Command->doNotify(-1, array('foo' => 1));

        $this->assertEquals(1, $Called, 'ICommand::doNotify() Failed to trigger callback');
        $this->assertNotEmpty($Arguments, 'ICommand::doNotify() Caused an exceptional behaviour');
        $this->assertInstanceOf('\\BLW\\Model\\Command\\Event', $Arguments[0], 'ICommand::doNotify() Created and invalid event');
        $this->assertEquals(1, $Arguments[0]->foo, 'ICommand::doNotify() Created and invalid event');
    }

    /**
     * @depend test_doNotify
     * @covers ::onNotify
     */
    public function test_onNotify()
    {
        $Called = 0;
        $Type   = 0;

        $this->Command->onNotify(function($Event) use (&$Called, &$Type) {$Called++; $Type = $Event->Type;});
        $this->Command->doNotify(-1);

        $this->assertEquals(1, $Called, 'ICommand::onNotify() Failed to register callback');
        $this->assertEquals(-1, $Type, 'ICommand::onNotify() Failed to register callback');
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

        $this->Command->onInput(function() use (&$Called) {$Called++;});
        $this->Command->run($this->Input, $this->Output);

        $this->assertEquals(1, $Called, 'ICommand::onInput() Failed to register callback');
    }


    /**
     * @depends test_run
     * @covers ::onOutput
     */
    public function test_onOutput()
    {
        $Called = 0;

        $this->Command->onOutput(function() use (&$Called) {$Called++;});
        $this->Command->run($this->Input, $this->Output);

        $this->assertEquals(1, $Called, 'ICommand::onInput() Failed to register callback');
    }

    /**
     * @depends test_run
     * @covers ::onError
     */
    public function test_onError()
    {
        $Called = 0;

        $this->Command->onError(function() use (&$Called) {$Called++;});
        $this->Command->run($this->Input, $this->Output);

        $this->assertEquals(1, $Called, 'ICommand::onInput() Failed to register callback');
    }

    /**
     * @depends test_construct
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertSame(sprintf('[Command:%s:MockCommand]', basename(get_class($this->Command))), @strval($this->Command), '(string) ICommand returned an invalid value');
    }
}
