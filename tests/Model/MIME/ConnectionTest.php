<?php
/**
 * ConnectionTest.php | Mar 10, 2014
 *
 * @filesource
 * @license MIT
 * @copyright Copyright (c) 2013-2018, mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * @package BLW\MIME
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model\MIME;

use BLW\Model\InvalidArgumentException;
use BLW\Model\MIME\Connection;


/**
 * Tests BLW Library MIME Connection header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\Connection
 */
class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\MIME\ContentType
     */
    protected $Header = NULL;

    protected function setUp()
    {
        $this->Header = new Connection('keep-alive');
    }

    protected function tearDown()
    {
        $this->Header = NULL;
    }

    public function generateValidConnections()
    {
        return array(
        	 array('keep-alive', 'keep-alive')
            ,array('drop', 'drop')
        );
    }

    public function generateInvalidConnections()
    {
        return array(
        	 array('????', '')
        	,array(false, '')
            ,array(new \stdClass, '')
            ,array(array(), '')
        );
    }

    /**
     * @covers ::parseConnection
     */
    public function test_parseConnection()
    {
        # Valid Connection
        foreach($this->generateValidConnections() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseConnection($Original), 'Connection::parseConnection() returned an invalid format');
        }

        # Invalid Connection
        foreach($this->generateInvalidConnections() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseConnection($Original), 'Connection::parseConnection() returned an invalid format');
        }
    }

    /**
     * @depends test_parseConnection
     * @covers ::__construct
     */
    public function test_construct()
    {
        $this->Header = new Connection('keep-alive');

        # Check params
        $this->assertAttributeEquals('Connection', '_Type', $this->Header, 'Connection::__construct() failed to set $_Type');
        $this->assertAttributeEquals('keep-alive', '_Value', $this->Header, 'Connection::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            new Connection(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("Connection: keep-alive\r\n", @strval($this->Header), 'Connection::__toSting() returned an invalid format');
    }
}