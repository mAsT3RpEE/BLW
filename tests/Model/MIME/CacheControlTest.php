<?php
/**
 * CacheControlTest.php | Apr 8, 2014
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
use BLW\Model\MIME\CacheControl;


/**
 * Tests BLW Library MIME Accept-Charset header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\CacheControl
 */
class CacheControlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\MIME\CacheControl
     */
    protected $Header = NULL;

    /**
     * @var \ReflectionProperty[]
     */
    protected $Properties = array();

    protected function setUp()
    {
        $this->Header      = new CacheControl('no-cache, must-revalidate');
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
            ,array('no-store', 'no-store')
            ,array('max-age=30', 'max-age=30')
            ,array('max-stale', 'max-stale')
            ,array('max-age', 'max-age')
            ,array('max-fresh=30', 'max-fresh=30')
            ,array('no-transform', 'no-transform')
            ,array('only-if-cached', 'only-if-cached')
            ,array('public', 'public')
            ,array('private = "true"', 'private = "true"')
            ,array('no-cache="true"', 'no-cache="true"')
            ,array('must-revalidate', 'must-revalidate')
            ,array('proxy-revalidate', 'proxy-revalidate')
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

            $this->assertEquals($Expected, $this->Header->parseDirective($Original), 'CacheControl::parseDirective() returned an invalid format');
        }

        # Invalid type
        foreach($this->generateInvalidDirectives() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseDirective($Original), 'CacheControl::parseDirective() returned an invalid format');
        }
    }

    /**
     * @depends test_parseDirective
     * @covers ::__construct
     * @covers ::_combine
     */
    public function test_construct()
    {
        $this->Header = new CacheControl('no-cache,must-revalidate');

        # Check params
        $this->assertEquals('Cache-Control', $this->Properties['Type']->getValue($this->Header), 'CacheControl::__construct() failed to set $_Type');
        $this->assertEquals('no-cache, must-revalidate', $this->Properties['Value']->getValue($this->Header), 'CacheControl::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            new CacheControl(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("Cache-Control: no-cache, must-revalidate\r\n", @strval($this->Header), 'CacheControl::__toSting() returned an invalid format');
    }
}
