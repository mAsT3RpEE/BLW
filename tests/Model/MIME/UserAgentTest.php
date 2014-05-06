<?php
/**
 * UserAgentTest.php | Mar 10, 2014
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
use BLW\Model\MIME\UserAgent;


/**
 * Tests BLW Library MIME Contetn-UserAgent header.
 * @package BLW\MIME
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\UserAgent
 */
class UserAgentTest extends \PHPUnit_Framework_TestCase
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
        $this->Header      = new UserAgent('Test UserAgent');
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

    public function generateValidUserAgents()
    {
        return array(
        	 array('test', 'test')
        	,array('test with space', 'test with space')
        	,array('`~!@#$%^&*()-_=+[{]}\\|;:\',<.>/?', '`~!@#$%^&*()-_=+[{]}\\|;:\',<.>/?')
            ,array('"""""still okay"""""""', 'still okay')
        );
    }

    public function generateInvalidUserAgents()
    {
        return array(
        	 array('', '')
            ,array('"""', '')
        	,array(false, '')
        );
    }

    /**
     * @covers ::parseUserAgent
     */
    public function test_parseUserAgent()
    {
        # Valid UserAgent
        foreach($this->generateValidUserAgents() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseUserAgent($Original), 'UserAgent::parseUserAgent() returned an invalid format');
        }

        # Invalid UserAgent
        foreach($this->generateInvalidUserAgents() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseUserAgent($Original), 'UserAgent::parseUserAgent() returned an invalid format');
        }
    }

    /**
     * @depends test_parseUserAgent
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Check params
        $this->assertEquals('User-Agent', $this->Properties['Type']->getValue($this->Header), 'UserAgent::__construct() failed to set $_Type');
        $this->assertEquals('Test UserAgent', $this->Properties['Value']->getValue($this->Header), 'UserAgent::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            new UserAgent(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("User-Agent: Test UserAgent\r\n", @strval($this->Header), 'UserAgent::__toSting() returned an invalid format');
    }
}