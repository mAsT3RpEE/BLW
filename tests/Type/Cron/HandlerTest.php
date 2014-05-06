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
namespace BLW\Tests\Type\Cron;

use ReflectionProperty;
use ReflectionMethod;

use Psr\Log\NullLogger;


/**
 * Tests BLW Library Cron Handler base class
 * @package BLW\Cron
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
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
     * @covers ::enterMutex
     */
    public function test_enterMutex()
    {
        $this->assertTrue($this->Handler->enterMutex(), 'IHandler::enterMutex() Should return true');
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
}