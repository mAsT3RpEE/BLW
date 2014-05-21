<?php
/**
 * WindowsShellTest.php | Apr 1, 2014
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

use BLW\Model\Config\Generic as GenericConfig;

use BLW\Model\Command\WindowsShell as Command;
use BLW\Model\Command\Input\Generic as GenericInput;
use BLW\Model\Command\Output\Generic as GenericOutput;
use BLW\Model\Command\Argument\Generic as GenericArgument;
use BLW\Model\Command\Option\Generic as GenericOption;
use BLW\Model\Stream\Handle as HandleStream;
use BLW\Model\Mediator\Symfony as SymfonyMediator;

/**
 * Test for BLW ShellCommand object
 * @package BLW\Command
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Command\WindowsShell
 */
class WindowsShellTest extends \PHPUnit_Framework_TestCase
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
     * @covers ::createCommandLine
     */
    public function test_createCommandLine()
    {
        $Expected = 'cmd /V:ON /E:ON /C "(php -x foo foo.php)"';

        $this->Input->Options[0]  = new GenericOption('x', 'foo');
        $this->Input->Arguments[] = new GenericArgument('foo.php');

        $this->assertEquals($Expected, $this->Command->createCommandLine('php', $this->Input), 'ShellCommand::createCommandLine() returned an invalid result');
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Check properties
        $this->assertTrue($this->Config['Extras']['bypass_shell'], 'WindowsShellCommand::__construct() Failed to update $Config[Extra]');
    }

    /**
     * @coversNothing
     */
    public function test_doRun()
    {
        // Only Windows
        if (DIRECTORY_SEPARATOR != '\\') return true;

        $Expected = 'foo';

        $this->assertEquals(0, $this->Command->run($this->Input, $this->Output), 'ShellCommand::run() returned an invalid result');
        $this->assertEquals($Expected, $this->Output->stdOut->getContents(), 'ShellCommand::doRun() Failed to process command output');
        $this->assertEmpty($this->Output->stdErr->getContents(), 'ShellCommand::doRun() Failed to process command error');
    }
}
