<?php
/**
 * RequestTest.php | Apr 10, 2014
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

use ReflectionMethod;

use BLW\Type\IDataMapper;
use BLW\Type\HTTP\IRequest;

use BLW\Model\Config\Generic as GenericConfig;
use BLW\Model\GenericURI;
use BLW\Model\InvalidArgumentException;


/**
 * Test for BLW Request base class
 * @package BLW\HTTP
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\HTTP\ARequest
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\IURI
     */
    protected $URI = NULL;

    /**
     * @var \BLW\Type\HTTP\ARequest
     */
    protected $Request = NULL;

    /**
     * @var \BLW\Type\IConfig
     */
    protected $Config = NULL;

    protected function setUp()
    {
        $this->URI     = $this->getMockForAbstractClass('\BLW\Type\AURI', array('http://example.com/'));
        $this->Config  = new GenericConfig();
        $this->Request = $this->getMockForAbstractClass('\BLW\Type\HTTP\ARequest', array(IRequest::GET, $this->Config));
    }

    protected function tearDown()
    {
        $this->Request = NULL;
        $this->URI     = NULL;
    }

    /**
     * @covers ::getFactoryMethods
     */
    public function test_getFactoryMethods()
    {
        $this->assertNotEmpty($this->Request->getFactoryMethods(), 'IRequest::getFactoryMethods() Returned an invalid value');
        $this->assertContainsOnlyInstancesOf('ReflectionMethod', $this->Request->getFactoryMethods(), 'IRequest::getFactoryMethods() Returned an invalid value');
    }

    /**
     * @covers ::__construct
     */
    public function test_constuct()
    {
        $Request = $this->getMockForAbstractClass('\BLW\Type\HTTP\ARequest', array(IRequest::GET, $this->Config));

        $this->assertAttributeSame(IRequest::GET, '_Type', $Request, 'IRequest::__construct() Failed to set $_Type');
        $this->assertAttributeSame(null, '_URI', $Request, 'IRequest::__construct() Failed to reset $_URI');
        $this->assertAttributeSame(null, '_Referer', $Request, 'IRequest::__construct() Failed to reset $_Referer');
        $this->assertAttributeSame($this->Config, '_Config', $Request, 'IRequest::__construct() Failed to set $_Config');
        $this->assertAttributeInstanceOf('\\BLW\\Type\\MIME\\IHead', '_Head', $Request, 'IRequest::__construct() Failed to set $_Head');
        $this->assertAttributeInstanceOf('\\BLW\\Type\\MIME\\IBody', '_Body', $Request, 'IRequest::__construct() Failed to set $_Body');

        # No config
        $Request = $this->getMockForAbstractClass('\BLW\Type\HTTP\ARequest', array(IRequest::GET));

        $this->assertAttributeInstanceOf('\\BLW\\Type\\IConfig', '_Config', $Request, 'IRequest::__construct() Failed to set $_Config');

        # Invalid type
        try {
            $this->getMockForAbstractClass('\\BLW\\Type\\HTTP\\ARequest', array(-1, $this->Config));
            $this->fail('Failed to generate exception with invalid arguments');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::createFromString
     */
    public function test_createFromString()
    {
        $this->setExpectedException('RuntimeException');

        $Request = "";
        $Request = $this->Request->createFromString($Request);
    }

    public function generateValidTypes()
    {
        return array(
             array(IRequest::CONNECT, IDataMapper::UPDATED)
            ,array(IRequest::DELETE, IDataMapper::UPDATED)
            ,array(IRequest::GET, IDataMapper::UPDATED)
            ,array(IRequest::HEAD, IDataMapper::UPDATED)
            ,array(IRequest::OPTIONS, IDataMapper::UPDATED)
            ,array(IRequest::POST, IDataMapper::UPDATED)
        );
    }

    public function generateInvalidTypes()
    {
        return array(
             array(0, IDataMapper::INVALID)
            ,array(2.0, IDataMapper::INVALID)
            ,array('foo', IDataMapper::INVALID)
            ,array(false, IDataMapper::INVALID)
            ,array(NULL, IDataMapper::INVALID)
            ,array(array(), IDataMapper::INVALID)
            ,array(new \stdClass, IDataMapper::INVALID)
        );
    }

    /**
     * @covers ::setType
     */
    public function test_setType()
    {
        # Valid arguments
        foreach($this->generateValidTypes() as $Arguments) {

            list($Input, $Expected) = $Arguments;

            $this->assertSame($Expected, $this->Request->setType($Input), 'IRequest::setType() Should return IDataMapper::UPDATED');
            $this->assertAttributeSame($Input, '_Type', $this->Request, 'IRequest::setType() Failed to update $_Type');
        }

        # Invalid arguments
        foreach($this->generateInvalidTypes() as $Arguments) {

            list($Input, $Expected) = $Arguments;

            $this->assertSame($Expected, $this->Request->setType($Input), 'IRequest::setType() Should return IDataMapper::INVALID');
        }
    }

    /**
     * @depends test_setType
     * @covers ::getType
     */
    public function test_getType()
    {
        $this->assertSame(IDataMapper::UPDATED, $this->Request->setType(IRequest::DELETE), 'IRequest::setType() Should return IDataMapper::UPDATED');
        $this->assertSame(IRequest::DELETE, $this->Request->getType(), 'IRequest::getType() Returned an invalid value');
    }

    public function generateValidURIs()
    {
        return array(
             array($this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('http://example.com/')), IDataMapper::UPDATED)
            ,array($this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('ftp://example.com/')), IDataMapper::UPDATED)
            ,array($this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('https://example.com/')), IDataMapper::UPDATED)
            ,array($this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('http://example.com/path/file?query#fragment')), IDataMapper::UPDATED)
            ,array($this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('isbn:1391320392039023920:23293')), IDataMapper::UPDATED)
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
     * @covers ::setURI
     */
    public function test_setURI()
    {
        # Valid arguments
        foreach($this->generateValidURIs() as $Arguments) {

            list($Input, $Expected) = $Arguments;

            $this->assertSame($Expected, $this->Request->setURI($Input), 'IRequest::setURI() Should return '. $Expected);
            $this->assertAttributeSame($Input, '_URI', $this->Request, 'IRequest::setURI() Failed to update $_URI');
        }

        # Invalid arguments
        foreach($this->generateInvalidURIs() as $Arguments) {

            list($Input, $Expected) = $Arguments;

            $this->assertSame($Expected, $this->Request->setURI($Input), 'IRequest::setURI() Should return '. $Expected);
        }
    }

    /**
    * @depends test_setURI
    * @covers ::getURI
    */
    public function test_getURI()
    {
        $this->assertSame(IDataMapper::UPDATED, $this->Request->setURI($this->URI), 'IRequest::setURI() Should return IDataMapper::UPDATED');
        $this->assertSame($this->URI, $this->Request->getURI(), 'IRequest::getURI() Returned an invalid value');
    }

    /**
     * @covers ::setReferer
     */
    public function test_setReferer()
    {
        # Valid arguments
        foreach($this->generateValidURIs() as $Arguments) {

            list($Input, $Expected) = $Arguments;

            $this->assertSame($Expected, $this->Request->setReferer($Input), 'IRequest::setReferer() Should return '. $Expected);
            $this->assertAttributeSame($Input, '_Referer', $this->Request, 'IRequest::setReferer() Failed to update $_Referer');
        }

        # Invalid arguments
        foreach($this->generateInvalidURIs() as $Arguments) {

            list($Input, $Expected) = $Arguments;

            $this->assertSame($Expected, $this->Request->setReferer($Input), 'IRequest::setReferer() Should return '. $Expected);
        }
    }

    /**
    * @depends test_setReferer
    * @covers ::getReferer
    */
    public function test_getReferer()
    {
        $this->assertSame(IDataMapper::UPDATED, $this->Request->setReferer($this->URI), 'IRequest::setReferer() Should return IDataMapper::UPDATED');
        $this->assertSame($this->URI, $this->Request->getReferer(), 'IRequest::getReferer() Returned an invalid value');
    }

    /**
     * @depends test_setType
     * @depends test_setURI
     * @depends test_setReferer
     * @covers ::__get
     */
    public function test_get()
    {
        # Type
        $this->assertSame($this->Request->getType(), $this->Request->Type, 'IRequest::$Type should equal IRequest::getType()');

        # URI
        $this->assertSame($this->Request->getURI(), $this->Request->URI, 'IRequest::$URI should equal IRequest::getURI()');

        # Referer
        $this->assertSame($this->Request->getReferer(), $this->Request->Referer, 'IRequest::$Referer should equal IRequest::getReferer()');

        # Config
        $this->assertSame($this->Config, $this->Request->Config, 'IRequest::$Config should equal $_Config');

        # Header
        $this->assertSame($this->Request->getHeader(), $this->Request->Header, 'IRequest::$Header Should equal IRequest::getHeader()');

        # Body
        $this->assertSame($this->Request->getBody(), $this->Request->Body, 'IRequest::$Body Should equal IRequest::getBody()');

        # Undefined
        try {
            $this->Request->undefined;
            $this->fail('Failed to generate notice with undefined property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {}

        @$this->Request->undefined;
    }


    /**
     * @depends test_setType
     * @depends test_setURI
     * @depends test_setReferer
     * @covers ::__isset
     */
    public function test_isset()
    {
        $this->assertSame(IDataMapper::UPDATED, $this->Request->setType(IRequest::DELETE), 'IRequest::setType() Should return IDataMapper::UPDATED');
        $this->assertSame(IDataMapper::UPDATED, $this->Request->setURI($this->URI), 'IRequest::setURI() Should return IDataMapper::UPDATED');
        $this->assertSame(IDataMapper::UPDATED, $this->Request->setReferer($this->URI), 'IRequest::setReferer() Should return IDataMapper::UPDATED');

        # Type
        $this->assertTrue(isset($this->Request->Type), 'IRequest::$Type Should exist');

        # URI
        $this->assertTrue(isset($this->Request->URI), 'IRequest::$URI Should exist');

        # Referer
        $this->assertTrue(isset($this->Request->Referer), 'IRequest::$Referer Should exist');

        # Config
        $this->assertTrue(isset($this->Request->Config), 'IRequest::$Config Should exist');

        # Header
        $this->assertTrue(isset($this->Request->Header), 'IRequest::$Header Should not exist');

        # Body
        $this->assertTrue(isset($this->Request->Body), 'IRequest::$Body Should not exist');

        # Undefined
        $this->assertFalse(isset($this->Request->undefined), 'IRequest::$undefined should not exist');
    }

    /**
     * @depends test_get
     * @covers ::__set
     */
    public function test_set()
    {
        # Type
        $this->Request->Type = IRequest::DELETE;
        $this->assertSame(IRequest::DELETE, $this->Request->Type, 'IRequest::$Type Should equal IRequest::getType()');

        try {
            $this->Request->Type = null;
            $this->fail('Failed to generate notice with invalid value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {}

        @$this->Request->Type = null;

        # URI
        $Expected           = new GenericURI('http://foo.com');
        $this->Request->URI = $Expected;

        $this->assertSame($Expected, $this->Request->getURI(), 'IRequest::setURI() Failed to update $_URI');

        try {
            $this->Request->URI = null;
            $this->fail('Failed to generate notice with invalid value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {}

        @$this->Request->URI = null;

        # Referer
        $Expected               = new GenericURI('http://foo.com');
        $this->Request->Referer = $Expected;

        $this->assertSame($Expected, $this->Request->getReferer(), 'IRequest::$Referer Failed to update $_Referer');

        try {
            $this->Request->Referer = null;
            $this->fail('Failed to generate notice with invalid value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {}

        @$this->Request->Referer = null;

        # Config
        try {
            $this->Request->Config = NULL;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Request->Config = NULL;

        # Header
        try {
            $this->Request->Header = NULL;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Request->Header = NULL;

        # Body
        try {
            $this->Request->Body = NULL;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Request->Body = NULL;

        # Undefined
        try {
            $this->Request->undefined = '';
            $this->fail('Failed to generate notice with undefined property');
        }

        catch (\PHPUnit_Framework_Error $e) {
            $this->assertContains('non-existant property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Request->undefined = '';
    }

    /**
     * @depends test_isset
     * @depends test_set
     * @covers ::__unset
     */
    public function test_unset()
    {
        # Type
        $this->assertSame(IDataMapper::UPDATED, $this->Request->setType(IRequest::DELETE), 'IRequest::setType() Should return IDataMapper::UPDATED');
        unset($this->Request->Type);
        $this->assertFalse(isset($this->Request->Type), 'IRequest::$Type Should not exist');

        # URI
        $this->assertSame(IDataMapper::UPDATED, $this->Request->setURI($this->URI), 'IRequest::setURI() Should return IDataMapper::UPDATED');
        unset($this->Request->URI);
        $this->assertFalse(isset($this->Request->URI), 'IRequest::$URI Should not exist');

        # Referer
        $this->assertSame(IDataMapper::UPDATED, $this->Request->setReferer($this->URI), 'IRequest::setReferer() Should return IDataMapper::UPDATED');
        unset($this->Request->Referer);
        $this->assertFalse(isset($this->Request->URI), 'IRequest::$Referer Should not exist');

        # Undefined
        unset($this->Request->undefined);
    }
}