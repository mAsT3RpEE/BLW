<?php
/**
 * AgeTest.php | Mar 10, 2014
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


/**
 * Tests BLW Library MIME Contetn-Type header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\Age
 */
class AgeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\MIME\Age
     */
    protected $Header = NULL;

    /**
     * @var \ReflectionProperty[]
     */
    protected $Properties = array();

    protected function setUp()
    {
        $this->Header      = new Age('100');
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
             array('100', '100')
            ,array(';;100;;', '100')
            ,array('"100"', '100')
        );
    }

    public function generateInvalidTypes()
    {
        return array(
             array('foo', '2147483648')
            ,array(false, '2147483648')
            ,array(new \stdClass, '2147483648')
            ,array(array(), '2147483648')
        );
    }

    /**
     * @covers ::parseAge
     */
    public function test_parseAge()
    {
        # Valid type
        foreach ($this->generateValidTypes() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseAge($Original), 'Age::parseAge() returned an invalid format');
        }

        # Invalid type
        foreach ($this->generateInvalidTypes() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseAge($Original), 'Age::parseAge() returned an invalid format');
        }
    }

    /**
     * @depends test_parseAge
     * @covers ::__construct
     */
    public function test_construct()
    {
        $this->Header = new Age('100');

        # Check params
        $this->assertEquals('Age', $this->Properties['Type']->getValue($this->Header), 'Age::__construct() failed to set $_Type');
        $this->assertEquals('100', $this->Properties['Value']->getValue($this->Header), 'Age::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            new Age(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        } catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("Age: 100\r\n", @strval($this->Header), 'Age::__toSting() returned an invalid format');
    }
}
