<?php
/**
 * AcceptRangesTest.php | Apr 8, 2014
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
 * @coversDefaultClass \BLW\Model\Mime\AcceptRanges
 */
class AcceptRangesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\MIME\AcceptRanges
     */
    protected $Header = NULL;

    /**
     * @var \ReflectionProperty[]
     */
    protected $Properties = array();

    protected function setUp()
    {
        $this->Header      = new AcceptRanges('bytes');
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
             array('bytes', 'bytes')
            ,array(';;bytes, ;;; unicode-1-1;;', 'bytes')
            ,array('"bytes"', 'bytes')
        );
    }

    public function generateInvalidTypes()
    {
        return array(
             array(false, 'none')
            ,array(new \stdClass, 'none')
            ,array(array(), 'none')
        );
    }

    /**
     * @covers ::parseRange
     */
    public function test_parseRange()
    {
        # Valid type
        foreach ($this->generateValidTypes() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseRange($Original), 'AcceptRanges::parseRange() returned an invalid format');
        }

        # Invalid type
        foreach ($this->generateInvalidTypes() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseRange($Original), 'AcceptRanges::parseRange() returned an invalid format');
        }
    }

    /**
     * @depends test_parseRange
     * @covers ::__construct
     * @covers ::_combine
     */
    public function test_construct()
    {
        $this->Header = new AcceptRanges('bytes kilobytes');

        # Check params
        $this->assertEquals('Accept-Ranges', $this->Properties['Type']->getValue($this->Header), 'AcceptRanges::__construct() failed to set $_Type');
        $this->assertEquals('bytes kilobytes', $this->Properties['Value']->getValue($this->Header), 'AcceptRanges::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            new AcceptRanges(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        } catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("Accept-Ranges: bytes\r\n", @strval($this->Header), 'AcceptRanges::__toSting() returned an invalid format');
    }
}
