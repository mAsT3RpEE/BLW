<?php
/**
 * ContentMD5Test.php | Mar 10, 2014
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
use BLW\Model\MIME\ContentMD5;


/**
 * Tests BLW Library MIME Content-MD5 header.
 * @package BLW\MIME
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\ContentMD5
 */
class ContentMD5Test extends \PHPUnit_Framework_TestCase
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
        $this->Header      = new ContentMD5('9dd4e461268c8034f5c8564e155c67a6');
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

    public function generateValidMD5s()
    {
        return array(
        	 array('acbd18db4cc2f85cedef654fccc4a4d8', 'acbd18db4cc2f85cedef654fccc4a4d8')
            ,array('37b51d194a7513e45b56f6524f2d51f2', '37b51d194a7513e45b56f6524f2d51f2')
        );
    }

    public function generateInvalidMD5s()
    {
        return array(
        	 array('foo', 'd41d8cd98f00b204e9800998ecf8427e')
        	,array(false, 'd41d8cd98f00b204e9800998ecf8427e')
            ,array(new \stdClass, 'd41d8cd98f00b204e9800998ecf8427e')
            ,array(array(), 'd41d8cd98f00b204e9800998ecf8427e')
        );
    }

    /**
     * @covers ::parseMD5
     */
    public function test_parseMD5()
    {
        # Valid MD5
        foreach($this->generateValidMD5s() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseMD5($Original), 'ContentMD5::parseMD5() returned an invalid format');
        }

        # Invalid MD5
        foreach($this->generateInvalidMD5s() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseMD5($Original), 'ContentMD5::parseMD5() returned an invalid format');
        }
    }

    /**
     * @depends test_parseMD5
     * @covers ::__construct
     */
    public function test_construct()
    {
        $this->Header = new ContentMD5('9dd4e461268c8034f5c8564e155c67a6');

        # Check params
        $this->assertEquals('Content-MD5', $this->Properties['Type']->getValue($this->Header), 'ContentMD5::__construct() failed to set $_Type');
        $this->assertEquals('9dd4e461268c8034f5c8564e155c67a6', $this->Properties['Value']->getValue($this->Header), 'ContentMD5::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            new ContentMD5(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("Content-MD5: 9dd4e461268c8034f5c8564e155c67a6\r\n", @strval($this->Header), 'ContentMD5::__toSting() returned an invalid format');
    }
}