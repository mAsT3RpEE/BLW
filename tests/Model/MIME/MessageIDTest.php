<?php
/**
 * MessageIDTest.php | Mar 10, 2014
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
use BLW\Model\MIME\MessageID;


/**
 * Tests BLW Library MIME Contetn-ID header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\MessageID
 */
class MessageIDTest extends \PHPUnit_Framework_TestCase
{
    const MESSAGE_ID = '[\x2d\x2e\x30-\x39\x41-\x5a\x5f\x61-\x7a]{8,}\x40[\x21\x23-\x27\x2b\x2d\x2e\x30-\x39\x41-\x5a\x5f\x61-\x7a]+';

    /**
     * @var \BLW\Model\MIME\MessageID
     */
    protected $Header = NULL;

    /**
     * @var \ReflectionProperty[]
     */
    protected $Properties = array();

    protected function setUp()
    {
        $this->Header      = new MessageID;
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

    public function generateValidIDs()
    {
        return array(
        	 array('abcdefgh@example.com', 'abcdefgh@example.com')
        	,array('12345678@example.com', '12345678@example.com')
        	,array('"abcd.1234"@example.com', '"abcd.1234"@example.com')
            ,array(';;;ab_cd.12-34@exam-pl_e.co.uk;;;', 'ab_cd.12-34@exam-pl_e.co.uk')
        );
    }

    public function generateInvalidIDs()
    {
        return array(
        	 array('', self::MESSAGE_ID)
            ,array('root', self::MESSAGE_ID)
            ,array('1234567', self::MESSAGE_ID)
        	,array(false, self::MESSAGE_ID)
        	,array(NULL, self::MESSAGE_ID)
        );
    }

    /**
     * @covers ::parseID
     */
    public function test_parseID()
    {
        # Valid id
        foreach($this->generateValidIDs() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseID($Original), 'MessageID::parseID() returned an invalid format');
        }

        # Invalid id
        foreach($this->generateInvalidIDs() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertRegExp("!^$Expected$!", $this->Header->parseID($Original), 'MessageID::parseID() returned an invalid format');
        }
    }

    /**
     * @covers ::getID
     */
    public function test_getID()
    {
        $Regex = sprintf('!^%s$!', self::MESSAGE_ID);

        $this->assertRegExp($Regex, $this->Header->getID(), 'MessageID::getID() returned an invalid format');
    }

    /**
     * @depends test_getID
     * @covers ::getURL
     */
    public function test_getURL()
    {
        $Expected = sprintf('mid:%s', $this->Header->getID());

        $this->assertEquals($Expected, $this->Header->getURL(), 'MessageID::getURL() returned an invalid format');
    }

    /**
     * @depends test_parseID
     * @covers ::__construct
     */
    public function test_construct()
    {
        $Regex = sprintf('!<%s>!', self::MESSAGE_ID);

        # Check params
        $this->assertEquals('Message-ID', $this->Properties['Type']->getValue($this->Header), 'MessageID::__construct() failed to set $_Type');
        $this->assertRegExp($Regex, $this->Properties['Value']->getValue($this->Header), 'MessageID::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            new MessageID(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertRegExp(sprintf("!Message-ID: <%s>\r\n!", self::MESSAGE_ID), @strval($this->Header), 'MessageID::__toSting() returned an invalid format');
    }
}
