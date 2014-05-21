<?php
/**
 * WrapperTest.php | Feb 12, 2014
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
namespace BLW\Type;

use DOMDocument;
use ReflectionProperty;
use PHPUnit_Framework_Error_Notice;
use BadMethodCallException;


class MockComponent2073
{
    public $foo = 1;
    public $callable = array(__CLASS__, 'foo');
    public static function foo() {return 'foo';}
    public function __set($nam, $val) {throw new \Exception('foo');}
}

/**
 * Tests BLW Library Adaptor type.
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 *  @coversDefaultClass \BLW\Type\AWrapper
 */
class WrapperTest extends \BLW\Type\IterableTest
{
    /**
     * @var \BLW\Type\IWrapper
     */
    protected $Wrapper = NULL;

    /**
     * @var \DOMElement
     */
    protected $Component = NULL;

    protected function setUp()
    {
        $this->Component    = new MockComponent2073;
        $this->Wrapper      = $this->getMockForAbstractClass('\\BLW\\Type\\AWrapper', array($this->Component));
        $this->Iterable     = $this->Wrapper;

        $Status = new ReflectionProperty($this->Wrapper, '_Status');

        $Status->setAccessible(true);
        $Status->setValue($this->Wrapper, -1);

        $this->Wrapper
            ->expects($this->any())
            ->method('getID')
            ->will($this->returnValue('IWrapper'));
    }

    protected function tearDown()
    {
        $this->Component   = NULL;
        $this->Wrapper     = NULL;
        $this->Iterable    = NULL;
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        $Document   = new DOMDocument('1.0', 'UTF-8');
        $Component  = $Document->createElement('span', 'foo');
        $Wrapper    = $this->getMockForAbstractClass('\\BLW\\Type\\AWrapper', array($Component));

        # Check properties
        $this->assertAttributeSame($Component, '_Component', $Wrapper, 'IWrapper::__construct() Failed to set component');
    }

    /**
     * @covers ::getInstance
     */
    public function test_getInstance()
    {
        $Copy   = $this->Wrapper->getInstance($this->Component);
        $Status = new ReflectionProperty($Copy, '_Status');

        $Status->setAccessible(true);
        $Status->setValue($Copy, -1);

        $this->assertEquals($Copy, $this->Wrapper, 'IWrapper::getInstance() returned invalid value');
    }

    /**
     * @covers ::__call
     */
    public function test__call()
    {
        # Component function
        $this->assertEquals('foo', $this->Wrapper->foo(), 'IWrapper::__call() Failed to invoke $_Component->foo()');

        # Variable function
        $this->assertEquals('foo', $this->Wrapper->callable(), 'IWrapper::__call() Failed to call variable function');

        # Test Invalid call
        try {
            $this->Wrapper->undefined();
            $this->fail('Unable to raise exception on undefined function');
        } catch (BadMethodCallException $e) {}
    }

