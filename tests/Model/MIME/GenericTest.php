<?php
/**
 * GenericTest.php | Mar 10, 2014
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
use BLW\Model\MIME\Generic;


/**
 * Tests BLW Library MIME header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\Generic
 */
class GenericTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\MIME\Generic
     */
    protected $Header = NULL;

    protected function setUp()
    {
        $this->Header = new Generic('Generic', 'Test description');
    }

    protected function tearDown()
    {
        $this->Header = NULL;
    }

    public function generateValidTypes()
    {
        return array(
        	 array('test', 'test')
        	,array('test with space', 'test')
        	,array('`~!@#$%^&*()-_=+[{]}\\|;:\',<.>/?', '!')
            ,array('"""""still okay"""""""', 'still')
        );
    }

    public function generateInvalidTypes()
    {
        return array(
        	 array('', 'X-Header')
            ,array('"""', 'X-Header')
        	,array(false, 'X-Header')
        );
    }

    /**
     * @covers ::parseType
     */
    public function test_parseType()
    {
        # Valid Description
        foreach($this->generateValidTypes() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseType($Original), 'Generic::parseType() returned an invalid format');
        }

        # Invalid Description
        foreach($this->generateInvalidTypes() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseType($Original), 'Generic::parseType() returned an invalid format');
        }
    }

    public function generateValidValues()
    {
        return array(
        	 array('test', 'test')
        	,array('test with space', 'test with space')
        	,array('`~!@#$%^&*()-_=+[{]}\\|;:\',<.>/?', '`~!@#$%^&*()-_=+[{]}\\|;:\',<.>/?')
            ,array('"""""still okay"""""""', '"""""still okay"""""""')
        );
    }

    public function generateInvalidValues()
    {
        return array(
        	 array('', '')
            ,array("\n\n", '')
            ,array("\r\r", '')
            ,array(false, '')
        );
    }

    /**
     * @covers ::parseValue
     */
    public function test_parseValue()
    {
        # Valid Description
        foreach($this->generateValidValues() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseValue($Original), 'Generic::parseValue() returned an invalid format');
        }

        # Invalid Description
        foreach($this->generateInvalidValues() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseValue($Original), 'Generic::parseValue() returned an invalid format');
        }
    }

    /**
     * @depends test_parseType
     * @depends test_parseValue
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Check params
        $this->assertAttributeEquals('Generic', '_Type', $this->Header, 'Generic::__construct() failed to set $_Type');
        $this->assertAttributeEquals('Test description', '_Value', $this->Header, 'Generic::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            new Generic(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        }

        catch (InvalidArgumentException $e) {}

        try {
            new Generic('Generic', null);
            $this->fail('Failed to generate exception with invalid parameters');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("Generic: Test description\r\n", @strval($this->Header), 'Generic::__toSting() returned an invalid format');
    }
}
