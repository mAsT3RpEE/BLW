<?php
/**
 * TransportTest.php | Mar 24, 2014
 *
 * @filesource
 * @license MIT
 * @copyright Copyright (c) 2013-2018, mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 *    @package BLW\Mail
 *    @version 1.0.0
 *    @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Test\Type\Mail;


use ReflectionProperty;

use BLW\Type\IDataMapper;

use BLW\Type\Mail\ITransport;

use BLW\Model\GenericEmailAddress;
use BLW\Model\GenericFile;
use BLW\Model\GenericContainer;
use BLW\Model\FileException;

use BLW\Model\Mail\GenericMessage;

/**
 * Tests Transport Module type.
 * @package BLW\MIME
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\Mail\ATransport
 */
class TransportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\IMediator
     */
    protected $Mediator = NULL;

    /**
     * @var \BLW\Model\Mail\Transport\ITransport
     */
    protected $Transport = NULL;

    /**
     * @var \BLW\Model\Mail\IMessage
     */
    protected $Message = NULL;

    protected function setUp()
    {
        $this->Mediator   = $this->getMockForAbstractClass('\\BLW\\Type\\IMediator');
        $this->Transport  = $this->getMockForAbstractClass('\\BLW\\Type\\Mail\\ATransport', array($this->Mediator));
        $this->Message    = new GenericMessage();

        $this->Transport
            ->expects($this->any())
            ->method('doSend')
            ->will($this->returnValue(ITransport::SUCCESS));

        $this->Message->addTo       (new GenericEmailAddress('receiver@example.com', 'Receiver'));
        $this->Message->addFrom     (new GenericEmailAddress('sender@example.com', 'Sender'));
        $this->Message->addReplyTo  (new GenericEmailAddress('sender@example.com', 'Sender'));
        $this->Message->addCC       (new GenericEmailAddress('copy@example.com', 'CC'));
        $this->Message->addBCC      (new GenericEmailAddress('shadowcopy@example.com', 'BCC'));
        $this->Message->setSubject  ('Test Subject');
        $this->Message->setHTML     (new \DOMDocument('1.0'));
        $this->Message->setText     ('Text Body');
    }

    protected function tearDown()
    {
        $this->Transport = NULL;
        $this->Mediator  = NULL;
        $this->Message   = NULL;
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        $Mediator = new ReflectionProperty($this->Transport, '_Mediator');
        $Mediator->setAccessible(true);

        $this->assertSame($this->Mediator, $Mediator->getValue($this->Transport), 'Itransport::__costruct() failed to set Mediator');
    }

    /**
     * @covers ::parseRecipients
     */
    public function test_parseRecipients()
    {
        $Recipients = $this->Transport->parseRecipients($this->Message);

        $this->assertInstanceOf('\\BLW\\Type\\IContainer', $Recipients, 'ITransport::parseRecipients() returned an invalid value');
        $this->assertCount(3, $Recipients, 'ITransport::parseRecipients() returned an invalid value');

        foreach ($Recipients as $Address) {
            $this->assertInstanceOf('\\BLW\\Type\\IEmailAddress', $Address, 'ITransport::parseRecipients() returned an invalid value');
        }
    }

    /**
     * @depends test_construct
     * @covers ::send
     */
    public function test_send()
    {
        $this->assertEquals(ITransport::SUCCESS, $this->Transport->send($this->Message), 'ITransport::send() is supposed to return ITransport::SUCCESS');
    }

    /**
     * @depends test_construct
     * @covers ::__toString
     */
    public function test_toString()
    {
        $Class  = basename(get_class($this->Transport));

        $this->assertSame("[Mail\\Transport:$Class]", @strval($this->Transport), '(string) ITransport returned an invalid string');
    }
}
