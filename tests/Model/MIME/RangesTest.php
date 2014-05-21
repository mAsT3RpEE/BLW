<?php
/**
 * RangeTest.php | Apr 8, 2014
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
 * Tests BLW Library MIME Contetn-Type header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\Range
 */
class RangesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\MIME\Range
     */
    protected $Header = NULL;

    /**
     * @var \ReflectionProperty[]
     */
    protected $Properties = array();

    protected function setUp()
    {
        $this->Header      = new Range('bytes=0-500');
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
             array('token=0-500', 'token=0-500')
            ,array(';;token=0-500;;;', 'token=0-500')
            ,array('"token=0-500"', 'token=0-500')
            ,array('token=-500', 'token=-500')
            ,array('token=0-', 'token=0-')
            ,array('token=500-1000,', 'token=500-1000')
            ,array('token=0-500,500-1000', 'token=0-500,500-1000')
        );
    }

    public function generateInvalidTypes()
    {
        return array(
             array(false, 'bytes=0-0')
            ,array(new \stdClass, 'bytes=0-0')
            ,array(array(), 'bytes=0-0')
        );
    }

    /**
     * @covers ::parseRanges
     */
    public function test_parseRanges()
    {
        # Valid type
        foreach ($this->generateValidTypes() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseRanges($Original), 'Range::parseRanges() returned an invalid format');
        }

        # Invalid type
        foreach ($this->generateInvalidTypes() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseRanges($Original), 'Range::parseRanges() returned an invalid format');
        }
    }

    /**
     * @depends test_parseRanges
     * @covers ::__construct
     */
    public function test_construct()
    {
        $this->Header = new Range('bytes=0-500');

        # Check params
        $this->assertEquals('Range', $this->Properties['Type']->getValue($this->Header), 'Range::__construct() failed to set $_Type');
        $this->assertEquals('bytes=0-500', $this->Properties['Value']->getValue($this->Header), 'Range::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            new Range(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        } catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("Range: bytes=0-500\r\n", @strval($this->Header), 'Range::__toSting() returned an invalid format');
    }
}
