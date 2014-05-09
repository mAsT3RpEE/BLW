<?php
/**
 * MessageTest.php | Jan 14, 2014
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
namespace BLW\Model\Mail;

use ReflectionProperty;

use BLW\Type\IDataMapper;
use BLW\Type\Mail\ITransport;

use BLW\Model\GenericEmailAddress;
use BLW\Model\GenericFile;
use BLW\Model\GenericContainer;
use BLW\Model\FileException;
use BLW\Model\ClassException;
use BLW\Model\Mail\MIME\Message as MIMEMessage;
use BLW\Model\Mail\GenericMessage;
use BLW\Model\Mail\Transport\Mock as Transport;


/**
 * Tests Message Module type.
 * @package BLW\Mail
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mail\GenericMessage
 */
class MessageTest extends \BLW\Type\Mail\AddressHandlerTest
{
    const FILE   = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVQYV2NgYAAAAAMAAWgmWQ0AAAAASUVORK5CYII=';

    /**
     * @var \BLW\Type\Mail\IMessage
     */
    protected $Message = NULL;

    /**
     * @var \ReflectionProperty[]
     */
    protected $Properties = NULL;

    protected function setUp()
    {
        $this->Message        = new GenericMessage;
        $this->AddressHandler = $this->Message;
    }

    protected function tearDown()
    {
        $this->Message        = NULL;
        $this->AddressHandler = NULL;
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        $this->assertInstanceOf('\\BLW\\Type\\IContainer', $this->Message->getTo(), 'IMesage::getTo() should return an instance of IContainer');
        $this->assertInstanceOf('\\BLW\\Type\\IContainer', $this->Message->getFrom(), 'IMesage::getFrom() should return an instance of IContainer');
        $this->assertInstanceOf('\\BLW\\Type\\IContainer', $this->Message->getReplyTo(), 'IMesage::getReplyTo() should return an instance of IContainer');
        $this->assertInstanceOf('\\BLW\\Type\\IContainer', $this->Message->getCC(), 'IMesage::getCC() should return an instance of IContainer');
        $this->assertInstanceOf('\\BLW\\Type\\IContainer', $this->Message->getBCC(), 'IMesage::getBCC() should return an instance of IContainer');
    }

    /**
     * @covers ::getHTML
     */
    public function test_getHTML()
    {
        $this->assertNull($this->Message->getHTML(), 'IMessage::getHTML() should initially be NULL');
    }

    /**
     * @depends test_getHTML
     * @covers ::setHTML
     */
    public function test_setHTML()
    {
        $Document = new \DOMDocument('1.0');

        # Valid HTML
        $this->assertEquals(IDataMapper::UPDATED, $this->Message->setHTML($Document), 'IMessage::setHTML() should return IDataMapper::UPDATED');
        $this->assertSame($Document, $this->Message->getHTML(), 'IMessage::setHTML() did not update message as expected');

        # Invalid HTML
        $this->assertEquals(IDataMapper::INVALID, $this->Message->setHTML(NULL), 'IMessage::setHTML() should return IDataMapper::INVALID');
    }

    /**
     * @covers ::getText
     */
    public function test_getText()
    {
        $this->assertSame('', $this->Message->getText(), 'IMessage::getText() should return an empty string');
    }

    /**
     * @depends test_getText
     * @covers ::setText
     */
    public function test_setText()
    {
        $Document = 'this is a test';

        # Valid HTML
        $this->assertEquals(IDataMapper::UPDATED, $this->Message->setText($Document), 'IMessage::setText() should return IDataMapper::UPDATED');
        $this->assertSame($Document, $this->Message->getText(), 'IMessage::setText() did not update message as expected');

        # Invalid HTML
        $this->assertEquals(IDataMapper::INVALID, $this->Message->setText(NULL), 'IMessage::setText() should return IDataMapper::INVALID');
    }

    /**
     * @covers ::getSubject
     */
    public function test_getSubject()
    {
        $this->assertSame('', $this->Message->getSubject(), 'IMessage::getSubject() should return an empty string');
    }

