<?php
/**
 * DataMapableTest.php | Feb 12, 2014
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

use ReflectionProperty;


/**
 * Tests BLW Library Adaptor type.
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\ADataMapable
 */
class DataMapableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\IDataMapable
     */
    protected $DataMapable = NULL;

    /**
     * @var \BLW\Type\IDataMapper
     */
    protected $DataMapper   = NULL;

    public function mock_get($name)
    {
        if ($name == 'foo' || $name = 'foo1') {
            return 1;
        } else trigger_error('Undefined property', E_USER_NOTICE);

        return NULL;
    }

    public function mock_isset($name)
    {
        if ($name == 'foo' || $name == 'foo1') {
            return true;
        }

        return false;
    }

    public function mock_set($name, $value)
    {
        switch ($name) {
            case 'foo1': return IDataMapper::UPDATED;
            case 'foo2': return IDataMapper::READONLY;
            case 'foo3': return IDataMapper::ONESHOT;
            case 'foo4': return IDataMapper::INVALID;
            case 'foo5': return IDataMapper::UNDEFINED;
        }
    }

    public function mock_unset($name)
    {}

    protected function setUp()
    {
        $this->DataMapper  = $this->getMockForAbstractClass('\\BLW\\Type\\IDataMapper', array(), '', false);
        $this->DataMapable = $this->getMockForAbstractClass('\\BLW\\Type\\ADataMapable');

        $this->DataMapper
            ->expects($this->any())
            ->method('offsetGet')
            ->will($this->returnCallback(array($this, 'mock_get')));

        $this->DataMapper
            ->expects($this->any())
            ->method('offsetExists')
            ->will($this->returnCallback(array($this, 'mock_isset')));

        $this->DataMapper
            ->expects($this->any())
            ->method('offsetSet')
            ->will($this->returnCallback(array($this, 'mock_set')));

        $this->DataMapper
            ->expects($this->any())
            ->method('offsetUnset')
            ->will($this->returnCallback(array($this, 'mock_unset')));

        $Property = new ReflectionProperty($this->DataMapable, '_DataMapper');

        $Property->setAccessible(true);
        $Property->setValue($this->DataMapable, $this->DataMapper);
    }

    protected function tearDown()
    {
        $this->DataMapper   = NULL;
        $this->DataMapable = NULL;
    }

    /**
     * @covers ::__get
     */
    public function test__get()
    {
        # Test dynamic property
        $this->assertEquals(1, $this->DataMapable->foo, 'IDataMapable::$foo should equal 1.');

        # Test undefined property
        try {
            $this->DataMapable->undefined;
            $this->fail('Failed to generate exception with undefined property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Undefined property', $e->getMessage());
        }

        $this->assertNull(@$this->DataMapable->undefined, 'IDataMapable::$undefined should be null');
    }

   /**
    * @covers ::__isset
    */
   public function test__isset()
   {
       # Test dynamic property
       $this->assertTrue(isset($this->DataMapable->foo), 'IDataMapable::$foo should exist.');

        # Test undefined property
       $this->assertFalse(isset($this->DataMapable->undefined), 'IDataMapable::$undefined should not exist.');
  }

    /**
     * @covers ::__set
     */
    public function test__set()
    {
        # Test dynamic property
        $this->DataMapable->foo1 = 1;

        # Test readonly property
        try {
            $this->DataMapable->foo2 = 1;
            $this->fail('Failed to generate notice on readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->DataMapable->foo2 = 1;

        # Test singleshot property
        try {
            $this->DataMapable->foo3 = 1;
            $this->fail('Failed to generate warning on singleshot property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->DataMapable->foo3 = 1;

        # Test invalid value
        try {
            $this->DataMapable->foo4 = 1;
            $this->fail('Failed to generate warning on invalid value');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->DataMapable->foo4 = 1;

        # Test undefined property
        try {
            $this->DataMapable->foo5 = 1;
            $this->fail('Failed to generate notice on undefined property');
        } catch (\PHPUnit_Framework_Error_Warning $e) {
            $this->assertContains('Tried to modify non-existant property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->DataMapable->foo5 = 1;
    }

    /**
     * @covers ::__unset()
     */
    public function test_unset()
    {
        unset($this->DataMapable->undefined);
    }
}
