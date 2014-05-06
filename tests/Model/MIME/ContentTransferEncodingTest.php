<?php
/**
 * ContentTranferEncodingTest.php | Mar 10, 2014
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
use BLW\Model\MIME\ContentTransferEncoding;


/**
 * Tests BLW Library MIME Contetn-Type header.
 * @package BLW\MIME
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\ContentTransferEncoding
 */
class ContentTransferEncodingTest extends \PHPUnit_Framework_TestCase
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
        $this->Header      = new ContentTransferEncoding('quoted-printable');
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

    public function generateValidEncodings()
    {
        return array(
        	 array('7bit', '7bit')
        	,array('8bit', '8bit')
        	,array('binary', 'binary')
            ,array('quoted-printable', 'quoted-printable')
            ,array('base64', 'base64')
            ,array(';;base64;;', 'base64')
        );
    }

    public function generateInvalidEncodings()
    {
        return array(
        	 array('foo', 'binary')
            ,array('9bit', 'binary')
        	,array(false, 'binary')
        );
    }

    /**
     * @covers ::parseEncoding
     */
    public function test_parseEncoding()
    {
        # Valid Encoding
        foreach($this->generateValidEncodings() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseEncoding($Original), 'ContentTransferEncoding::parseEncoding() returned an invalid format');
        }

        # Invalid Encoding
        foreach($this->generateInvalidEncodings() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseEncoding($Original), 'ContentTransferEncoding::parseEncoding() returned an invalid format');
        }
    }

    /**
     * @depends test_parseEncoding
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Check params
        $this->assertEquals('Content-Transfer-Encoding', $this->Properties['Type']->getValue($this->Header), 'ContentTransferEncoding::__construct() failed to set $_Type');
        $this->assertEquals('quoted-printable', $this->Properties['Value']->getValue($this->Header), 'ContentTranferEncoding::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            new ContentTransferEncoding(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("Content-Transfer-Encoding: quoted-printable\r\n", @strval($this->Header), 'ContentTransferEncoding::__toSting() returned an invalid format');
    }
}