    public function generateSubjects()
    {
        return array(
        	 array('pre-(-post','pre--post')
        	,array('pre-)-post','pre--post')
        	,array('pre-<-post','pre--post')
        	,array('pre->-post','pre--post')
        	,array('pre-@-post','pre--post')
        	,array('pre-,-post','pre--post')
        	,array('pre-;-post','pre--post')
        	,array('pre-:-post','pre--post')
        	,array('pre-\-post','pre--post')
        	,array('pre-"-post','pre--post')
        	,array('pre-/-post','pre--post')
        	,array('pre-[-post','pre--post')
        	,array('pre-]-post','pre--post')
        	,array('pre-?-post','pre--post')
        	,array('pre-=-post','pre--post')
        );
    }
    /**
     * @depends test_getSubject
     * @covers ::setSubject
     */
    public function test_setSubject()
    {
        $Subject = 'Test Subject';
        $Object  = new \SplFileInfo(__FILE__);

        # Valid Subject
        $this->assertEquals(IDataMapper::UPDATED, $this->Message->setSubject($Subject), 'IMessage::setSubject() should return IDataMapper::UPDATED');
        $this->assertSame($Subject, $this->Message->getSubject(), 'IMessage::setSubject() did not update message as expected');

        # Invalid Subject
        $this->assertEquals(IDataMapper::INVALID, $this->Message->setSubject(NULL), 'IMessage::setText() should return IDataMapper::INVALID');

        # Subject Filtering
        foreach($this->generateSubjects() as $Arguments) {

            list($Original, $Filtered) = $Arguments;

            $this->assertEquals(IDataMapper::UPDATED, $this->Message->setSubject($Original), 'IMessage::setSubject() should return IDataMapper::UPDATED');
            $this->assertEquals($Filtered, $this->Message->getSubject(), 'IMessage::setSubject() did not filter subject as expected');
        }
    }

    /**
     * @covers ::addAttachment
     */
    public function test_addAttachment()
    {
        $Expected = new GenericFile(self::FILE);

        # ReadableFile
        $this->assertEquals(IDataMapper::UPDATED, $this->Message->addAttachment($Expected), 'IMessage::addAttachment() should return IDataMapper::UPDATED');
        $this->assertSame($Expected, $this->readAttribute($this->Message, '_Attachments')->offsetGet(0), 'IMessage::$_Attachments was not updated by IMessage::addAttachments()');

        # Unreadable file
        try {
            $this->Message->addAttachment(new GenericFile('z:\\undefined\\!!!'));
            $this->fail('Failed to generate exception on invalid file');
        }

        catch (FileException $e) {}
    }

    /**
     * @depends test_addAttachment
     * @covers ::getAttachments
     */
    public function test_getAttachments()
    {
        $File     = new GenericFile(self::FILE);
        $Expected = new GenericContainer;
        $Expected->append($File);

        $this->assertEquals(IDataMapper::UPDATED, $this->Message->addAttachment($File), 'IMessage::addAttachment() should return IDataMapper::UPDATED');
        $this->assertInstanceOf('\\BLW\\Type\\IContainer', $this->Message->getAttachments(), 'IMessage::getAttachments() should return an instance of IContainer');
        $this->assertEquals($Expected, $this->Message->getAttachments(), 'IMessage::getAttachments() returned an invalid IContainer');
    }

    /**
     * @covers ::InlineAttachment
     */
    public function test_InlineAttachment()
    {
        $File  = new GenericFile(self::FILE);
        $Regex = '!\\x40[\\x30-\\x39\\x41-\\x5a\\x61\\x7a]+!';

        # ReadableFile
        $this->assertRegExp($Regex, $this->Message->inlineAttachment($File), 'IMessage::inlineAttachment() should return string of format `@[\w]+');
        $this->assertTrue(!empty($File->UniqueID), 'IFile::$UniqueID should exist and should not be empty');
        $this->assertSame($File, $this->readAttribute($this->Message, '_InlineAttachments')->offsetGet(0), 'IMessage::$_Attachments was not updated by IMessage::addAttachments()');

        # Unreadable file
        try {
            $this->Message->InlineAttachment(new GenericFile('X:\\undefined'));
            $this->fail('Failed to generate notice on invalid file');
        }

        catch(FileException $e) {}
    }

