<?php
/**
 * AddressHandlerTest.php | Feb 12, 2014
 *
 * @filesource
 * @license MIT
 * @copyright Copyright (c) 2013-2018, mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * @package BLW\Core
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type\Mail;

use BLW\Type\IDataMapper;

use BLW\Model\GenericContainer;
use BLW\Model\GenericEmailAddress;

/**
 * Tests BLW Library AddressHandler type.
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 *  @coversDefaultClass \BLW\Type\Mail\AAddressHandler
 */
class AddressHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\Mail\IAddressHandler
     */
    protected $AddressHandler = NULL;

    protected function setUp()
    {
        $this->AddressHandler = $this->getMockForAbstractClass('\\BLW\\Type\\Mail\\AAddressHandler');

        $Properties     = array(
            'To'       => new \ReflectionProperty($this->AddressHandler, '_To')
            ,'From'    => new \ReflectionProperty($this->AddressHandler, '_From')
            ,'ReplyTo' => new \ReflectionProperty($this->AddressHandler, '_ReplyTo')
            ,'CC'      => new \ReflectionProperty($this->AddressHandler, '_CC')
            ,'BCC'     => new \ReflectionProperty($this->AddressHandler, '_BCC')
        );

        $Properties['To']->setAccessible(true);
        $Properties['To']->setValue($this->AddressHandler, new \BLW\Model\GenericContainer());
        $Properties['From']->setAccessible(true);
        $Properties['From']->setValue($this->AddressHandler, new \BLW\Model\GenericContainer());
        $Properties['ReplyTo']->setAccessible(true);
        $Properties['ReplyTo']->setValue($this->AddressHandler, new \BLW\Model\GenericContainer());
        $Properties['CC']->setAccessible(true);
        $Properties['CC']->setValue($this->AddressHandler, new \BLW\Model\GenericContainer());
        $Properties['BCC']->setAccessible(true);
        $Properties['BCC']->setValue($this->AddressHandler, new \BLW\Model\GenericContainer());
    }

    protected function tearDown()
    {
        $this->AddressHandler = NULL;
    }

    /**
     * @covers ::addTo
     */
    public function test_addTo()
    {
        $Expected = new GenericEmailAddress('test@example.com', 'Test User');

        # Valid Email
        $this->assertEquals(IDataMapper::UPDATED, $this->AddressHandler->addTo($Expected), 'IAddressHandler::addTo() should return IDataMapper::UPDATED');
        $this->assertSame($Expected, $this->readAttribute($this->AddressHandler, '_To')->offsetGet(0), 'IAddressHandler::$_To was not updated by IAddressHandler::AddTo()');

        # Invalid Email
        $this->assertEquals(IDataMapper::INVALID, $this->AddressHandler->addTo(new GenericEmailAddress('Invalid')), 'IAddressHandler::addTo() should return IDataMapper::INVALID');
    }

    /**
     * @depends test_addTo
     * @covers ::getTo
     */
    public function test_getTo()
    {
        $Email    = new GenericEmailAddress('test@example.com', 'Test User');
        $Expected = new GenericContainer;
        $Expected->append($Email);

        $this->assertEquals(IDataMapper::UPDATED, $this->AddressHandler->addTo($Email), 'IAddressHandler::addTo() should return IDataMapper::UPDATED');
        $this->assertInstanceOf('\\BLW\\Type\\IContainer', $this->AddressHandler->getTo(), 'IAddressHandler::getTo() should return an instance of IContainer');
        $this->assertEquals($Expected, $this->AddressHandler->getTo(), 'IAddressHandler::getTo() returned an invalid IContainer');
    }

    /**
     * @covers ::addFrom
     */
    public function test_addFrom()
    {
        $Expected = new GenericEmailAddress('test@example.com', 'Test User');

        # Valid Email
        $this->assertEquals(IDataMapper::UPDATED, $this->AddressHandler->addFrom($Expected), 'IAddressHandler::addFrom() should return IDataMapper::UPDATED');
        $this->assertSame($Expected, $this->readAttribute($this->AddressHandler, '_From')->offsetGet(0), 'IAddressHandler::$_From was not updated by IAddressHandler::AddFrom()');

        # Invalid Email
        $this->assertEquals(IDataMapper::INVALID, $this->AddressHandler->addFrom(new GenericEmailAddress('Invalid')), 'IAddressHandler::addFrom() should return IDataMapper::INVALID');
    }

    /**
     * @depends test_addFrom
     * @covers ::getFrom
     */
    public function test_getFrom()
    {
        $Email    = new GenericEmailAddress('test@example.com', 'Test User');
        $Expected = new GenericContainer;
        $Expected->append($Email);

        $this->assertEquals(IDataMapper::UPDATED, $this->AddressHandler->addFrom($Email), 'IAddressHandler::addFrom() should return IDataMapper::UPDATED');
        $this->assertInstanceOf('\\BLW\\Type\\IContainer', $this->AddressHandler->getFrom(), 'IAddressHandler::getFrom() should return an instance of IContainer');
        $this->assertEquals($Expected, $this->AddressHandler->getFrom(), 'IAddressHandler::getFrom() returned an invalid IContainer');
    }

    /**
     * @covers ::addReplyTo
     */
    public function test_addReplyTo()
    {
        $Expected = new GenericEmailAddress('test@example.com', 'Test User');

        # Valid Email
        $this->assertEquals(IDataMapper::UPDATED, $this->AddressHandler->addReplyTo($Expected), 'IAddressHandler::addReplyTo() should return IDataMapper::UPDATED');
        $this->assertSame($Expected, $this->readAttribute($this->AddressHandler, '_ReplyTo')->offsetGet(0), 'IAddressHandler::$_ReplyTo was not updated by IAddressHandler::AddReplyTo()');

        # Invalid Email
        $this->assertEquals(IDataMapper::INVALID, $this->AddressHandler->addReplyTo(new GenericEmailAddress('Invalid')), 'IAddressHandler::addReplyTo() should return IDataMapper::INVALID');
    }

    /**
     * @depends test_addReplyTo
     * @covers ::getReplyTo
     */
    public function test_getReplyTo()
    {
        $Email    = new GenericEmailAddress('test@example.com', 'Test User');
        $Expected = new GenericContainer;
        $Expected->append($Email);

        $this->assertEquals(IDataMapper::UPDATED, $this->AddressHandler->addReplyTo($Email), 'IAddressHandler::addReplyTo() should return IDataMapper::UPDATED');
        $this->assertInstanceOf('\\BLW\\Type\\IContainer', $this->AddressHandler->getReplyTo(), 'IAddressHandler::getReplyTo() should return an instance of IContainer');
        $this->assertEquals($Expected, $this->AddressHandler->getReplyTo(), 'IAddressHandler::getReplyTo() returned an invalid IContainer');
    }

    /**
     * @covers ::addCC
     */
    public function test_addCC()
    {
        $Expected = new GenericEmailAddress('test@example.com', 'Test User');

        # Valid Email
        $this->assertEquals(IDataMapper::UPDATED, $this->AddressHandler->addCC($Expected), 'IAddressHandler::addCC() should return IDataMapper::UPDATED');
        $this->assertSame($Expected, $this->readAttribute($this->AddressHandler, '_CC')->offsetGet(0), 'IAddressHandler::$_CC was not updated by IAddressHandler::AddCC()');

        # Invalid Email
        $this->assertEquals(IDataMapper::INVALID, $this->AddressHandler->addCC(new GenericEmailAddress('Invalid')), 'IAddressHandler::addCC() should return IDataMapper::INVALID');
    }

    /**
     * @depends test_addCC
     * @covers ::getCC
     */
    public function test_getCC()
    {
        $Email    = new GenericEmailAddress('test@example.com', 'Test User');
        $Expected = new GenericContainer;
        $Expected->append($Email);

        $this->assertEquals(IDataMapper::UPDATED, $this->AddressHandler->addCC($Email), 'IAddressHandler::addCC() should return IDataMapper::UPDATED');
        $this->assertInstanceOf('\\BLW\\Type\\IContainer', $this->AddressHandler->getCC(), 'IAddressHandler::getCC() should return an instance of IContainer');
        $this->assertEquals($Expected, $this->AddressHandler->getCC(), 'IAddressHandler::getCC() returned an invalid IContainer');
    }

    /**
     * @covers ::addBCC
     */
    public function test_addBCC()
    {
        $Expected = new GenericEmailAddress('test@example.com', 'Test User');

        # Valid Email
        $this->assertEquals(IDataMapper::UPDATED, $this->AddressHandler->addBCC($Expected), 'IAddressHandler::addBCC() should return IDataMapper::UPDATED');
        $this->assertSame($Expected, $this->readAttribute($this->AddressHandler, '_BCC')->offsetGet(0), 'IAddressHandler::$_BCC was not updated by IAddressHandler::AddBCC()');

        # Invalid Email
        $this->assertEquals(IDataMapper::INVALID, $this->AddressHandler->addBCC(new GenericEmailAddress('Invalid')), 'IAddressHandler::addBCC() should return IDataMapper::INVALID');
    }

    /**
     * @depends test_addBCC
     * @covers ::getBCC
     */
    public function test_getBCC()
    {
        $Email    = new GenericEmailAddress('test@example.com', 'Test User');
        $Expected = new GenericContainer;
        $Expected->append($Email);

        $this->assertEquals(IDataMapper::UPDATED, $this->AddressHandler->addBCC($Email), 'IAddressHandler::addBCC() should return IDataMapper::UPDATED');
        $this->assertInstanceOf('\\BLW\\Type\\IContainer', $this->AddressHandler->getBCC(), 'IAddressHandler::getBCC() should return an instance of IContainer');
        $this->assertEquals($Expected, $this->AddressHandler->getBCC(), 'IAddressHandler::getBCC() returned an invalid IContainer');
    }
}
