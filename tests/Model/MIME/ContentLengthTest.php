<?php
/**
 * ContentLengthTest.php | Mar 10, 2014
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
use BLW\Model\MIME\ContentLength;


/**
 * Tests BLW Library MIME Content-Length header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\ContentLength
 */
class ContentLengthTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\MIME\ContentType
     */
    protected $Header = NULL;

    /**
     * @var \ReflectionProperty[]
     */
    protected $Properties = array();

    protected function setUp()
    {
        $this->Header      = new ContentLength('1024');
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

    public function generateValidLengths()
    {
        return array(
        	 array('1024', '1024')
            ,array('1024', '1024')
            ,array(';;1024;;', '1024')
        );
    }

    public function generateInvalidLengths()
    {
        return array(
        	 array('foo', '0')
        	,array(false, '0')
            ,array(new \stdClass, '0')
            ,array(array(), '0')
        );
    }

    /**
     * @covers ::parseLength
     */
    public function test_parseLength()
    {
        # Valid Length
        foreach($this->generateValidLengths() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseLength($Original), 'ContentLength::parseLength() returned an invalid format');
        }

        # Invalid Length
        foreach($this->generateInvalidLengths() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseLength($Original), 'ContentLength::parseLength() returned an invalid format');
        }
    }

    /**
     * @depends test_parseLength
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Check params
        $this->assertEquals('Content-Length', $this->Properties['Type']->getValue($this->Header), 'ContentLength::__construct() failed to set $_Type');
        $this->assertEquals('1024', $this->Properties['Value']->getValue($this->Header), 'ContentLength::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            new ContentLength(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("Content-Length: 1024\r\n", @strval($this->Header), 'ContentLength::__toSting() returned an invalid format');
    }
}
