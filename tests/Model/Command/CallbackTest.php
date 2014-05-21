<?php
/**
 * CallbackTest.php | Apr 1, 2014
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


use BLW\Type\Command\IOutput;
use BLW\Type\Command\IInput;
use BLW\Type\Command\ICommand;

use BLW\Model\InvalidArgumentException;
use BLW\Model\Config\Generic as GenericConfig;

use BLW\Model\Command\Callback as Command;
use BLW\Model\Command\Input\Generic as GenericInput;
use BLW\Model\Command\Output\Generic as GenericOutput;
use BLW\Model\Stream\Handle as HandleStream;
use BLW\Model\Mediator\Symfony as SymfonyMediator;

/**
 * Test for BLW CallbackCommand object
 * @package BLW\Command
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Command\Callback
 */
class CallbackTest extends \PHPUnit_Framework_TestCase
{
    const INPUT  = 'data:text/plain,test input';
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
     * @var \BLW\Model\Command\Callback
     */
    protected $Command = NULL;

    /**
     * @var int
     */
    protected $Called = 0;

    /**
     * @var \Closure
     */
    protected $Action = NULL;

    public function mock_Callback($Event)
    {
        $this->assertInstanceOf('\\BLW\\Type\\IEvent', $Event, 'ShellCommand produced an invalid event');
        //$this->assertNotSame(false, $Event->Size, 'Something unexpected happened');
    }

    protected function setUp()
    {
        $this->Called   = 0;
        $Called         = &$this->Called;
        $this->Action   = function (IInput $Input, IOutput $Output, ICommand $Command) use (&$Called) {$Called++; return -1;};
        $this->Input    = new GenericInput(new HandleStream(fopen(self::INPUT, 'r')));
        $this->Output   = new GenericOutput(new HandleStream(fopen(self::OUTPUT, 'w')), new HandleStream(fopen(self::OUTPUT, 'w')));
        $this->Mediator = new SymfonyMediator;
        $this->Config   = new GenericConfig(array('Timeout' => 10));
        $this->Command  = new Command($this->Action, $this->Config, $this->Mediator, 'CallbackCommand');
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
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Valid arguments
        $this->Command  = new Command($this->Action, $this->Config, $this->Mediator);

        $this->assertAttributeInstanceOf('\\Jeremeamia\\SuperClosure\\SerializableClosure', '_Command', $this->Command, 'ICommand::__construct() Failed to set $_Command');
        $this->assertAttributeSame($this->Config, '_Config', $this->Command, 'ICommand::__construct() Failed to set $_Config');
        $this->assertRegExp('!BLW_.*!', $this->Command->getID(), 'ICommand::__construct() Failed to set $_ID');
        $this->assertSame($this->Mediator, $this->Command->getMediator(), 'ICommand::__construct() Failed to set $_Mediator');

        # Invalid arguments
        try {
            $this->Command = new Command('ping', $this->Config, $this->Mediator, 'CallbackCommand');
            $this->fail('Failed to generate notice with invalid $ID');

        } catch (InvalidArgumentException $e) {

        }

        try {
            $this->Command = new Command($this->Action, new GenericConfig, $this->Mediator, 'CallbackCommand');
            $this->fail('Failed to generate exception with invalid $ID');

        } catch (InvalidArgumentException $e) {

        }

        try {
            $this->Command = new Command($this->Action, $this->Config, $this->Mediator, array());
            $this->fail('Failed to generate exception with invalid $ID');

        } catch (\PHPUnit_Framework_Error_Notice $e) {

        }
    }

    /**
     * @covers ::doRun
     */
    public function test_doRun()
    {
        $this->assertEquals(-1, $this->Command->doRun($this->Input, $this->Output), 'CallbackCommand::doRun() returned an invalid result');
        $this->assertEquals(1, $this->Called, 'CallbackCommand::doRun() Failed to call callback');
    }
}
