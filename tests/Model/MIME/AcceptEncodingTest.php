<?php
/**
 * AcceptEncodingTest.php | Apr 8, 2014
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
 * @coversDefaultClass \BLW\Model\Mime\AcceptEncoding
 */
class AcceptEncodingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\MIME\AcceptEncoding
     */
    protected $Header = NULL;

    /**
     * @var \ReflectionProperty[]
     */
    protected $Properties = array();

    protected function setUp()
    {
        $this->Header      = new AcceptEncoding('compress, gzip; q=0.5');
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
             array('gzip', 'gzip')
            ,array(';;gzip, ;;; unicode-1-1;;', 'gzip')
            ,array('"gzip"', 'gzip')
        );
    }

    public function generateInvalidTypes()
    {
        return array(
             array(false, '*')
            ,array(new \stdClass, '*')
            ,array(array(), '*')
        );
    }

    /**
     * @covers ::parseEncoding
     */
    public function test_parseEncoding()
    {
        # Valid type
        foreach ($this->generateValidTypes() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseEncoding($Original), 'AcceptEncoding::parseEncoding() returned an invalid format');
        }

        # Invalid type
        foreach ($this->generateInvalidTypes() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseEncoding($Original), 'AcceptEncoding::parseEncoding() returned an invalid format');
        }
    }

    /**
     * @depends test_parseEncoding
     * @covers ::__construct
     * @covers ::_combine
     */
    public function test_construct()
    {
        $this->Header = new AcceptEncoding('compress, gzip; q=0.5');

        # Check params
        $this->assertEquals('Accept-Encoding', $this->Properties['Type']->getValue($this->Header), 'AcceptEncoding::__construct() failed to set $_Type');
        $this->assertEquals('compress, gzip; q=0.5', $this->Properties['Value']->getValue($this->Header), 'AcceptEncoding::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            new AcceptEncoding(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        } catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("Accept-Encoding: compress, gzip; q=0.5\r\n", @strval($this->Header), 'AcceptEncoding::__toSting() returned an invalid format');
    }
}
