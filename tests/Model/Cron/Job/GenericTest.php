<?php
/**
 * GenericTest.php | Apr 8, 2014
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
namespace BLW\Model\Cron\Job;

use DateInterval;

use BLW\Model\InvalidArgumentException;

use BLW\Model\Config\Generic as GenericConfig;
use BLW\Model\Cron\Job\Generic as Job;

use BLW\Model\Stream\Handle as ResourceStream;
use BLW\Model\Command\Input\Generic as Input;
use BLW\Model\Command\Output\Generic as Output;
use BLW\Model\Command\Callback as CallbackCommand;
use BLW\Model\Serializer\Mock;


/**
 * Tests Generic Cron handler class
 * @package BLW\Cron
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Cron\Job\Generic
 */
class GenericTest extends \PHPUnit_Framework_TestCase
{
    const INPUT  = 'data:text/plain,test input';
    const OUTPUT = 'php://memory';

    /**
     * @var \DateInterval
     */
    protected $Interval = NULL;

    /**
     * @var \BLW\Type\ICommand
     */
    protected $Command = NULL;

    /**
     * @var \BLW\Model\Cron\Job\Generic
     */
    protected $Job = NULL;

    protected function setUp()
    {
        $this->Interval     = new DateInterval('PT15M');
        $this->Command      = new CallbackCommand(function ($Input, $Output) {

            $Output->write('foo');

            return 0;

        }, new GenericConfig(array('Timeout' => 10)));

        $this->Job          = new Job($this->Command, $this->Interval);
    }

    protected function tearDown()
    {
        $this->Job      = NULL;
        $this->Command  = NULL;
        $this->Interval = NULL;
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Valid arguments
        $Job = new Job($this->Command, $this->Interval);

        $this->assertAttributeSame($this->Command, '_Component', $Job, 'Generic::__create() Failed to set $_Command');
        $this->assertAttributeSame($this->Interval, '_Interval', $Job, 'Generic::__create() Failed to set $_Interval');

        # Invalid arguments
        try {
            new Job($this->Command, new DateInterval('PT1S'));
            $this->fail('Failed to generate exception with invalid arguments');
        } catch (InvalidArgumentException $e) {}
    }

    /**
     * @depends test_construct
     * @coversNothing
     */
    public function test_serialize()
    {
        global $BLW_Serializer;

        $BLW_Serailizer = new \BLW\Model\Serializer\Mock;
        $Input          = new Input(new ResourceStream(fopen(self::INPUT, 'r')));
        $Output         = new Output(new ResourceStream(fopen(self::OUTPUT, 'w')), new ResourceStream(fopen(self::OUTPUT, 'w')));
        $Serialized     = unserialize(serialize($this->Job));

        $this->assertSame(0, $Serialized->run($Input, $Output), 'IHandler::run() Failed to execute AlreadyRunning command');
        $this->assertStringStartsWith('foo', $Output->stdOut->getContents(), 'IHandler::run() Failed to execute Waiting command');
    }
}
