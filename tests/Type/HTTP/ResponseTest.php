<?php
/**
 * ResponseTest.php | Apr 10, 2014
 *
 * @filesource
 * @license MIT
 * @copyright Copyright (c) 2013-2018, mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * @package BLW\HTTP
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Tests\Type\HTTP;

use ReflectionProperty;
use ReflectionMethod;
use BLW\Type\IDataMapper;
use BLW\Type\HTTP\IResponse;


/**
 * Test for BLW Response base class
 * @package BLW\HTTP
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\HTTP\AResponse
 */
class ResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\IURI
     */
    protected $URI = NULL;

    /**
     * @var \BLW\Type\HTTP\AResponse
     */
    protected $Response = NULL;

    protected function setUp()
    {
        $setProp        = function($Object, $name, $value) {

            $Property   = new ReflectionProperty($Object, $name);

            $Property->setAccessible(true);

            $Property->setValue($Object, $value);
        };

        $this->URI      = $this->getMockForAbstractClass('\BLW\Type\AURI', array('http://example.com/'));
        $this->Response = $this->getMockForAbstractClass('\BLW\Type\HTTP\AResponse');

        $setProp($this->Response, '_Protocol', 'HTTP');
        $setProp($this->Response, '_Version', '1.1');
        $setProp($this->Response, '_Status', '200');
    }

    protected function tearDown()
    {
        $this->Response = NULL;
        $this->URI     = NULL;
    }

    /**
     * @covers ::getCodeString
     */
    public function test_getCodeString()
    {
        # Valid arguments
        $this->assertSame('OK', $this->Response->getCodeString(200), 'IResponse::getCodeString() Should return `OK`');
        $this->assertSame('Undefined', $this->Response->getCodeString(-1), 'IResponse::getCodeString() Should return `Undefined`');

        # Invalid arguments
        $this->assertSame('Undefined', $this->Response->getCodeString(array()), 'IResponse::getCodeString() Should return `Undefined`');
    }

    /**
     * @covers ::isValidCode
     */
    public function test_isValidCode()
    {
        $this->assertTrue($this->Response->isValidCode(200), 'IResponse::isValidCode() Should return true');
        $this->assertFalse($this->Response->isValidCode(430), 'IResponse::isValidCode() Should return false');
    }

    public function generateValidURIs()
    {
        return array(
        	 array($this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('http://example.com/')), IDataMapper::UPDATED)
        	,array($this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('ftp://example.com/')), IDataMapper::UPDATED)
        	,array($this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('https://example.com/')), IDataMapper::UPDATED)
        	,array($this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('http://example.com/path/file?query#fragment')), IDataMapper::UPDATED)
        );
    }

    public function generateInvalidURIs()
    {
        return array(
        	 array($this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('example.com')), IDataMapper::INVALID)
        	,array($this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('//example.com')), IDataMapper::INVALID)
            ,array('foo', IDataMapper::INVALID)
            ,array(false, IDataMapper::INVALID)
            ,array(NULL, IDataMapper::INVALID)
            ,array(array(), IDataMapper::INVALID)
            ,array(new \stdClass, IDataMapper::INVALID)
        );
    }


    /**
     * @covers ::setRequestURI
     */
    public function test_setRequestURI()
    {
        $Property = new ReflectionProperty($this->Response, '_RequestURI');

        $Property->setAccessible(true);

        # Valid arguments
        foreach($this->generateValidURIs() as $Arguments) {

            list($Input, $Expected) = $Arguments;

            $this->assertSame($Expected, $this->Response->setRequestURI($Input), 'IResponse::setRequestURI() Should return '. $Expected);
            $this->assertSame($Input, $Property->getValue($this->Response), 'IResponse::setRequestURI() Failed to update $_RequestURI');
        }

        # Invalid arguments
        foreach($this->generateInvalidURIs() as $Arguments) {

            list($Input, $Expected) = $Arguments;

            $this->assertSame($Expected, $this->Response->setRequestURI($Input), 'IResponse::setRequestURI() Should return '. $Expected);
        }
    }

    /**
     * @depends test_setRequestURI
     * @covers ::getRequestURI
     */
    public function test_getRequestURI()
    {
        $this->assertSame(IDataMapper::UPDATED, $this->Response->setRequestURI($this->URI), 'IResponse::setRequestURI() Should return IDataMapper::UPDATED');
        $this->assertSame($this->URI, $this->Response->getRequestURI(), 'IResponse::getRequestURI() Returned an invalid value');
    }

    /**
     * @covers ::setURI
     */
    public function test_setURI()
    {
        $Property = new ReflectionProperty($this->Response, '_URI');

        $Property->setAccessible(true);

        # Valid arguments
        foreach($this->generateValidURIs() as $Arguments) {

            list($Input, $Expected) = $Arguments;

            $this->assertSame($Expected, $this->Response->setURI($Input), 'IResponse::setURI() Should return '. $Expected);
            $this->assertSame($Input, $Property->getValue($this->Response), 'IResponse::setURI() Failed to update $_URI');
        }

        # Invalid arguments
        foreach($this->generateInvalidURIs() as $Arguments) {

            list($Input, $Expected) = $Arguments;

            $this->assertSame($Expected, $this->Response->setURI($Input), 'IResponse::setURI() Should return '. $Expected);
        }
    }

    /**
     * @depends test_setURI
     * @covers ::getURI
     */
    public function test_getURI()
    {
        $this->assertSame(IDataMapper::UPDATED, $this->Response->setURI($this->URI), 'IResponse::setURI() Should return IDataMapper::UPDATED');
        $this->assertSame($this->URI, $this->Response->getURI(), 'IResponse::getURI() Returned an invalid value');
    }

    /**
     * @covers ::setStorage
     */
    public function test_setStorage()
    {
        $Property = new ReflectionProperty($this->Response, '_Storage');

        $Property->setAccessible(true);

        $Expected = array('foo' => 1);

        $this->assertNotEquals($Expected, $Property->getValue($this->Response), 'IResponse::$_Storage should not equal $Expected');
        $this->assertSame(IDataMapper::UPDATED, $this->Response->setStorage($Expected), 'IResponse::setStorage() Should return IDataMapper::UPDATED');
        $this->assertSame($Expected, $Property->getValue($this->Response), 'IResponse::setStorage() Failed to update $_Storage');
    }

    /**
     * @depends test_setRequestURI
     * @depends test_setURI
     * @depends test_setStorage
     * @covers ::__get
     */
    public function test_get()
    {
        # URI
        $this->assertSame(IDataMapper::UPDATED, $this->Response->setURI($this->URI), 'IResponse::setURI() Should return IDataMapper::UPDATED');
        $this->assertSame($this->URI, $this->Response->URI, 'IResponse::$URI Should equal IResponse::getURI()');

        # Header
        $this->assertSame($this->Response->getHeader(), $this->Response->Header, 'IResponse::$Header Should equal IResponse::getBody()');

        # Body
        $this->assertSame($this->Response->getBody(), $this->Response->Body, 'IResponse::$Body Should equal IResponse::getBody()');

        # Undefined
        try {
        	$this->Response->undefined;
        	$this->fail('Failed to generate notice with undefined property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {}
    }


    /**
     * @depends test_setRequestURI
     * @depends test_setURI
     * @depends test_setStorage
     * @covers ::__isset
     */
    public function test_isset()
    {
        $this->assertSame(IDataMapper::UPDATED, $this->Response->setURI($this->URI), 'IResponse::setURI() Should return IDataMapper::UPDATED');

        # URI
        $this->assertTrue(isset($this->Response->URI), 'IResponse::$URI Should exist');

        # Header
        $this->assertTrue(isset($this->Response->Header), 'IResponse::$Header Should not exist');

        # Body
        $this->assertTrue(isset($this->Response->Body), 'IResponse::$Body Should not exist');

        # Undefined
        $this->assertFalse(isset($this->Response->undefined), 'IResponse::$undefined should not exist');
    }

    /**
     * @depends test_get
     * @covers ::__set
     */
    public function test_set()
    {
        # URI
        $this->Response->URI = $this->URI;
        $this->assertSame($this->URI, $this->Response->getURI(), 'IResponse::$URI Failed to update $_URI');

        try {
            $this->Response->URI = $this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('www.example.com'));
            $this->fail('Failed to generate notice with invalid value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # Header
        try {
        	$this->Response->Header = NULL;
        	$this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
        	$this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # Body
        try {
        	$this->Response->Body = NULL;
        	$this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
        	$this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # Undefined
        try {
            $this->Response->undefined = '';
            $this->fail('Failed to generate notice with undefined property');
        }

        catch (\PHPUnit_Framework_Error $e) {
            $this->assertContains('non-existant property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }
    }

    /**
     * @depends test_isset
     * @depends test_set
     * @covers ::__unset
     */
    public function test_unset()
    {
        $this->assertSame(IDataMapper::UPDATED, $this->Response->setURI($this->URI), 'IResponse::setURI() Should return IDataMapper::UPDATED');

        # URI
        unset($this->Response->URI);
        $this->assertFalse(isset($this->Response->URI), 'IResponse::$URI Should not exist');

        # Header
        try {
            unset($this->Response->Header);
            $this->fail('Faied to genereate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
        	$this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # Body
        try {
            unset($this->Response->Body);
            $this->fail('Faied to genereate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
        	$this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # Undefined
        unset($this->Response->undefined);
    }

    /**
     * @covers ::getFactoryMethods
     */
    public function test_getFactoryMethods()
    {
        $this->assertGreaterThan(1, count($this->Response->getFactoryMethods()), 'IResponse::getFactoryMethods() Returned an invalid result');
    }

    /**
     * @covers ::_parseParts
     */
    public function test_parseParts()
    {
        $test   = file_get_contents(dirname(dirname(__DIR__)). '/Config/response.txt');
        $Method = function ($Response) {

        	$Method = new ReflectionMethod($Response, '_parseParts');

        	$Method->setAccessible(true);

        	return $Method->invokeArgs($Response, array_slice(func_get_args(), 1));
        };

        $Parts = $Method($this->Response, $test);

        $this->assertEquals('HTTP', $Parts['Protocol'], 'IMessage::_parseParts() Failed to parse protocol');
        $this->assertEquals('1.1', $Parts['Version'], 'IMessage::_parseParts() Failed to parse verion');
        $this->assertEquals(200, $Parts['Status'], 'IMessage::_parseParts() Failed to parse status');
        $this->assertCount(9, $Parts['Headers'], 'IMessage::_parseParts() Failed to parse headers');
        $this->assertStringStartsWith('<!DOCTYPE html>', $Parts['Body'], 'IMessage::_parseParts() Failed to parse body');
   }

   /**
    * @depends test_parseParts
    * @covers ::createFromString
    */
   public function test_createFromString()
   {
       $test     = file_get_contents(dirname(dirname(__DIR__)). '/Config/response.txt');
       $Response = $this->Response->createFromString($test);

       $this->assertSame('HTTP', $Response->Protocol, 'IResponse::$Protocol should be HTTP');
       $this->assertSame('1.1', $Response->Version, 'IResponse::$Version should be 1.1');
       $this->assertSame(200, $Response->Status, 'IResponse::$Status should be 200');
       $this->assertCount(9, $Response->Header, 'IResponse::$Head should have 9 members');
       $this->assertCount(1, $Response->Body, 'IResponse::$Body should have 1 member');
   }
}