<?php
/**
 * HeaderTest.php | Mar 10, 2014
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
namespace BLW\Tests\Type\MIME;

use BLW\Model\InvalidArgumentException;


/**
 * Tests BLW Library MIME header type.
 * @package BLW\MIME
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\MIME\AHeader
 */
class HeaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\MIME\IHeader
     */
    protected $Header = NULL;

    /**
     * @var \ReflectionProperty[]
     */
    protected $Properties = array();

    protected function setUp()
    {
        $this->Header      = $this->getMockForAbstractClass('\\BLW\\Type\\MIME\\AHeader');
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

    /**
     * @covers ::getType
     */
    public function test_getType()
    {
        $this->assertEquals('', $this->Header->getType(), 'IHeader::getType() should initially be ``');
        $this->Properties['Type']->setValue($this->Header, 'test');
        $this->assertNotEmpty($this->Header->getType(), 'IHeader::getType() did not change as expected');
   }

    /**
     * @covers ::getValue
     */
    public function test_getValue()
    {
        $this->assertEquals('', $this->Header->getValue(), 'IHeader::getValue() should initially be ``');
        $this->Properties['Value']->setValue($this->Header, 'test');
        $this->assertNotEmpty($this->Header->getValue(), 'IHeader::getValue() did not change as expected');
   }


    public function generateValidParameters()
    {
        return array(
        	 array('name', 'image.gif', '; name=image.gif')
        	,array('foo', '1', '; foo=1')
        	,array('Charset', 'utf-8', '; charset=utf-8')
        );
    }

    public function generateInvalidParameters()
    {
        return array(
        	 array('pre-;-post', 'test')
            ,array('pre-?-post', 'test')
            ,array('pre-(-post', 'test')
            ,array('pre-)-post', 'test')
        );
    }

   /**
    * @covers ::parseParameter
    */
   public function test_parseParameter()
   {
        # Valid parameter
        foreach($this->generateValidParameters() as $Parameters) {
            list($Argument, $Value, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseParameter($Argument, $Value), 'ContentType::parseParameter() returned an invalid format');
        }

        # Invalid parameter
        foreach($this->generateInvalidParameters() as $Parameters) {
            list($Argument, $Value) = $Parameters;

            try {
                $this->Header->parseParameter($Argument, $Value);
                $this->fail('Failed to generate exception with invalid parameter');
            }

            catch (InvalidArgumentException $e) {}
        }
    }

   /**
    * @depends test_getType
    * @depends test_getValue
    * @covers ::__toString
    */
   public function test_toString()
   {
        @strval($this->Header);

        # Test warning on initial state
        $e = error_get_last();

        $this->assertContains('Type or Value', $e['message'], 'Failed to generate warning on (string) IHeader');

        # Update Type
        $this->Properties['Type']->setValue($this->Header, 'test');

        @strval($this->Header);

        $e = error_get_last();

        $this->assertContains('Type or Value', $e['message'], 'Failed to generate warning on (string) IHeader');

        # Update value
        $this->Properties['Value']->setValue($this->Header, 'test');

        $this->assertEquals("test: test\r\n", @strval($this->Header), '(string) IHeader is in an unexpected format');
   }
}