    /**
     * @depends test_InlineAttachment
     * @covers ::getInlineAttachments
     */
    public function test_getInlineAttachments()
    {
        $File     = new GenericFile(self::FILE);
        $Expected = new GenericContainer;
        $Regex    = '!\\x40[\\x30-\\x39\\x41-\\x5a\\x61\\x7a]+!';
        $Expected->append($File);

        $this->assertRegExp($Regex, $this->Message->inlineAttachment($File), 'IMessage::inlineAttachment() should return string of format `@[\w]+');
        $this->assertInstanceOf('\\BLW\\Type\\IContainer', $this->Message->getInlineAttachments(), 'IMessage::getInlineAttachments() should return an instance of IContainer');
        $this->assertEquals($Expected, $this->Message->getInlineAttachments(), 'IMessage::getInlineAttachments() returned an invalid IContainer');
    }

    public function generateMessage()
    {
        $this->Message->setParent   ($this->getMockForAbstractClass('\\BLW\\Type\\IObject'));
        $this->Message->setMediator ($this->getMockForAbstractClass('\\BLW\\Type\\IMediator'));
        $this->Message->addTo       (new GenericEmailAddress('receiver@example.com', 'Receiver'));
        $this->Message->addFrom     (new GenericEmailAddress('sender@example.com', 'Sender'));
        $this->Message->addReplyTo  (new GenericEmailAddress('sender@example.com', 'Sender'));
        $this->Message->addCC       (new GenericEmailAddress('copy@example.com', 'CC'));
        $this->Message->addBCC      (new GenericEmailAddress('shadowcopy@example.com', 'BCC'));
        $this->Message->setSubject  ('Test Subject');
        $this->Message->setHTML     (new \DOMDocument('1.0'));
        $this->Message->setText     ('Text Body');

        $this->Message->addAttachment(new GenericFile(self::FILE));
        $this->Message->InlineAttachment(new GenericFile(self::FILE));
    }

    /**
     * @depends test_addAttachment
     * @depends test_InlineAttachment
     * @depends test_setHTML
     * @depends test_setText
     * @depends test_setSubject
     * @covers ::__get
     */
    public function test_get()
    {
        $this->generateMessage();

        # Make property readable / writable
	    $Status = new ReflectionProperty($this->Message, '_Status');
	    $Status->setAccessible(true);

	    # Status
        $this->assertSame($this->Message->Status, $Status->getValue($this->Message), 'IMessage::$Status should equal IMessage::_Status');

	    # Serializer
	    $this->assertSame($this->Message->Serializer, $this->Message->getSerializer(), 'IMessage::$Serializer should equal IMessage::getSerializer()');

	    # Parent
        $this->assertInstanceOf('\\BLW\\Type\\IObject', $this->Message->Parent, 'IMessage::$Parent should be an instance of IObject');

        # ID
        $this->assertSame($this->Message->ID, $this->Message->getID(), 'IMessage::$ID should equal IMessage::getID()');

        # Mediator
        $this->assertInstanceOf('\\BLW\\Type\\IMediator', $this->Message->Mediator, 'IMessage::$Mediator should be an instance of IMediator');

        # MediatorID
        $this->assertNotEmpty($this->Message->MediatorID, 'IMessage::$MediatorID should not be empty');

        # To
        $this->assertInstanceOf('\\BLW\\Type\\IContainer', $this->Message->To, 'IMessage::$To should return an instance of IContainer');
        $this->assertCount(1, $this->Message->To, 'IMessage::$To should return an instance of IContainer with 1 item');

        # From
        $this->assertInstanceOf('\\BLW\\Type\\IContainer', $this->Message->From, 'IMessage::$From should return an instance of IContainer');
        $this->assertCount(1, $this->Message->From, 'IMessage::$From should return an instance of IContainer with 1 item');

        # ReplyTo
        $this->assertInstanceOf('\\BLW\\Type\\IContainer', $this->Message->ReplyTo, 'IMessage::$ReplyTo should return an instance of IContainer');
        $this->assertCount(1, $this->Message->ReplyTo, 'IMessage::$ReplyTo should return an instance of IContainer with 1 item');

        # CC
        $this->assertInstanceOf('\\BLW\\Type\\IContainer', $this->Message->CC, 'IMessage::$CC should return an instance of IContainer');
        $this->assertCount(1, $this->Message->CC, 'IMessage::$CC should return an instance of IContainer with 1 item');

        # BCC
        $this->assertInstanceOf('\\BLW\\Type\\IContainer', $this->Message->BCC, 'IMessage::$BCC should return an instance of IContainer');
        $this->assertCount(1, $this->Message->BCC, 'IMessage::$BCC should return an instance of IContainer with 1 item');

        # HTML
        $this->assertInstanceOf('DOMDocument', $this->Message->HTML, 'IMessage::$HTML should be an instance of DOMDocument');

        # Text
        $this->assertEquals('Text Body', $this->Message->Text, 'IMessage::$Text should equal `Text Body`');

        # Subject
        $this->assertEquals('Test Subject', $this->Message->Subject, 'IMessage::$Subject should equal `Test Subject`');

        # Test undefined property
        try {
            $this->Message->undefined;
            $this->fail('Failed to generate notice on undefined property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Undefined property', $e->getMessage(), 'Message::$undefined is undefined and should raise a notice');
        }

        @$this->Message->undefined;
    }

