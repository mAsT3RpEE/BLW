<?php
/**
 * ContentIDTest.php | Mar 10, 2014
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
use BLW\Model\MIME\ContentID;


/**
 * Tests BLW Library MIME Contetn-ID header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\ContentID
 */
class ContentIDTest extends \PHPUnit_Framework_TestCase
{
    const CONTENT_ID = '[\x2d\x2e\x30-\x39\x41-\x5a\x5f\x61-\x7a]{8,}\x40[\x21\x23-\x27\x2b\x2d\x2e\x30-\x39\x41-\x5a\x5f\x61-\x7a]+';

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
        $this->Header      = new ContentID;
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
        	 array('', self::CONTENT_ID)
            ,array('root', self::CONTENT_ID)
            ,array('1234567', self::CONTENT_ID)
        	,array(false, self::CONTENT_ID)
        	,array(NULL, self::CONTENT_ID)
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

            $this->assertEquals($Expected, $this->Header->parseID($Original), 'ContentID::parseID() returned an invalid format');
        }

        # Invalid id
        foreach($this->generateInvalidIDs() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertRegExp("!^$Expected$!", $this->Header->parseID($Original), 'ContentID::parseID() returned an invalid format');
        }
    }

    /**
     * @covers ::getID
     */
    public function test_getID()
    {
        $Regex = sprintf('!^%s$!', self::CONTENT_ID);

        $this->assertRegExp($Regex, $this->Header->getID(), 'ContentID::getID() returned an invalid format');
    }

    /**
     * @depends test_getID
     * @covers ::getURL
     */
    public function test_getURL()
    {
        $Expected = sprintf('cid:%s', $this->Header->getID());

        $this->assertEquals($Expected, $this->Header->getURL(), 'ContentID::getURL() returned an invalid format');
    }

    /**
     * @depends test_parseID
     * @covers ::__construct
     */
    public function test_construct()
    {
        $Regex = sprintf('!<%s>!', self::CONTENT_ID);

        # Check params
        $this->assertEquals('Content-ID', $this->Properties['Type']->getValue($this->Header), 'ContentType::__construct() failed to set $_Type');
        $this->assertRegExp($Regex, $this->Properties['Value']->getValue($this->Header), 'ContentType::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            new ContentID(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertRegExp(sprintf("!Content-ID: <%s>\r\n!", self::CONTENT_ID), @strval($this->Header), 'ContentType::__toSting() returned an invalid format');
    }
}
