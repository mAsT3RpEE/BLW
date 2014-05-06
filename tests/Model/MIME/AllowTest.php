<?php
/**
 * AllowTest.php | Apr 8, 2014
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
namespace BLW\Tests\Model\MIME;

use BLW\Model\InvalidArgumentException;
use BLW\Model\MIME\Allow;


/**
 * Tests BLW Library MIME Allow header.
 * @package BLW\MIME
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\Allow
 */
class AllowTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\MIME\Allow
     */
    protected $Header = NULL;

    /**
     * @var \ReflectionProperty[]
     */
    protected $Properties = array();

    protected function setUp()
    {
        $this->Header      = new Allow('GET, PUT, HEAD');
        $this->Properties  = array(
             'Type'  => new \ReflectionProperty($this->Header, '_Type')
        	,'Value' => new \ReflectionProperty($this->Header, '_Value')
        );

        $this->Properties['Type']->setAccessible(true);
        $this->Properties['Value']->setAccessible(true);
    }

    protected function tearDown()
    {
        $this->Properties = NULL;
        $this->Header     = NULL;
    }

    public function generateValidTypes()
    {
        return array(
        	 array('PUT', 'PUT')
        	,array(';;PUT,:::POST ;;; HEAD;;', 'PUT')
        	,array('"PUT"', 'PUT')
        );
    }

    public function generateInvalidTypes()
    {
        return array(
        	 array(false, 'GET')
        	,array(new \stdClass, 'GET')
            ,array(array(), 'GET')
        );
    }

    /**
     * @covers ::parseAllow
     */
    public function test_parseAllow()
    {
        # Valid type
        foreach($this->generateValidTypes() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseAllow($Original), 'Allow::parseAllow() returned an invalid format');
        }

        # Invalid type
        foreach($this->generateInvalidTypes() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseAllow($Original), 'Allow::parseAllow() returned an invalid format');
        }
    }

    /**
     * @depends test_parseAllow
     * @covers ::__construct
     */
    public function test_construct()
    {
        $this->Header = new Allow('GET, PUT, HEAD');

        # Check params
        $this->assertEquals('Allow', $this->Properties['Type']->getValue($this->Header), 'Allow::__construct() failed to set $_Type');
        $this->assertEquals('GET, PUT, HEAD', $this->Properties['Value']->getValue($this->Header), 'Allow::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            new Allow(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("Allow: GET, PUT, HEAD\r\n", @strval($this->Header), 'Allow::__toSting() returned an invalid format');
    }
}