<?php
/**
 * SymfonyTest.php | Jan 07, 2014
 *
 * Copyright (c) 2013-2018 mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 *
 * @filesource
 * @copyright mAsT3RpEE's Zone
 * @license MIT
 */

/**
 * @package BLW\Core
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Tests\Model\Logger;

use BLW\Interfaces\Logger as LoggerInterface;
use BLW\Model\Logger\Monolog as Logger;
use Monolog\Handler\StreamHandler;


/**
 * Tests Symfony Logger Module type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class SymfonyTest extends \PHPUnit_Framework_TestCase
{
    private $Logger  = NULL;
    private $LogFile = 'BLW.log';
    private $Level   = 100;

    public function setUp()
    {
        $this->LogFile = tempnam(sys_get_temp_dir(), 'Log');

        Logger::Initialize(array(
             'DataSource' => $this->LogFile
            ,'Level'      => $this->Level
            ,'hard_init'  => true
        ));

        $this->Logger = Logger::GetInstance('test');

        $this->Logger->popHandler();
        $this->Logger->pushHandler(new StreamHandler($this->LogFile, $this->Level));
    }

    public function tearDown()
    {
        $this->Logger->popHandler();

        $this->Logger = NULL;

        sleep(0); @unlink($this->LogFile);
    }

    public function test_debug()
    {
        $this->Logger->debug('debug test');

        $Contents = file_get_contents($this->LogFile);

        $this->assertContains('DEBUG', $Contents);
        $this->assertContains('debug test', $Contents);
    }

    public function test_info()
    {
        $this->Logger->info('info test');

        $Contents = file_get_contents($this->LogFile);

        $this->assertContains('INFO', $Contents);
        $this->assertContains('info test', $Contents);
    }

    public function test_warning()
    {
        $this->Logger->warning('warning test');

        $Contents = file_get_contents($this->LogFile);

        $this->assertContains('WARNING', $Contents);
        $this->assertContains('warning test', $Contents);
    }

    public function test_error()
    {
        $this->Logger->error('error test');

        $Contents = file_get_contents($this->LogFile);

        $this->assertContains('ERROR', $Contents);
        $this->assertContains('error test', $Contents);
    }

    public function test_serialize()
    {
        $Logger     = Logger::GetInstance('test2');
        $Serialized = unserialize(serialize($Logger));

        $this->assertEquals($Logger, $Serialized);
    }
}