<?php
/**
 * Generic.php | Apr 8, 2014
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
namespace BLW\Model\Cron\Handler;

use DateInterval;

use Psr\Log\NullLogger;

use BLW\Model\Mediator\Symfony as Mediator;
use BLW\Model\Stream\Handle as ResourceStream;
use BLW\Model\Config\Generic as GenericConfig;

use BLW\Model\Command\Input\Generic as Input;
use BLW\Model\Command\Output\Generic as Output;
use BLW\Model\Command\Callback as CallbackCommand;

use BLW\Model\Cron\Job\Generic as Job;
use BLW\Model\Cron\Handler\Generic as Handler;


/**
 * Tests Generic cron handler
 * @package BLW\Cron
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Cron\Handler\Generic
 */
class GenericTest  extends \PHPUnit_Framework_TestCase
{
    const INPUT  = 'data:text/plain,test input';
    const OUTPUT = 'php://memory';

    /**
     * @var \BLW\Type\IMediator
     */
    protected $Mediator = NULL;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $Logger = NULL;

    /**
     * @var \BLW\Model\Cron\Handler\Generic
     */
    protected $Handler = NULL;

    protected function setUp()
    {
        $this->Mediator = new Mediator;
        $this->Logger   = new NullLogger;
        $this->Handler  = new Handler($this->Mediator, $this->Logger);
    }

    protected function tearDown()
    {
        $this->Handler  = NULL;
        $this->Logger   = NULL;
        $this->Mediator = NULL;
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Valid arguments
        $Handler = new Handler($this->Mediator, $this->Logger, true);

        $this->assertSame($this->Mediator, $Handler->getMediator(), '_Mediator', $Handler, 'Generic::__construct() Failed to set $_Mediator');
        $this->assertAttributeSame($this->Logger, 'logger', $Handler, 'Generic::__construct() Failed to set $logger');
        $this->assertAttributeSame(true, '_isThreadCompatible', $Handler, 'Generic::__construct() Failed to set $_isThreadCompatible');

        # Invalid arguents
    }

    /**
     * @depends test_construct
     * @covers ::run
     * @covers ::_die
     * @covers ::_dispatch
     */
    public function test_run()
    {
        $Input   = new Input(new ResourceStream(fopen(self::INPUT, 'r')));
        $Output  = new Output(new ResourceStream(fopen(self::OUTPUT, 'w')), new ResourceStream(fopen(self::OUTPUT, 'w')));
        $Command = new CallbackCommand(function ($Input, $Output) {

            $Output->write('foo');

            return 0;

        }, new GenericConfig(array('Timeout' => 10)));

        $Job = new Job($Command, new DateInterval('PT15M'));

        # No Jobs test
        $this->assertSame(0, $this->Handler->run($Input, $Output), 'IHandler::run() Failed to execute AlreadyRunning command');
        $this->assertStringStartsWith('No jobs. Waiting', $Output->stdOut->getContents(), 'IHandler::run() Failed to execute Waiting command');

        # Locked cron file test
        ftruncate($Output->stdOut->fp, 0);
        $this->Handler->enterMutex();

        $this->assertSame(0, $this->Handler->run($Input, $Output), 'IHandler::run() Failed to execute AlreadyRunning command');
        $this->assertStringStartsWith('Cron already running.', $Output->stdOut->getContents(), 'IHandler::run() Failed to execute Waiting command');
        $this->Handler->exitMutex();

        # Fake job test
        ftruncate($Output->stdOut->fp, 0);
        $this->Handler->attach($Job);

        $this->assertSame(0, $this->Handler->run($Input, $Output), 'IHandler::run() Failed to execute AlreadyRunning command');
        $this->assertStringStartsWith('foo', $Output->stdOut->getContents(), 'IHandler::run() Failed to execute Waiting command');

        # Waiting for jobs test
        ftruncate($Output->stdOut->fp, 0);
        $this->assertSame(0, $this->Handler->run($Input, $Output), 'IHandler::run() Failed to execute AlreadyRunning command');
        $this->assertStringStartsWith('No jobs. Waiting', $Output->stdOut->getContents(), 'IHandler::run() Failed to execute Waiting command');
    }

    /**
     * @depends test_run
     * @coversNothing
     */
    public function test_serialize()
    {
        global $BLW_Serializer;

        $BLW_Serializer = new \BLW\Model\Serializer\Mock;

        $Input          = new Input(new ResourceStream(fopen(self::INPUT, 'r')));
        $Output         = new Output(new ResourceStream(fopen(self::OUTPUT, 'w')), new ResourceStream(fopen(self::OUTPUT, 'w')));
        $Command        = new CallbackCommand(function ($Input, $Output) {

            $Output->write('foo');

            return 0;

        }, new GenericConfig(array('Timeout' => 10)));

        $Job = new Job($Command, new DateInterval('PT15M'));

        $this->Handler->attach($Job);

        # Test Command serialization
        $Serialized = unserialize(serialize($this->Handler));

        $this->assertSame(0, $Serialized->run($Input, $Output), 'IHandler::run() Failed to execute AlreadyRunning command');
        $this->assertStringStartsWith('foo', $Output->stdOut->getContents(), 'IHandler::run() Failed to execute Waiting command');
    }
}
