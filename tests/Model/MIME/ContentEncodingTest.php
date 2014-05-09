<?php
/**
 * ContentEncodingTest.php | Apr 8, 2014
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
use BLW\Model\MIME\ContentEncoding;


/**
 * Tests BLW Library MIME Contetn-Type header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\ContentEncoding
 */
class ContentEncodingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\MIME\ContentEncoding
     */
    protected $Header = NULL;

    protected function setUp()
    {
        $this->Header = new ContentEncoding('compress, gzip; q=0.5');
    }

    protected function tearDown()
    {
        $this->Header = NULL;
    }

    public function generateValidEncodings()
    {
        return array(
        	 array('gzip', 'gzip')
        	,array(';;gzip, ;;; unicode-1-1;;', 'gzip')
        	,array('"gzip"', 'gzip')
        );
    }

    public function generateInvalidEncodings()
    {
        return array(
        	 array(false, '')
        	,array(new \stdClass, '')
            ,array(array(), '')
        );
    }

    /**
     * @covers ::parseEncoding
     */
    public function test_parseEncoding()
    {
        # Valid type
        foreach($this->generateValidEncodings() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseEncoding($Original), 'ContentEncoding::parseEncoding() returned an invalid format');
        }

        # Invalid type
        foreach($this->generateInvalidEncodings() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseEncoding($Original), 'ContentEncoding::parseEncoding() returned an invalid format');
        }
    }

    /**
     * @depends test_parseEncoding
     * @covers ::__construct
     * @covers ::_combine
     */
    public function test_construct()
    {
        $this->Header = new ContentEncoding('compress, gzip');

        # Check params
        $this->assertAttributeEquals('Content-Encoding', '_Type', $this->Header, 'ContentEncoding::__construct() failed to set $_Type');
        $this->assertAttributeEquals('compress, gzip', '_Value', $this->Header, 'ContentEncoding::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            new ContentEncoding(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("Content-Encoding: compress, gzip\r\n", @strval($this->Header), 'ContentEncoding::__toSting() returned an invalid format');
    }
}