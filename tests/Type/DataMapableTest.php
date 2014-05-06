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
namespace BLW\Tests\Type;

use BLW\Type\IDataMapper;
use ReflectionObject;
use PHPUnit_Framework_Error_Notice;
use PHPUnit_Framework_Error;

/**
 * Tests BLW Library Adaptor type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\IDataMapable
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
        }

        else trigger_error('Undefined property', E_USER_NOTICE);

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
        switch($name)
        {
        	case 'foo1': return IDataMapper::UPDATED;
        	case 'foo2': return IDataMapper::READONLY;
        	case 'foo3': return IDataMapper::ONESHOT;
        	case 'foo4': return IDataMapper::INVALID;
        	case 'foo5': return IDataMapper::UNDEFINED;
        }
    }

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

        $Object   = new ReflectionObject($this->DataMapable);
        $Property = $Object->getProperty('_DataMapper');

        $Property->setAccessible(true);
        $Property->setValue($this->DataMapable, $this->DataMapper);
        $Property->setAccessible(false);

        unset($Property, $Object);
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
        $this->assertEquals(1, $this->DataMapable->foo, 'DataMapable::$foo should equal 1.');

        # Test undefined property
        try { $this->DataMapable->bar; }

        catch (PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Undefined property', $e->getMessage());
        }
   }

   /**
    * @covers ::__isset
    */
   public function test__isset()
   {
       # Test dynamic property
       $this->assertTrue(isset($this->DataMapable->foo), 'DataMapable::$foo should exist.');

        # Test undefined property
       $this->assertFalse(isset($this->DataMapable->bar), 'DataMapable::$bar shouldn\'t exist.');
  }

    /**
     * @covers ::__set
     */
    public function test__set()
    {
        # Test dynamic property
        $this->DataMapable->foo1 = 1;
        $this->assertEquals(1, $this->DataMapable->foo1, 'DataMapable::$foo should equal 1');

        # Test readonly property
        try {
            $this->DataMapable->foo2 = 1;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        # Test singleshot property
        try {
            $this->DataMapable->foo3 = 1;
            $this->fail('Failed to generate warning on singleshot property');
        }

        catch (PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        # Test invalid value
        try {
            $this->DataMapable->foo4 = 1;
            $this->fail('Failed to generate warning on invalid value');
        }

        catch (PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        # Test undefined property
        try {
            $this->DataMapable->foo5 = 1;
            $this->fail('Failed to generate notice on undefined property');
        }

        catch (PHPUnit_Framework_Error $e) {
            $this->assertContains('Tried to modify non-existant property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

    }
}