    /**
     * @depends test_construct
     * @covers ::__get
     */
    public function test_get()
    {
        # Status
        $this->assertAttributeSame($this->Wrapper->Status, '_Status', $this->Wrapper, 'IWrapper::$Status should equal IWrapper::_Status');

        # Serializer
        $this->assertSame( $this->Wrapper->getSerializer(), $this->Wrapper->Serializer,'IWrapper::$Serializer should equal IWrapper::getSerializer()');

        # Parent
        $this->assertSame($this->Wrapper->getParent(), $this->Wrapper->Parent, 'IWrapper::$Parent should equal IWrapper::getParent()');

        # ID
        $this->assertSame($this->Wrapper->getID(), $this->Wrapper->ID, 'IWrapper::$ID should equal IWrapper::getID()');

        # Component
        $this->assertSame($this->Component, $this->Wrapper->Component, 'IWrapper::$Component should equal $_Component');

        # Test Component property
        $this->assertEquals(1, $this->Wrapper->foo, 'IWrapper::$foo should equal IWrapper::$_Component->foo');

        # Test undefined property
        try {
            $this->Wrapper->undefined;
            $this->fail('Failed to generate notice with undefined property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Undefined property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        $this->assertNull(@$this->Wrapper->undefined, 'IWrapper::$undifined should be NULL');
    }

   /**
    * @depends test_construct
    * @covers ::__isset
    */
   public function test__isset()
   {
        # Status
        $this->assertTrue(isset($this->Wrapper->Serializer), 'IWrapper::$Status should exist');

        # Serializer
        $this->assertTrue(isset($this->Wrapper->Serializer), 'IWrapper::$Serializer should exist');

        # Parent
        $this->assertFalse(isset($this->Wrapper->Parent), 'IWrapper::$Parent should not exist');

        # ID
        $this->assertTrue(isset($this->Wrapper->ID), 'IWrapper::$ID should exist');

        # Component
        $this->assertTrue(isset($this->Wrapper->Component), 'IWrapper::$Component should exist');

        # Test component property
       $this->assertTrue(isset($this->Wrapper->foo), 'Wrapper::$foo should exist.');

        # Test undefined property
       $this->assertFalse(isset($this->Wrapper->bar), 'Wrapper::$bar shouldn\'t exist.');
  }

    /**
     * @covers ::__set
     */
    public function test__set()
    {
        # Status
        try {
            $this->Wrapper->Status = 0;
            $this->fail('Failed to generate notice on readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Wrapper->Status = 0;

        # Serializer
        try {
            $this->Wrapper->Serializer = 0;
            $this->fail('Failed to generate notice on readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Wrapper->Serializer = 0;

        # Parent
        $Parent                = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Wrapper->Parent = $Parent;

        $this->assertSame($Parent, $this->Wrapper->Parent, 'IWrapper::$Parent should equal IWrapper::getParent()');

        try {
            $this->Wrapper->Parent = null;
            $this->fail('Failed to generate notice with invalid value');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Wrapper->Parent = null;

        try {
            $this->Wrapper->Parent = $Parent;
            $this->fail('Failed to generate notice with oneshot value');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # ID
        try {
            $this->Wrapper->ID = 'foo';
            $this->fail('Failed to generate notice with invalid value');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Wrapper->ID = 'foo';

        # ID
        try {
            $this->Wrapper->Component = null;
            $this->fail('Failed to generate notice with invalid value');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Wrapper->Component = null;

        # Component property
        $this->Wrapper->foo = 100;
        $this->assertEquals(100, $this->Wrapper->foo, 'Wrapper::$foo failed to update component.');

        # Undefined property
        try {
            $this->Wrapper->undefined = 1;
            $this->fail('Failed to generate notice with undefined property');
        } catch (\PHPUnit_Framework_Error_Warning $e) {}

        @$this->Wrapper->undefined = 1;
    }

    /**
     * @covers ::__unset
     */
    public function test_unset()
    {
        # Parent
        $this->Wrapper->Parent = $this->getMockForAbstractClass('\\BLW\Type\IObject');

        unset($this->Wrapper->Parent);

        $this->assertNull($this->Wrapper->Parent, 'unset(IWrapper::$Parent) Did not reset $_Parent');

        # Status
        unset($this->Wrapper->Status);

        $this->assertSame(0, $this->Wrapper->Status, 'unset(IWrapper::$Status) Did not reset $_Status');

        # Component property
        unset($this->Wrapper->foo);

        $this->assertFalse(isset($this->Wrapper->foo), 'unset(IWrapper::$foo) Failed to delete property from component');

        # undefined
        unset($this->Wrapper->undefined);
    }

    /**
     * @covers ::__toString
     */
    public function test__toString()
    {
        $Expected = '!\x5b[\x30-\x39\x41-\x5a\x5f\x61-\x7a\x5f]+\x3aBLW\x5cType\x5cMockComponent2073\x5d!';

        $this->assertRegExp($Expected, @strval($this->Wrapper), 'strval(IWrapper) should equal `[IWrapper::MockComponent2073]`');
    }
}
