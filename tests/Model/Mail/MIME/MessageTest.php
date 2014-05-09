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
 * @package BLW\Mail
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model\Mail\MIME;

use ReflectionProperty;
use ReflectionMethod;

use BLW\Model\InvalidArgumentException;

use BLW\Type\MIME\IMessage;

use BLW\Model\GenericContainer;
use BLW\Model\GenericEmailAddress;
use BLW\Model\MIME\Generic;
use BLW\Model\MIME\CC;
use BLW\Model\Mail\GenericMessage;
use BLW\Model\Mail\MIME\Message;


/**
 * Tests MimeMessage Module type.
 * @package BLW\Mail
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mail\MIME\Message
 */
class MessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\Mail\MIME\Message
     */
    protected $MimeMessage = NULL;

    protected function setUp()
    {
        $this->MimeMessage = new Message('1.0', 'multipart/mixed');
    }

    protected function tearDown()
    {
        $this->MimeMessage = NULL;
    }

    public function generateInvalidArguments()
    {
        return array(
        	 array('foo',         'multipart/mixed')
            ,array('',            'multipart/mixed')
        	,array(false,         'multipart/mixed')
        	,array(NULL,          'multipart/mixed')
        	,array(array(),       'multipart/mixed')
        	,array(new \stdClass, 'multipart/mixed')
        );
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Check properties
        $Head = new ReflectionProperty($this->MimeMessage, '_Head');
        $Body = new ReflectionProperty($this->MimeMessage, '_Body');

        $Head->setAccessible(true);
        $Body->setAccessible(true);

        $this->assertInstanceOf('\\BLW\\Type\\MIME\\IHead', $Head->getValue($this->MimeMessage), sprintf('MimeMesage::__construct(%s, %s) Failed to set $_Head', print_r($Head->getValue($this->MimeMessage), true), '...'));
        $this->assertInstanceOf('\\BLW\\Type\\MIME\\IBody', $Body->getValue($this->MimeMessage), sprintf('MimeMesage::__construct(%s, %s) Failed to set $_Body', '...', print_r($Head->getValue($this->MimeMessage), true)));

        # Invalid Arguments
        foreach ($this->generateInvalidArguments() as $Arguments) {
            list($Version, $Section) = $Arguments;

            try {
                new Message($Version, $Section);
                $this->fail('Failed to generate error with invalid arguments:'. print_r($Arguments, true));
            }

            catch (InvalidArgumentException $e) {}

            catch (\PHPUnit_Framework_Error $e) {}
        }
    }

    /**
     * @depends test_construct
     * @covers ::getHeader
     */
    public function test_getHeader()
    {
        $this->assertInstanceof('\\BLW\\Type\\MIME\\IHead', $this->MimeMessage->getHeader(), 'IMimeMessage::getHeader() returned an invalid result');
    }

    /**
     * @depends test_construct
     * @covers ::getBody
     */
    public function test_getBody()
    {
        $this->assertInstanceof('\\BLW\\Type\\MIME\\IBody', $this->MimeMessage->getBody(), 'IMimeMessage::getBody() returned an invalid result');
    }

    /**
     * @depends test_construct
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertNotEmpty(@strval($this->MimeMessage), '(string) IMimeMessage should not be empty');
    }

    /**
     * @covers ::getFactoryMethods
     */
    public function test_getFactoryMethods()
    {
        $Expected = array(
        	 new ReflectionMethod($this->MimeMessage, 'createMessage')
            ,new ReflectionMethod($this->MimeMessage, 'createFromString')
        );

        $this->assertEquals($Expected, $this->MimeMessage->getFactoryMethods(), 'MimeMessage::getFactoryMethods() returned an invalid value');
    }

    /**
     * covers ::createAddressHeader
     */
    public function test_createAddressHeader()
    {
        if (!is_callable('imap_rfc822_parse_adrlist')) return true;

        $Container   = new GenericContainer(IMessage::EMAIL);
        $Container[] = new GenericEmailAddress('test@example.com', 'Test User');
        $Container[] = new GenericEmailAddress('admin@example.com');
        $Expected    = new CC($Container);

        $this->assertEquals($Expected, $this->MimeMessage->createAddressHeader('CC', 'Test User <test@example.com>, admin@example.com'), 'MimeMessage::createAddressHeader() Returned an invalid value');

        # Invalid arguments
        try {
            $this->MimeMessage->createAddressHeader(null, 'test@example.com');
            $this->fail('Failed to generate exception with invalid arguments');
        }

        catch (InvalidArgumentException $e) {}

        try {
            $this->MimeMessage->createAddressHeader('To', null);
            $this->fail('Failed to generate exception with invalid arguments');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @depends test_construct
     * @depends test_createAddressHeader
     * @covers ::createMessage
     */
    public function test_createMessage()
    {
        # Unimplemented
        try {
            $this->MimeMessage->createMessage();
        }

        catch (\RuntimeException $e) {}
    }

    /**
     * @depends test_toString
     * @depends test_construct
     * @covers ::createFromString
     * @covers ::_createHeader
     */
    public function test_createFromString()
    {
        if (!is_callable('imap_rfc822_parse_headers')) return;

        $Container   = new GenericContainer('object');
        $Container[] = new GenericEmailAddress('foo@example.com');

        $this->MimeMessage->getHeader()->append(new Generic('Foo', 'bar1'));
        $this->MimeMessage->getHeader()->append(new Generic('Foo', 'bar2'));
        $this->MimeMessage->getHeader()->offsetSet('CC', new CC($Container));

        # Unimplemented
        try {
            $this->assertEquals($this->MimeMessage, $this->MimeMessage->createFromString(strval($this->MimeMessage)), 'MimeMessage::createFromString((string) MimeMessage should equal MimeMessage');
        }

        catch (\RuntimeException $e) {}

        # Invalid values
        try {
            $this->MimeMessage->createFromString(null);
            $this->fail('Failed to generate exception with invalid arguments');
        }

        catch (InvalidArgumentException $e) {}
    }
}
