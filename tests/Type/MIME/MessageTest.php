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
namespace BLW\Type\MIME;

use ReflectionProperty;

use BLW\Model\InvalidArgumentException;


/**
 * Tests Message Module type.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\MIME\AMessage
 */
class MessageTest extends \PHPUnit_Framework_TestCase
{
    const RAW = <<<EOT
Header: value
foo: bar1
foo: bar2

line1

line2

EOT;

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
             array('Accept', '*/*;q=0.8', '\\BLW\\Model\\MIME\\Accept')
            ,array('accept-charset', 'iso-8859-5, unicode-1-1;q=0.8', '\\BLW\\Model\\MIME\\AcceptCharset')
            ,array('ACCEPT-ENCODING', 'gzip deflate', '\\BLW\\Model\\MIME\\AcceptEncoding')
            ,array(new \SplFileInfo('foo.txt'), 'foo', '\\BLW\\Model\\MIME\\Generic')
            ,array('Accept-Language', 'en-us;q=0.8', '\\BLW\\Model\\MIME\\AcceptLanguage')
            ,array('Accept-Ranges', '0-*', '\\BLW\\Model\\MIME\\AcceptRanges')
            ,array('Age', '1000', '\\BLW\\Model\\MIME\\Age')
            ,array('Allow', 'GET', '\\BLW\\Model\\MIME\\Allow')
            ,array('Cache-Control', 'no-cache', '\\BLW\\Model\\MIME\\CacheControl')
            ,array('Connection', 'keep-alive', '\\BLW\\Model\\MIME\\Connection')
            ,array('Content-Base', 'http://google.com', '\\BLW\\Model\\MIME\\ContentBase')
            ,array('Content-Description', 'foo', '\\BLW\\Model\\MIME\\ContentDescription')
            ,array('Content-Disposition', 'inline', '\\BLW\\Model\\MIME\\ContentDisposition')
            ,array('Content-Encoding', 'gzip', '\\BLW\\Model\\MIME\\ContentEncoding')
            ,array('Content-ID', 'id@domain.com', '\\BLW\\Model\\MIME\\ContentID')
            ,array('Content-Language', 'en-us', '\\BLW\\Model\\MIME\\ContentLanguage')
            ,array('Content-Length', '1000', '\\BLW\\Model\\MIME\\ContentLength')
            ,array('Content-Location', 'http://foo.com/', '\\BLW\\Model\\MIME\\ContentLocation')
            ,array('Content-Transfer-Encoding', 'base64', '\\BLW\\Model\\MIME\\ContentTransferEncoding')
            ,array('Content-MD5', md5('foo'), '\\BLW\\Model\\MIME\\ContentMD5')
            ,array('Content-Range', '0-*/100', '\\BLW\\Model\\MIME\\ContentRange')
            ,array('Date', date('c'), '\\BLW\\Model\\MIME\\Date')
            ,array('Expires', date('c'), '\\BLW\\Model\\MIME\\Expires')
            ,array('If-Modified-Since', date('c'), '\\BLW\\Model\\MIME\\IfModifiedSince')
            ,array('Last-Modified', date('c'), '\\BLW\\Model\\MIME\\LastModified')
            ,array('Location', 'http://foo.com', '\\BLW\\Model\\MIME\\Location')
            ,array('Message-ID', 'id@hostc.com', '\\BLW\\Model\\MIME\\MessageID')
            ,array('MIME-Version', '1.0', '\\BLW\\Model\\MIME\\MIMEVersion')
            ,array('Pragma', 'no-cache', '\\BLW\\Model\\MIME\\Pragma')
            ,array('Range', '0-*', '\\BLW\\Model\\MIME\\Range')
            ,array('Referer', 'http://foo.com', '\\BLW\\Model\\MIME\\Referer')
            ,array('Subject', 'foo', '\\BLW\\Model\\MIME\\Subject')
            ,array('Trailer', 'foo', '\\BLW\\Model\\MIME\\Trailer')
            ,array('Vary', 'Content-Type', '\\BLW\\Model\\MIME\\Vary')
            ,array('Via', '1.0 fred', '\\BLW\\Model\\MIME\\Via')
        );
    }

    /**
     * @covers ::createHeader
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
        } catch (InvalidArgumentException $e) {}

        try {
            $this->Message->createHeader('foo', NULL);
            $this->fail('Failed to generate exception with invalid arguments');
        } catch (InvalidArgumentException $e) {}
    }

    public function generateHeaders()
    {
        return array(
             array('Content-Type', 'Content-Type')
            ,array('CONTENT-TYPE', 'Content-Type')
            ,array('content-type', 'Content-Type')
            ,array('content-md5',  'Content-MD5')
            ,array('content-id',   'Content-ID')
            ,array('Mime-Version', 'MIME-Version')
        );
    }

    /**
     * @covers ::normalizeHeaderType
     */
    public function test_normalizeHeaderType()
    {
        foreach ($this->generateHeaders() as $Arguments) {

            list ($Raw, $Sanitized) = $Arguments;

            $this->assertSame($Sanitized, $this->Message->normalizeHeaderType($Raw), 'IMessage::normalizeHeader() Returned an invalid value');
        }
    }

    /**
     * @depends test_normalizeHeaderType
     * @covers ::parseParts
     */
    public function test_parseParts()
    {
        $this->Message->parseParts(self::RAW, $Header, $Body);

        $this->assertCount(2, $Header, 'IMessage::parseParts() Failed to parse headers');
        $this->assertArrayHasKey('Header', $Header, 'IMessage::parseParts failed to parse headers');
        $this->assertArrayHasKey('Foo', $Header, 'IMessage::parseParts failed to parse headers');
        $this->assertGreaterThan(2, $Body, 'IMessage::parseParts() Failed to parse body');
    }
}
