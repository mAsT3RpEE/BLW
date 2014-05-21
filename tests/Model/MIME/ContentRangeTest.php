<?php
/**
 * ContentRangeTest.php | Mar 10, 2014
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


/**
 * Tests BLW Library MIME Content-Range header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\ContentRange
 */
class ContentRangeTest extends \PHPUnit_Framework_TestCase
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
        $this->Header      = new ContentRange('bytes 0-500/500');
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

    public function generateValidRanges()
    {
        return array(
             array('bytes 0-500/1024', 'bytes 0-500/1024')
            ,array('bytes 0-500/*', 'bytes 0-500/*')
        );
    }

    public function generateInvalidRanges()
    {
        return array(
             array('foo', 'invalid')
            ,array(false, 'invalid')
            ,array(new \stdClass, 'invalid')
            ,array(array(), 'invalid')
        );
    }

    /**
     * @covers ::parseRange
     */
    public function test_parseRange()
    {
        # Valid Range
        foreach ($this->generateValidRanges() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseRange($Original), 'ContentRange::parseRange() returned an invalid format');
        }

        # Invalid Range
        foreach ($this->generateInvalidRanges() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseRange($Original), 'ContentRange::parseRange() returned an invalid format');
        }
    }

    /**
     * @depends test_parseRange
     * @covers ::__construct
     */
    public function test_construct()
    {
        $this->Header = new ContentRange('bytes 0-500/500');

        # Check params
        $this->assertEquals('Content-Range', $this->Properties['Type']->getValue($this->Header), 'ContentRange::__construct() failed to set $_Type');
        $this->assertEquals('bytes 0-500/500', $this->Properties['Value']->getValue($this->Header), 'ContentRange::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            new ContentRange(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        } catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("Content-Range: bytes 0-500/500\r\n", @strval($this->Header), 'ContentRange::__toSting() returned an invalid format');
    }
}
