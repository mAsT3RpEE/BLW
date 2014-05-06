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
namespace BLW\Tests\Type\HTTP;

use ReflectionProperty;
use ReflectionMethod;

use BLW\Type\IDataMapper;
use BLW\Type\HTTP\IRequest;

use BLW\Model\Config\Generic as GenericConfig;


/**
 * Test for BLW Request base class
 * @package BLW\HTTP
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
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

    protected function setUp()
    {
        $this->URI     = $this->getMockForAbstractClass('\BLW\Type\AURI', array('http://example.com/'));
        $this->Request = $this->getMockForAbstractClass('\BLW\Type\HTTP\ARequest', array(IRequest::GET, new GenericConfig));
    }

    protected function tearDown()
    {
        $this->Request = NULL;
        $this->URI     = NULL;
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
        	 array('foo', IDataMapper::INVALID)
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
        $Property = new ReflectionProperty($this->Request, '_Type');

        $Property->setAccessible(true);

        # Valid arguments
        foreach($this->generateValidTypes() as $Arguments) {

            list($Input, $Expected) = $Arguments;

            $this->assertSame($Expected, $this->Request->setType($Input), 'IRequest::setType() Should return '. $Expected);
            $this->assertSame($Input, $Property->getValue($this->Request), 'IRequest::setType() Failed to update $_Type');
        }

        # Invalid arguments
        foreach($this->generateInvalidTypes() as $Arguments) {

            list($Input, $Expected) = $Arguments;

            $this->assertSame($Expected, $this->Request->setType($Input), 'IRequest::setType() Should return '. $Expected);
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
        $Property = new ReflectionProperty($this->Request, '_URI');

        $Property->setAccessible(true);

        # Valid arguments
        foreach($this->generateValidURIs() as $Arguments) {

            list($Input, $Expected) = $Arguments;

            $this->assertSame($Expected, $this->Request->setURI($Input), 'IRequest::setURI() Should return '. $Expected);
            $this->assertSame($Input, $Property->getValue($this->Request), 'IRequest::setURI() Failed to update $_URI');
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
        $Property = new ReflectionProperty($this->Request, '_Referer');

        $Property->setAccessible(true);

        # Valid arguments
        foreach($this->generateValidURIs() as $Arguments) {

            list($Input, $Expected) = $Arguments;

            $this->assertSame($Expected, $this->Request->setReferer($Input), 'IRequest::setReferer() Should return '. $Expected);
            $this->assertSame($Input, $Property->getValue($this->Request), 'IRequest::setReferer() Failed to update $_Referer');
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
        $this->assertSame(IDataMapper::UPDATED, $this->Request->setType(IRequest::DELETE), 'IRequest::setType() Should return IDataMapper::UPDATED');
        $this->assertSame(IRequest::DELETE, $this->Request->Type, 'IRequest::$Type Should equal IRequest::getType()');

        # URI
        $this->assertSame(IDataMapper::UPDATED, $this->Request->setURI($this->URI), 'IRequest::setURI() Should return IDataMapper::UPDATED');
        $this->assertSame($this->URI, $this->Request->URI, 'IRequest::$URI Should equal IRequest::getURI()');

        # Referer
        $this->assertSame(IDataMapper::UPDATED, $this->Request->setReferer($this->URI), 'IRequest::setReferer() Should return IDataMapper::UPDATED');
        $this->assertSame($this->URI, $this->Request->URI, 'IRequest::$Referer Should equal IRequest::getReferer()');

        # Config
        $this->assertCount(0, $this->Request->Config, 'IRequest::$Config Should be countable');

        # Header
        $this->assertSame($this->Request->getHeader(), $this->Request->Header, 'IRequest::$Header Should equal IRequest::getBody()');

        # Body
        $this->assertSame($this->Request->getBody(), $this->Request->Body, 'IRequest::$Body Should equal IRequest::getBody()');

        # Undefined
        try {
        	$this->Request->undefined;
        	$this->fail('Failed to generate notice with undefined property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {}
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
        $this->assertTrue(isset($this->Request->URI), 'IRequest::$Referer Should exist');

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
        $this->assertSame(IRequest::DELETE, $this->Request->getType(), 'IRequest::$Type Failed to update $_Type');

        try {
            $this->Request->Type = 'fooo';
            $this->fail('Failed to generate notice with invalid value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # URI
        $this->Request->URI = $this->URI;
        $this->assertSame($this->URI, $this->Request->getURI(), 'IRequest::$URI Failed to update $_URI');

        try {
            $this->Request->URI = $this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('www.example.com'));
            $this->fail('Failed to generate notice with invalid value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # Referer
        $this->Request->Referer = $this->URI;
        $this->assertSame($this->URI, $this->Request->getReferer(), 'IRequest::$Referer Failed to update $_Referer');

        try {
            $this->Request->Referer = $this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('www.example.com'));
            $this->fail('Failed to generate notice with invalid value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # Config
        try {
        	$this->Request->Config = NULL;
        	$this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
        	$this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # Header
        try {
        	$this->Request->Header = NULL;
        	$this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
        	$this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # Body
        try {
        	$this->Request->Body = NULL;
        	$this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
        	$this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # Undefined
        try {
            $this->Request->undefined = '';
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
        $this->assertSame(IDataMapper::UPDATED, $this->Request->setType(IRequest::DELETE), 'IRequest::setType() Should return IDataMapper::UPDATED');
        $this->assertSame(IDataMapper::UPDATED, $this->Request->setURI($this->URI), 'IRequest::setURI() Should return IDataMapper::UPDATED');
        $this->assertSame(IDataMapper::UPDATED, $this->Request->setReferer($this->URI), 'IRequest::setReferer() Should return IDataMapper::UPDATED');

        # Type
        unset($this->Request->Type);
        $this->assertFalse(isset($this->Request->Type), 'IRequest::$Type Should not exist');

        # URI
        unset($this->Request->URI);
        $this->assertFalse(isset($this->Request->URI), 'IRequest::$URI Should not exist');

        # Referer
        unset($this->Request->Referer);
        $this->assertFalse(isset($this->Request->URI), 'IRequest::$Referer Should not exist');

        # Config
        try {
            unset($this->Request->Config);
            $this->fail('Faied to genereate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
        	$this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # Header
        try {
            unset($this->Request->Header);
            $this->fail('Faied to genereate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
        	$this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # Body
        try {
            unset($this->Request->Body);
            $this->fail('Faied to genereate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
        	$this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # Undefined
        unset($this->Request->undefined);
    }
}