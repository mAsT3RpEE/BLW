<?php
/**
 * HelperTest.php | May 15, 2014
 *
 * @filesource
 * @license MIT
 * @copyright Copyright (c) 2013-2018, mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * @package BLW\HTTP
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model\HTTP\Client\CURL;

use DateTime;
use BLW\Model\HTTP\Client\CURL\Helper;
use BLW\Model\InvalidArgumentException;


/**
 * Tests BLW Library cURL helper
 * @package BLW\HTTP
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\HTTP\Client\CURL\Helper
 */
class HelperTest  extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\HTTP\Client\CURL\Helper
     */
    protected $Helper = NULL;

    protected function setUp()
    {
        $this->Helper = new Helper(4);
    }

    protected function tearDown()
    {
        $this->Helper = NULL;
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        $Helper = new Helper(3);

        $this->assertInternalType('resource', $Helper->MainHandle, 'Helper::__construct() Failed to open curl multi handle');
        $this->assertCount(4, $Helper->Handles, 'Helper::__construct() Failed to open curl handles');
        $this->assertCount(4, $Helper->FreeHandles, 'Helper::__construct() Failed to set $FreeHandles');

        # Invalid handles
        try {
            new Helper(null);
            $this->fail('Failed to generate exception with invalid arguments');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @depends test_construct
     * @covers ::__destruct
     */
    public function test_destruct()
    {
        $this->Helper->__destruct();

        $this->assertFalse(is_resource($this->Helper->MainHandle), 'Helper::__destruct() Failed to close curl multi handle');
    }

    /**
     * @depends test_construct
     * @covers ::getFreeHandle
     */
    public function test_getFreeHandle()
    {
        $this->assertSame($this->Helper->FreeHandles[0], $this->Helper->getFreeHandle(), 'Helper::getFreeHandle() Returned an unexpected value');

        $this->Helper->FreeHandles = array();

        $this->assertSame(false, $this->Helper->getFreeHandle(), 'Helper::getFreeHandle() should return false');
    }

    /**
     * @depends test_construct
     * @covers ::freeHandle
     */
    public function test_freeHandle()
    {
        $Handle = array_pop($this->Helper->FreeHandles);

        $this->Helper->Stats['HostConnections']['bar.com'] = array(1,2,3,4);
        $this->Helper->Stats['HostConnections']['foo.com'] = array($Handle);
        $this->Helper->Stats['HostConnections']['pie.com'] = array(1,2,$Handle,3,4);

        $this->Helper->freeHandle($Handle);

        $this->assertCount(5, $this->Helper->FreeHandles, 'Helper::freeHandle() Failed to add handle to free list');
        $this->assertSame($Handle, $this->Helper->FreeHandles[4], 'Helper::freeHandle() Failed to add handle to free list');

        $this->assertArrayNotHasKey('foo.com', $this->Helper->Stats['HostConnections'], 'Helper::freeHandle() Failed to update host connections');
        $this->assertCount(4, $this->Helper->Stats['HostConnections']['bar.com'], 'Helper::freeHandle() Failed to update host connections');
        $this->assertCount(4, $this->Helper->Stats['HostConnections']['pie.com'], 'Helper::freeHandle() Failed to update host connections');

        try {
            $this->Helper->freeHandle(null);
            $this->fail('Failed to generate exception with invalid arguments');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::getRate
     */
    public function test_getRate()
    {
        $this->assertSame(0.0, $this->Helper->getRate('foo.com'), 'Helper::getRate() should return 0');

        $this->Helper->Stats['NewConnections'] = array(
            new DateTime('-1 day'),
            new DateTime('-1 day'),
            new DateTime('-1 day'),
            new DateTime('-1 day'),
            new DateTime('-1 day'),
            new DateTime('-1 min'),
            new DateTime('-1 min'),
            new DateTime('-1 min'),
        );

        $this->assertSame(3.0, $this->Helper->getRate('foo.com'), 'Helper::getRate() should return 3');
        $this->assertCount(3, $this->Helper->Stats['NewConnections'], 'Helper::getRate() failed to delete old data');
    }

    /**
     * @covers ::getConnections
     */
    public function test_getConnections()
    {
        $this->assertSame(0, $this->Helper->getConnections('foo.com'), 'Helper::getConnections() should return 0');

        $this->Helper->Stats['HostConnections']['foo.com'] = array (1,2,3,4);

        $this->assertSame(4, $this->Helper->getConnections('foo.com'), 'Helper::getConnections() should return 4');
    }

    /**
     * @depends test_getFreeHandle
     * @depends test_freeHandle
     * @covers ::execute
     */
    public function test_execute()
    {
        $Handle1 = $this->Helper->FreeHandles[0];
        $Handle2 = $this->Helper->FreeHandles[1];

        $this->Helper->execute($Handle1, array(
            CURLOPT_URL => 'http://example.com',
            CURLOPT_RETURNTRANSFER => 1,
        ));

        $this->Helper->execute($Handle2, array(
            CURLOPT_URL => 'http://example.com',
            CURLOPT_RETURNTRANSFER => 1,
        ));

        $active = true;

        do {

            $result = (int) curl_multi_exec($this->Helper->MainHandle, $active);

        } while ($result == CURLM_CALL_MULTI_PERFORM || $active);

        $this->assertContains('<!doctype', strval(curl_multi_getcontent($Handle1)), 'Helper::execute() Failed to execute cURL operation');
        $this->assertContains('<!doctype', strval(curl_multi_getcontent($Handle2)), 'Helper::execute() Failed to execute cURL operation');
        $this->assertCount(2, $this->Helper->Stats['NewConnections'], 'Helper::execute() Failed to register new connections');
        $this->assertCount(2, $this->Helper->Stats['HostConnections']['example.com'], 'Helper::execute() Failed to register new connextions');

        # Invalid curl handle
        try {
            $this->Helper->execute(null, array());
            $this->fail('Failed to generate exeption with invalid arguments');
        }

        catch (InvalidArgumentException $e) {}

        # Invalid cURL options
        try {
            $this->Helper->execute($this->Helper->FreeHandles[3], array('foo' => 1));
            $this->fail('Failed to generate exception with invalid cURL options');
        }

        catch (\RuntimeException $e) {
            $this->assertContains('Unable to set', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }
    }
}