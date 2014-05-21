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
namespace BLW\Type\HTTP;

use ReflectionProperty;
use ReflectionMethod;

use BLW\Type\IDataMapper;

use BLW\Model\InvalidArgumentException;


/**
 * Test for BLW Response base class
 * @package BLW\HTTP
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
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
        $setProp = function ($Object, $name, $value) {
            $Property = new ReflectionProperty($Object, $name);

            $Property->setAccessible(true);
            $Property->setValue($Object, $value);
        };

        $this->URI      = $this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('http://example.com/'));
        $this->Response = $this->getMockForAbstractClass('\\BLW\\Type\\HTTP\\AResponse');

        $setProp($this->Response, '_Protocol', 'HTTP');
        $setProp($this->Response, '_Version', '1.1');
        $setProp($this->Response, '_Status', 200);
        $setProp($this->Response, '_Storage', array('foo' => 1));
    }

    protected function tearDown()
    {
        $this->Response = NULL;
        $this->URI      = NULL;
    }

    public function generateInvalidArgs()
    {
        return array(
             array(new \stdClass, '1.0', 200)
            ,array('HTTP', new \stdClass, 200)
        );
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        $Property = function ($Object, $Property) {
            $Property = new ReflectionProperty($Object, $Property);

            $Property->setAccessible(true);

            return $Property->getValue($Object);
        };

        // Valid arguments
        $Response = $this->getMockForAbstractClass('\\BLW\\Type\\HTTP\\AResponse', array('FOO', '2.0', '100'));

        $this->assertSame('FOO', $Property($Response, '_Protocol'), 'IResponse::__construct() Failed to set $_Protocol');
        $this->assertSame('2.0', $Property($Response, '_Version'), 'IResponse::__construct() Failed to set $_Version');
        $this->assertSame(100, $Property($Response, '_Status'), 'IResponse::__construct() Failed to set $_Status');
        $this->assertSame(null, $Property($Response, '_URI'), 'IResponse::__construct() Failed to set $_URI');
        $this->assertSame(null, $Property($Response, '_RequestURI'), 'IResponse::__construct() Failed to set $_RequestURI');
        $this->assertSame(array(), $Property($Response, '_Storage'), 'IResponse::__construct() Failed to set $_Storage');
        $this->assertInstanceOf('\\BLW\\Type\\MIME\\IHead', $Property($Response, '_Head'), 'IResponse::__construct() Failed to set $_Head');
        $this->assertInstanceOf('\\BLW\\Type\\MIME\\IBody', $Property($Response, '_Body'), 'IResponse::__construct() Failed to set $_Body');


        foreach ($this->generateInvalidArgs() as $Arguments) {

            try {
                $this->getMockForAbstractClass('\\BLW\\Type\\HTTP\\AResponse', $Arguments);
                $this->fail('Failed to generate exception with invalid arguments');
            } catch (InvalidArgumentException $e) {}
        }
    }

    /**
     * @covers ::getFactoryMethods
     */
    public function test_getFactoryMethods()
    {
        $this->assertNotEmpty($this->Response->getFactoryMethods(), 'IResponse::getFactoryMethods() Returned an invalid value');
        $this->assertContainsOnlyInstancesOf('ReflectionMethod', $this->Response->getFactoryMethods(), 'IResponse::getFactoryMethods() Returned an invalid value');
    }

    /**
     * @covers ::_parseParts
     */
    public function test_parseParts()
    {
        $test   = file_get_contents(dirname(dirname(__DIR__)). '/Config/response.txt');
        $test   = preg_replace('!\r*\n$!', "\r\n", $test);
        $Method = function ($Response) {
            $Method = new ReflectionMethod($Response, '_parseParts');
            $Args   = func_get_args();
            $Args   = array_slice($Args, 1);

            $Method->setAccessible(true);

            return $Method->invokeArgs($Response, $Args);
        };

        $Parts = $Method($this->Response, $test);

        $this->assertEquals('HTTP', $Parts['Protocol'], 'IMessage::_parseParts() Failed to parse protocol');
        $this->assertEquals('1.1', $Parts['Version'], 'IMessage::_parseParts() Failed to parse verion');
        $this->assertEquals(200, $Parts['Status'], 'IMessage::_parseParts() Failed to parse status');
        $this->assertCount(10, $Parts['Headers'], 'IMessage::_parseParts() Failed to parse headers');
        $this->assertStringStartsWith('<!DOCTYPE html>', $Parts['Body'], 'IMessage::_parseParts() Failed to parse body');

        // No header
        $test = substr($test, strpos($test, "\n") + 1);

        $Parts = $Method($this->Response, $test);

        $this->assertEquals('HTTP', $Parts['Protocol'], 'IMessage::_parseParts() Failed to parse protocol');
        $this->assertEquals('1.0', $Parts['Version'], 'IMessage::_parseParts() Failed to parse verion');
        $this->assertEquals(0, $Parts['Status'], 'IMessage::_parseParts() Failed to parse status');
        $this->assertCount(10, $Parts['Headers'], 'IMessage::_parseParts() Failed to parse headers');
        $this->assertStringStartsWith('<!DOCTYPE html>', $Parts['Body'], 'IMessage::_parseParts() Failed to parse body');
    }

   /**
    * @depends test_parseParts
    * @covers ::createFromString
    */
   public function test_createFromString()
   {
        $test     = file_get_contents(dirname(dirname(__DIR__)). '/Config/response.txt');
        $test     = preg_replace('!\r*\n$!', "\r\n", $test);
        $Response = $this->Response->createFromString($test);

        $this->assertSame('HTTP', $Response->Protocol, 'IResponse::$Protocol should be HTTP');
        $this->assertSame('1.1', $Response->Version, 'IResponse::$Version should be 1.1');
        $this->assertSame(200, $Response->Status, 'IResponse::$Status should be 200');
        $this->assertCount(11, $Response->Header, 'IResponse::$Head should have 9 members');
        $this->assertCount(1, $Response->Body, 'IResponse::$Body should have 1 member');

        // Small string
        try {
            $this->Response->createFromString('foo');
            $this->fail('Failed to generate exception with short string');
        } catch (InvalidArgumentException $e) {}

        // Invalid Arguments
        try {
            $this->Response->createFromString(new \stdClass);
            $this->fail('Failed to generate exception with invalid argument');
        } catch (InvalidArgumentException $e) {}
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
            ,array($this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('isbn:920438908089023:238080')), IDataMapper::UPDATED)
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
        # Valid arguments
        foreach ($this->generateValidURIs() as $Arguments) {

            list($Input, $Expected) = $Arguments;

            $this->assertSame($Expected, $this->Response->setRequestURI($Input), 'IResponse::setRequestURI() Should return '. $Expected);
            $this->assertAttributeSame($Input, '_RequestURI', $this->Response, 'IResponse::setRequestURI() Failed to update $_RequestURI');
        }

        # Invalid arguments
        foreach ($this->generateInvalidURIs() as $Arguments) {

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
        # Valid arguments
        foreach ($this->generateValidURIs() as $Arguments) {

            list($Input, $Expected) = $Arguments;

            $this->assertSame($Expected, $this->Response->setURI($Input), 'IResponse::setURI() Should return '. $Expected);
            $this->assertAttributeSame($Input, '_URI', $this->Response, 'IResponse::setURI() Failed to update $_URI');
        }

        # Invalid arguments
        foreach ($this->generateInvalidURIs() as $Arguments) {

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
     * @covers ::count
     */
    public function test_count()
    {
        $this->assertCount(1, $this->Response, 'IResponse::count() Returned an invalid value');
    }

    /**
     * @covers ::offsetExists
     */
    public function test_offsetExists()
    {
        // Valid index
        $this->assertTrue(isset($this->Response['foo']), 'IResponse[foo] should exist');

        // Undefined index
        $this->assertFalse(isset($this->Response['bar']), 'IResponse[bar] should not exist');

        // Invalid index
        try {
            isset($this->Response[array()]);
            $this->fail('Failed to generate error with invalid index');
        } catch (\PHPUnit_Framework_Error $e) {
            $this->assertContains('array_key_exists', $e->getMessage(), 'Invalid Notice: '. $e->getMessage());
        }
    }

    /**
     * @covers ::offsetGet
     */
    public function test_offsetGet()
    {
        // Valid index
        $this->assertSame(1, $this->Response['foo'], '$Respones[foo] should equal 1');

        // Unset index
        try {
            $this->Response['bar'];
            $this->fail('Failed to generate notice with undefined index');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Undefined index', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        // Invalid value
        try {
            $this->Response[array()];
            $this->fail('Failed to generate error with invalid index');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Undefined index', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Response[array()];
    }

    /**
     * @depends test_count
     * @covers ::offsetSet
     */
    public function test_offsetSet()
    {
        // Valid value valid index
        $this->Response['foo'] += 1;

        $this->assertSame(2, $this->Response['foo'], 'IResponse[foo] Failed to update $_Storage');

        $this->Response[] = 100;

        $this->assertSame(100, $this->Response[0], 'IResponse[] Failed to update $_Storage');

        // Valid value unkown index
        $this->Response['bar'] = 100;

        $this->assertSame(100, $this->Response['bar'], 'IResponse[bar] Failed to update $_Storage');

        // Valid value invalid index
        try {
            $this->Response[array()] = 1;
            $this->fail('Failed to generage notice with invalid index');
        } catch (\OutOfRangeException $e) {}

        // Invalid value valid index

        // Invalid value invalid index
    }

    /**
     * @depends test_offsetExists
     * @covers ::offsetUnset
     */
    public function test_offsetUnset()
    {
        // Valid index
        unset($this->Response['foo']);

        $this->assertFalse(isset($this->Response['foo']), 'unset(IResponse[foo]) failed to update $_Storage');

        // Unkown index
        unset($this->Response['bar']);

        // Invalid index
        unset($this->Response[array()]);
    }

    /**
     * @depends test_count
     * @covers ::setStorage
     */
    public function test_setStorage()
    {
        $Expected = array('bar' => 1);

        $this->assertAttributeNotEquals($Expected, '_Storage', $this->Response, 'IResponse::$_Storage should not equal $Expected');
        $this->assertSame(IDataMapper::UPDATED, $this->Response->setStorage($Expected), 'IResponse::setStorage() Should return IDataMapper::UPDATED');
        $this->assertAttributeSame($Expected, '_Storage', $this->Response, 'IResponse::setStorage() Failed to update $_Storage');
    }

    /**
     * @covers ::clearStorage
     */
    public function test_clearStorage()
    {
        $this->Response->clearStorage();

        $this->assertSame(0, count($this->Response), 'IResponse::clearStorage() Failed to reset $_Storage');
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
        $this->assertSame($this->Response->getURI(), $this->Response->URI, 'IResponse::$URI should equal $_URI');

        # RequestURI
        $this->assertSame($this->Response->getRequestURI(), $this->Response->RequestURI, 'IResponse::$RequestURI should equal $_RequestURI');

        # Protocol
        $this->assertSame('HTTP', $this->Response->Protocol, 'IResponse::$Protocol should equal `HTTP`');

        # Version
        $this->assertSame('1.1', $this->Response->Version, 'IResponse::$Version should equal 1.1');

        # Status
        $this->assertSame(200, $this->Response->Status, 'IResponse::$Status should equal IResponse::getStatus()');

        # Header
        $this->assertSame($this->Response->getHeader(), $this->Response->Header, 'IResponse::$Header Should equal IResponse::getBody()');

        # Body
        $this->assertSame($this->Response->getBody(), $this->Response->Body, 'IResponse::$Body Should equal IResponse::getBody()');

        # Undefined
        try {
            $this->Response->undefined;
            $this->fail('Failed to generate notice with undefined property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {}

        @$this->Response->undefined;
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
        $this->assertSame(IDataMapper::UPDATED, $this->Response->setRequestURI($this->URI), 'IResponse::setRequestURI() Should return IDataMapper::UPDATED');

        # URI
        $this->assertTrue(isset($this->Response->URI), 'IResponse::$URI Should exist');

        # RequestURI
        $this->assertTrue(isset($this->Response->RequestURI), 'IResponse::$RequestURI Should exist');

        # Protocol
        $this->assertTrue(isset($this->Response->Protocol), 'IResponse::$Protocol Should exist');

        # Version
        $this->assertTrue(isset($this->Response->Version), 'IResponse::$Version Should exist');

        # Status
        $this->assertTrue(isset($this->Response->Status), 'IResponse::$Status Should exist');

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
            $this->Response->URI = null;
            $this->fail('Failed to generate notice with invalid value');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Response->URI = null;

        # RequestURI
        $this->Response->RequestURI = $this->URI;

        $this->assertSame($this->URI, $this->Response->getRequestURI(), 'IResponse::$RequestURI Failed to update $_RequestURI');

        try {
            $this->Response->RequestURI = null;
            $this->fail('Failed to generate notice with invalid value');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Response->RequestURI = null;

        # Protocol
        try {
            $this->Response->Protocol = NULL;
            $this->fail('Failed to generate notice on readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Response->Protocol = NULL;

        # Version
        try {
            $this->Response->Version = NULL;
            $this->fail('Failed to generate notice on readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Response->Version = NULL;

        # Status
        try {
            $this->Response->Status = NULL;
            $this->fail('Failed to generate notice on readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Response->Status = NULL;

        # Header
        try {
            $this->Response->Header = NULL;
            $this->fail('Failed to generate notice on readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Response->Header = NULL;

        # Body
        try {
            $this->Response->Body = NULL;
            $this->fail('Failed to generate notice on readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Response->Body = NULL;

        # Undefined
        try {
            $this->Response->undefined = '';
            $this->fail('Failed to generate notice with undefined property');
        } catch (\PHPUnit_Framework_Error $e) {
            $this->assertContains('non-existant property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Response->undefined = '';
    }

    /**
     * @depends test_get
     * @depends test_set
     * @covers ::__unset
     */
    public function test_unset()
    {
        # URI
        $this->Response->URI = $this->URI;

        unset($this->Response->URI);

        $this->assertNull($this->Response->URI, 'unset(IResponse::$URI) Failed to reset $_URI');

        # RequestURI
        $this->Response->RequestURI = $this->URI;

        unset($this->Response->RequestURI);

        $this->assertNull($this->Response->RequestURI, 'unset(IResponse::$RequestURI) Failed to reset $_RequestURI');

        # Undefined
        unset($this->Response->undefined);
    }
}
