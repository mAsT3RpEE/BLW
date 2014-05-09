<?php
/**
 * ComponentMapableTest.php | Feb 12, 2014
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
use BadMethodCallException;

use BLW\Type\IDataMapper;

class MockComponent1089
{
    public $foo = 1;
    public $callable = array(__CLASS__, 'foo');
    public static function foo() {return 'foo';}
    public function __set($nam, $val) {throw new \Exception('foo');}
}

/**
 * Tests BLW Library Wrapper type.
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\AComponentMapable
 */
class ComponentMapableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\IComponentMapable
     */
    protected $ComponentMapable = NULL;

    /**
     * @var \BLW\MockComponent
     */
    protected $Component   = NULL;

    protected function setUp()
    {
        $this->Component        = new MockComponent1089();
        $this->ComponentMapable = $this->getMockForAbstractClass('\\BLW\\Type\\AComponentMapable');

        $Property = new ReflectionProperty($this->ComponentMapable, '_Component');

        $Property->setAccessible(true);
        $Property->setValue($this->ComponentMapable, $this->Component);

        unset($Property, $Object, $Document);
    }

    protected function tearDown()
    {
        $this->Component        = NULL;
        $this->ComponentMapable = NULL;
    }

    /**
     * @covers ::__call
     */
    public function test__call()
    {
        # Component function
        $this->assertEquals('foo', $this->ComponentMapable->foo(), 'IComponentMapable::__call() Failed to invoke $_Component->foo()');

        # Variable function
        $this->assertEquals('foo', $this->ComponentMapable->callable(), 'IComponentMapable::__call() Failed to call variable function');

        # Test Invalid call
        try {
            $this->ComponentMapable->undefined();
            $this->fail('Unable to raise exception on undefined function');
        }

        catch (BadMethodCallException $e) {}
    }

    /**
     * @covers ::__get
     */
    public function test__get()
    {
        # Test Component property
        $this->assertEquals(1, $this->ComponentMapable->foo, 'IComponentMapable::$foo should equal IComponentMapable::$_Component->foo');

        # Test undefined property
        try {
            $this->ComponentMapable->undefined;
            $this->fail('Failed to generate notice with undefined property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Undefined property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        $this->assertNull(@$this->ComponentMapable->undefined, 'IComponentMapable::$undifined should be NULL');
    }

   /**
    * @covers ::__isset
    */
   public function test__isset()
   {
       # Test dynamic property
       $this->assertTrue(isset($this->ComponentMapable->foo), 'ComponentMapable::$foo should exist.');

        # Test undefined property
       $this->assertFalse(isset($this->ComponentMapable->bar), 'ComponentMapable::$bar shouldn\'t exist.');
  }

    /**
     * @covers ::__set
     */
    public function test__set()
    {
        # Component property
        $this->ComponentMapable->foo = 100;
        $this->assertEquals(100, $this->ComponentMapable->foo, 'ComponentMapable::$foo failed to update component.');

        # Undefined property
        try {
            $this->ComponentMapable->undefined = 1;
            $this->fail('Failed to generate notice with undefined property');
        }

        catch (\PHPUnit_Framework_Error_Warning $e) {}

        @$this->ComponentMapable->undefined = 1;
    }

    /**
     * @covers ::__unset
     */
    public function test_unset()
    {
        # Component property
        unset($this->ComponentMapable->foo);

        $this->assertFalse(isset($this->ComponentMapable->foo), 'unset(IComponentMapable::$foo) Failed to delete property from component');

        # undefined
        unset($this->ComponentMapable->undefined);
    }
}