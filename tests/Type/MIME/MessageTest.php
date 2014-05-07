<?php
/**
 * MessageTest.php | Mar 20, 2014
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
namespace BLW\Tests\Type\MIME;

use ReflectionProperty;
use ReflectionMethod;

use BLW\Model\InvalidArgumentException;


/**
 * Tests Message Module type.
 * @package BLW\MIME
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\MIME\AMessage
 */
class MessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\MIME\IMessage
     */
    protected $Message = NULL;

    /**
     * @var \BLW\Type\MIME\IHead
     */
    protected $Head    = NULL;

    /**
     * @var \BLW\Type\MIME\IBody
     */
    protected $Body    = NULL;

    protected function setUp()
    {
        $this->Message = $this->getMockForAbstractClass('\\BLW\\Type\\MIME\\AMessage');
        $this->Head    = $this->getMockForAbstractClass('\\BLW\\Type\\MIME\\IHead');
        $this->Body    = $this->getMockForAbstractClass('\\BLW\\Type\\MIME\\IBody');

        $this->Head
            ->expects($this->any())
            ->method('__toString')
            ->will($this->returnValue("Head\r\n"));

        $this->Body
            ->expects($this->any())
            ->method('__toString')
            ->will($this->returnValue("Body\r\n"));

        $Property = new ReflectionProperty($this->Message, '_Head');

        $Property->setAccessible(true);
        $Property->setValue($this->Message, $this->Head);

        $Property = new ReflectionProperty($this->Message, '_Body');

        $Property->setAccessible(true);
        $Property->setValue($this->Message, $this->Body);
    }

    protected function tearDown()
    {
        $this->Message = NULL;
        $this->Head    = NULL;
        $this->Body    = NULL;
    }

    /**
     * @covers ::getHeader
     */
    public function test_getHeader()
    {
        $this->assertSame($this->Head, $this->Message->getHeader(), 'IMessage::getHeader() returned an invalid result');
    }

    /**
     * @covers ::getBody
     */
    public function test_getBody()
    {
        $this->assertSame($this->Body, $this->Message->getBody(), 'IMessage::getBody() returned an invalid result');
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("Head\r\nBody\r\n", @strval($this->Message), '(string) IMessage should equal HEAD\r\nBODY\r\n');
    }

    public function generateValidHeaders()
    {
        return array(
        	 array('Content-Type', 'text/html; charset="UTF-8"', '\\BLW\\Model\\MIME\\Generic')
            ,array('accept-charset', 'iso-8859-5, unicode-1-1;q=0.8', '\\BLW\\Model\\MIME\\AcceptCharset')
            ,array('CONTENT-BASE', 'http://google.com', '\\BLW\\Model\\MIME\\ContentBase')
            ,array('Last-Modified', 'Wed, 15 Nov 1995 04:58:08 GMT', '\\BLW\\Model\\MIME\\LastModified')
        );
    }

    /**
     * @cover ::createHeader
     */
    public function test_createHeader()
    {
        # Valid arguments
        foreach ($this->generateValidHeaders() as $Arguments) {

            list($Type, $Value, $Expected) = $Arguments;

            $this->assertInstanceOf($Expected, $this->Message->createHeader($Type, $Value), 'IMessage::createHeader() Returned an invalid result');
        }

        # Empty Header
        $Header = $this->Message->createHeader('"', '');

        $this->assertEquals('', @strval($Header), 'IMessage::createHeader() Did not deal with empty $Type');

        # Invalid arguments
        try {
            $this->Message->createHeader(NULL, 'foo');
            $this->fail('Failed to generate exception with invalid arguments');
        }

        catch (InvalidArgumentException $e) {}

            try {
            $this->Message->createHeader('foo', NULL);
            $this->fail('Failed to generate exception with invalid arguments');
        }

        catch (InvalidArgumentException $e) {}
    }
}