    /**
     * @depends test_addAttachment
     * @depends test_InlineAttachment
     * @depends test_setHTML
     * @depends test_setText
     * @depends test_setSubject
     * @covers ::__isset
     */
    public function test_isset()
    {
        $this->generateMessage();

        # Status
        $this->assertTrue(isset($this->Message->Serializer), 'IObject::$Status should exist');

	    # Serializer
	    $this->assertTrue(isset($this->Message->Serializer), 'IObject::$Serializer should exist');

	    # Parent
        $this->assertTrue(isset($this->Message->Parent), 'IObject::$Parent should not exist');

	    # ID
        $this->assertTrue(isset($this->Message->ID), 'IObject::$ID should exist');

        # Mediator
        $this->assertTrue(isset($this->Message->Mediator), 'IMessage::$Mediator should exist');

        # MediatorID
        $this->assertTrue(isset($this->Message->MediatorID), 'IMessage::$MediatorID should exist');

        # To
        $this->assertTrue(isset($this->Message->To), 'IMessage::$To should exist');

        # From
        $this->assertTrue(isset($this->Message->From), 'IMessage::$From should exist');

        # ReplyTo
        $this->assertTrue(isset($this->Message->ReplyTo), 'IMessage::$ReplyTo should exist');

        # CC
        $this->assertTrue(isset($this->Message->CC), 'IMessage::$CC should exist');

        # BCC
        $this->assertTrue(isset($this->Message->BCC), 'IMessage::$BCC should exist');

        # HTML
        $this->assertTrue(isset($this->Message->HTML), 'IMessage::$HTML should exist');

        # Text
        $this->assertTrue(isset($this->Message->Text), 'IMessage::$Text should exist');

        # Subject
        $this->assertTrue(isset($this->Message->Subject), 'IMessage::$Subject should exist');

        # Test undefined property
       $this->assertFalse(isset($this->Message->undefined), 'IObject::$undefined shouldn\'t exist');
    }

