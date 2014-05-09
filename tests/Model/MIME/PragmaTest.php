<?php
/**
 * PragmaTest.php | Apr 8, 2014
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
use BLW\Model\MIME\Pragma;


/**
 * Tests BLW Library MIME Accept-Charset header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\Pragma
 */
class PragmaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\MIME\Pragma
     */
    protected $Header = NULL;

    /**
     * @var \ReflectionProperty[]
     */
    protected $Properties = array();

    protected function setUp()
    {
        $this->Header      = new Pragma('no-cache, token = "quoted string"');
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

    public function generateValidDirectives()
    {
        return array(
        	 array('no-cache', 'no-cache')
        	,array(';;no-cache, ;;; unicode-1-1;;', 'no-cache')
        	,array('"no-cache"', 'no-cache')
            ,array('token', 'token')
            ,array('token=token', 'token=token')
            ,array('token =token', 'token =token')
            ,array('token = token', 'token = token')
            ,array('token = "Quoted string"', 'token = "Quoted string"')
        );
    }

    public function generateInvalidDirectives()
    {
        return array(
        	 array(false, 'no-cache')
        	,array(new \stdClass, 'no-cache')
            ,array(array(), 'no-cache')
        );
    }

    /**
     * @covers ::parseDirective
     */
    public function test_parseDirective()
    {
        # Valid type
        foreach($this->generateValidDirectives() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseDirective($Original), 'Pragma::parseDirective() returned an invalid format');
        }

        # Invalid type
        foreach($this->generateInvalidDirectives() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseDirective($Original), 'Pragma::parseDirective() returned an invalid format');
        }
    }

    /**
     * @depends test_parseDirective
     * @covers ::__construct
     * @covers ::_combine
     */
    public function test_construct()
    {
        $this->Header = new Pragma('no-cache, token = "quoted string"');

        # Check params
        $this->assertEquals('Pragma', $this->Properties['Type']->getValue($this->Header), 'Pragma::__construct() failed to set $_Type');
        $this->assertEquals('no-cache, token = "quoted string"', $this->Properties['Value']->getValue($this->Header), 'Pragma::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            new Pragma(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("Pragma: no-cache, token = \"quoted string\"\r\n", @strval($this->Header), 'Pragma::__toSting() returned an invalid format');
    }
}