<?php
/**
 * ContentTypeTest.php | Mar 10, 2014
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
use BLW\Model\MIME\ContentType;


/**
 * Tests BLW Library MIME Contetn-Type header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\ContentType
 */
class ContentTypeTest extends \PHPUnit_Framework_TestCase
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
        $this->Header      = new ContentType('text/plain', array('charset' => 'utf-8'));
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

    public function generateValidTypes()
    {
        return array(
        	 array('image/gif', 'image/gif')
        	,array(';;image/gif;;', 'image/gif')
        	,array('"image/gif"', 'image/gif')
            ,array('x-test/x-test', 'x-test/x-test')
        );
    }

    public function generateInvalidTypes()
    {
        return array(
        	 array('foo', 'text/plain')
            ,array('image / gif', 'text/plain')
        	,array(false, 'text/plain')
        	,array('test/plain', 'text/plain')
        );
    }

    /**
     * @covers ::parseType
     */
    public function test_parseType()
    {
        # Valid type
        foreach($this->generateValidTypes() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseType($Original), 'ContentType::parseType() returned an invalid format');
        }

        # Invalid type
        foreach($this->generateInvalidTypes() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseType($Original), 'ContentType::parseType() returned an invalid format');
        }
    }

    /**
     * @depends test_parseType
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Check params
        $this->assertEquals('Content-Type', $this->Properties['Type']->getValue($this->Header), 'ContentType::__construct() failed to set $_Type');
        $this->assertEquals('text/plain; charset=utf-8', $this->Properties['Value']->getValue($this->Header), 'ContentType::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            new ContentType(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        }

        catch (InvalidArgumentException $e) {}

        # No parameters
        $this->Header = new ContentType('text/plain');
        $this->assertEquals('text/plain', $this->Properties['Value']->getValue($this->Header), 'ContentType::__construct() failed to set $_Value');

        # Invalid property
        try {
            new ContentType('text/plain', array('???' => '???'));
            $this->fail('Failed to generate notice with invalid arguments');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("Content-Type: text/plain; charset=utf-8\r\n", @strval($this->Header), 'ContentType::__toSting() returned an invalid format');
    }
}