    /**
     * @depends test_get
     * @covers ::__set
     */
    public function test_set()
    {
        # Status
        try {
            $this->Message->Status = 0;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Message->Status = 0;

        # Serializer
        try {
            $this->Message->Serializer = 0;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Message->Serializer = 0;

        # Parent
        $Expected = $this->getMockForAbstractClass('\\BLW\\Type\\IObject');
        $this->Message->Parent = $Expected;
        $this->assertSame($Expected, $this->Message->Parent, 'IObject::$Parent should equal IObject::setParent()');

	    # ID
        $this->Message->ID = 'foo';
        $this->assertSame('foo', $this->Message->ID, 'IObject::$ID should equal `foo');

        # Mediator
        $Expected = $this->getMockForAbstractClass('\\BLW\\Type\\IMediator');
        $this->Message->Mediator = $Expected;
        $this->assertSame($Expected, $this->Message->Mediator, 'IMessage::$Mediator should equal IMessage::setMediator()');

        # MediatorID
        try {
            $this->Message->MediatorID = 0;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Message->MediatorID = 0;

        # To
        $this->Message->To = new GenericEmailAddress('test@example.com');
        $this->assertCount(1, $this->Message->To, 'IMessage::$To should now contain 1 email addresses');

        try {
            $this->Message->To = 'foo';
            $this->fail('Failed to generate notice on invalid value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Message->To = 'foo';

        # From
        $this->Message->From = new GenericEmailAddress('test@example.com');
        $this->assertCount(1, $this->Message->From, 'IMessage::$From should now contain 1 email addresses');

        try {
            $this->Message->From = 'foo';
            $this->fail('Failed to generate notice on invalid value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Message->From = 'foo';

        # ReplyTo
        $this->Message->ReplyTo = new GenericEmailAddress('test@example.com');
        $this->assertCount(1, $this->Message->ReplyTo, 'IMessage::$ReplyTo should now contain 1 email addresses');

        try {
            $this->Message->ReplyTo = 'foo';
            $this->fail('Failed to generate notice on invalid value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Message->ReplyTo = 'foo';

        # CC
        $this->Message->CC = new GenericEmailAddress('test@example.com');
        $this->assertCount(1, $this->Message->CC, 'IMessage::$CC should now contain 1 email addresses');

        try {
            $this->Message->CC = 'foo';
            $this->fail('Failed to generate notice on invalid value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Message->CC = 'foo';

        # BCC
        $this->Message->BCC = new GenericEmailAddress('test@example.com');
        $this->assertCount(1, $this->Message->BCC, 'IMessage::$BCC should now contain 1 email addresses');

        try {
            $this->Message->BCC = 'foo';
            $this->fail('Failed to generate notice on invalid value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Message->BCC = 'foo';

        # HTML
        $Expected = new \DOMDocument('1.0');
        $this->Message->HTML = $Expected;
        $this->assertSame($Expected, $this->Message->HTML, 'IMessage::$HTML should equal IMessage::setHTML()');

        try {
            $this->Message->HTML = 'foo';
            $this->fail('Failed to generate notice on invalid value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Message->HTML = 'foo';

        # Text
        $Expected = 'Text Body';
        $this->Message->Text = $Expected;
        $this->assertSame($Expected, $this->Message->Text, 'IMessage::$Text should equal IMessage::setText()');

        try {
            $this->Message->HTML = 1.1;
            $this->fail('Failed to generate notice on invalid value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Message->HTML = 1.1;

        # Subject
        $Expected = 'Test Subject';
        $this->Message->Subject = $Expected;
        $this->assertSame($Expected, $this->Message->Subject, 'IMessage::$Text should equal IMessage::setText()');

        try {
            $this->Message->Subject = 1.1;
            $this->fail('Failed to generate notice on invalid value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Message->Subject = 1.1;

        # Test undefined property
        $this->Message->foo = 1;
        $this->Message->foo = 100;
        $this->Message->bar = 1;

        $this->assertEquals(100, $this->Message->foo, 'IMessage::$foo should equal 1');
        $this->assertEquals(1, $this->Message->bar, 'IMessage::$bar should equal 1');
    }

    /**
     * @depends test_isset
     * @covers ::__unset
     */
    public function test_unset()
    {
        $this->generateMessage();

	    # Parent
        unset($this->Message->Parent);
        $this->assertFalse(isset($this->Message->Parent), 'IObject::$Parent should not exist');

        # Mediator
        unset($this->Message->Mediator);
        $this->assertFalse(isset($this->Message->Mediator), 'IMessage::$Mediator should not exist');

        # HTML
        unset($this->Message->HTML);
        $this->assertFalse(isset($this->Message->HTML), 'IMessage::$HTML should not exist');

        # Text
        unset($this->Message->Text);
        $this->assertEquals('', $this->Message->Text, 'IMessage::$Text should be empty');

        # Subject
        unset($this->Message->Subject);
        $this->assertEquals('', $this->Message->Subject, 'IMessage::$Text should be empty');
    }

    /**
     * @covers ::getFactoryMethods
     */
    public function test_getFactoryMethods()
    {
        $this->assertNotEmpty($this->Message->getFactoryMethods(), 'IMessage::getFactoryMethods() Returned an invalid value');
        $this->assertContainsOnlyInstancesOf('ReflectionMethod', $this->Message->getFactoryMethods(), 'IMessage::getFactoryMethods() should return an array of ReflectionMethod');
    }

    /**
     * @depends test_get
     * @depends test_set
     * @covers ::createMimeMessage
     * @covers ::_createMimeMessageWithAddresses
     * @covers ::_addBodyWithoutInlineAttachments
     * @covers ::_addBodyWithInlineAttachments
     */
    public function test_createMimeMessage()
    {
        $this->generateMessage();

        # Inline attachments
        $this->assertInstanceof('\\BLW\\Type\\MIME\\IMessage', $this->Message->createMimeMessage(), 'IMessage::createMimeMessage() returned an invalid value');

        # No Inline attachments
        $Attachments = $this->readAttribute($this->Message, '_InlineAttachments');
        unset($Attachments[0]);

        $this->assertInstanceof('\\BLW\\Type\\MIME\\IMessage', $this->Message->createMimeMessage(), 'IMessage::createMimeMessage() returned an invalid value');

        # Invalid attachment
        $Attachments[0] = new GenericFile('z:\\undefined\\!!!');

        try {
            $this->Message->createMimeMessage();
            $this->fail('Failed to generate exception with invalid arguments');
        }

        catch (FileException $e) {}

        # No recipient
        $x = $this->Message->To[0];
        unset($this->Message->To[0]);

        try {
            $this->Message->createMimeMessage();
            $this->fail('Failed to generate exception with invalid arguments');
        }

        catch (ClassException $e) {}

        # No sender
        $this->Message->To[0] = $x;
        $x = $this->Message->From[0];
        unset($this->Message->From[0]);

        try {
            $this->Message->createMimeMessage();
            $this->fail('Failed to generate exception with invalid arguments');
        }

        catch (ClassException $e) {}

        # No subject
        $this->Message->From[0] = $x;
        $x = $this->Message->Subject;
        unset($this->Message->Subject);

        try {
            $this->Message->createMimeMessage();
            $this->fail('Failed to generate exception with invalid arguments');
        }

        catch (ClassException $e) {}

        # No Body
        $this->Message->Subject = $x;
        $x = $this->Message->Text;
        unset($this->Message->Text);

        try {
            $this->Message->createMimeMessage();
            $this->fail('Failed to generate exception with invalid arguments');
        }

        catch (ClassException $e) {}
    }

    /**
     * @depends test_createMimeMessage
     * @covers ::sendWith()
     */
    public function test_sendWith()
    {
        $Transport = new Transport($this->getMockForAbstractClass('\\BLW\\Type\\IMediator'));

        $this->generateMessage();

        $this->assertEquals(ITransport::SUCCESS, $this->Message->sendWith($Transport, -1), 'IMessage::sendWidth() should return ITransport::SUCCESS');
        $this->assertInstanceof('\\BLW\\Type\\MIME\\IHead', $Transport->getMimeHead(), 'IMessage::sendWidth() produced an invalid MIME head');
        $this->assertInstanceof('\\BLW\\Type\\MIME\\IBody', $Transport->getMimeBody(), 'IMessage::sendWidth() produced an invalid MIME body');
        $this->assertCount(3, $Transport->getReceipients(), 'IMessage::sendWidth should send to 3 recipients');
    }
}
