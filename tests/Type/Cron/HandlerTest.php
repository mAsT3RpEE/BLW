<?php
/**
 * HandlerTest.php | Apr 8, 2014
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
namespace BLW\Type\Cron;

use ReflectionMethod;

use Psr\Log\NullLogger;
use BLW\Type\IDataMapper;


/**
 * Tests BLW Library Cron Handler base class
 * @package BLW\Cron
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\Cron\AHandler
 */
class HandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\Cron\AHandler
     */
    protected $Handler = NULL;

    protected function setUp()
    {
        $this->Handler = $this->getMockForAbstractClass('\BLW\Type\Cron\AHandler');

        $this->Handler->setMediator($this->getMockForAbstractClass('\BLW\Type\IMediator'));
        $this->Handler->setLogger(new NullLogger);
    }

    protected function tearDown()
    {
        $this->Handler = NULL;
    }

    /**
     * @covers ::_lockfile
     */
    public function test_lockfile()
    {
        $lockfile = function ($Handler)
        {
            $Method = new ReflectionMethod($Handler, '_lockfile');
            $Args   = func_get_args();
            $Args   = array_splice($Args, 1);

            $Method->setAccessible(true);
            return $Method->invokeArgs($Handler, $Args);
        };

        $this->assertNotEmpty($lockfile($this->Handler), 'IHandler::_lockfile() Returned an invalid value');
        $this->assertContains('.lock', $lockfile($this->Handler), 'IHandler::_lockfile() Returned an invalid value');
    }

    /**
     * @depends test_lockfile
     * @covers ::enterMutex
     */
    public function test_enterMutex()
    {
        $lockfile = function ($Handler)
        {
            $Method = new ReflectionMethod($Handler, '_lockfile');
            $Args   = func_get_args();
            $Args   = array_splice($Args, 1);

            $Method->setAccessible(true);
            return $Method->invokeArgs($Handler, $Args);
        };

        $this->assertTrue($this->Handler->enterMutex(), 'IHandler::enterMutex() Should return true');
        $this->assertFileExists($lockfile($this->Handler), 'IHandler::enterMutex() Did not create lockfile');
        $this->assertFalse($this->Handler->enterMutex(), 'IHandler::enterMutex() Should return false');
    }

    /**
     * @depends test_enterMutex
     * @covers ::exitMutex
     */
    public function test_exitMutex()
    {
        $this->assertTrue($this->Handler->enterMutex(), 'IHandler::enterMutex() Should return true');
        $this->assertTrue($this->Handler->exitMutex(), 'IHandler::exitMutex() Should return true');
        $this->assertFalse($this->Handler->exitMutex(), 'IHandler::exitMutex() Should return false');
    }

    /**
     * @depends test_enterMutex
     * @depends test_exitMutex
     * @covers ::__destruct
     */
    public function test_destruct()
    {
        $lockfile = function ($Handler)
        {
            $Method = new ReflectionMethod($Handler, '_lockfile');
            $Args   = func_get_args();
            $Args   = array_splice($Args, 1);

            $Method->setAccessible(true);
            return $Method->invokeArgs($Handler, $Args);
        };

        $this->assertTrue($this->Handler->enterMutex(), 'IHandler::enterMutex() Should return true');
        $this->assertFileExists($File = $lockfile($this->Handler), 'IHandler::enterMutex() Did not create lockfile');

        $this->Handler->__destruct();

        $this->assertFileNotExists($File, 'IHandler::__destruct() Failed to delete lockfile');
    }

    /**
     * @covers ::setLogger
     */
    public function test_setLogger()
    {
        $Expected = new NullLogger;

        # Valid arguments
        $this->assertEquals(IDataMapper::UPDATED, $this->Handler->setLogger($Expected), 'IJob::setLogger() Should return IDataMapper::UPDATED');
        $this->assertAttributeSame($Expected, 'logger', $this->Handler, 'IJob::setLogger() Failed to update $logger');

        # Invalid arguments
        try {
            $this->Handler->setLogger(NULL);
            $this->fail('Failed to generate error with invalid arguments');
        }

        catch(\PHPUnit_Framework_Error $e) {}

        unset($Property);
    }
}