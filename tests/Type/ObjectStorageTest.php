<?php
/**
 * ObjectStorageTest.php | Feb 12, 2014
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
use stdClass;
use DOMElement;
use BLW\Type\IDataMapper;

/**
 * Tests BLW Library Adaptor type.
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 *  @coversDefaultClass \BLW\Type\AObjectStorage
 */
class ObjectStorageTest extends \BLW\Type\SerializableTest
{
    /**
     * @var \BLW\Type\IObjectStorage
     */
    protected $ObjectStorage = NULL;

    protected function setUp()
    {
        $this->ObjectStorage = $this->getMockForAbstractClass('\\BLW\\Type\\AObjectStorage');
        $this->Serializable  = $this->ObjectStorage;
        $this->Serializer    = new \BLW\Model\Serializer\Mock;
    }

    protected function tearDown()
    {
        $this->ObjectStorage = NULL;
        $this->Serializer    = NULL;
        $this->Serializable  = NULL;
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->ObjectStorage[new stdClass] = 1;
        $this->ObjectStorage[new stdClass] = 1;
        $this->ObjectStorage[new stdClass] = 'test';
        $this->ObjectStorage[new stdClass] = new DOMElement('span', 'test');

        $this->assertEquals('[IObjectStorage:stdClass,stdClass,stdClass,stdClass]', strval($this->ObjectStorage),'(string) IObjectStorage returned an invalid format');
    }

    /**
     * @covers ::getParent
     */
    public function test_getParent()
    {
        $Expected = $this->getMockForAbstractClass('\\BLW\\Type\\IObject');
        $Property = new ReflectionProperty($this->ObjectStorage, '_Parent');

        $Property->setAccessible(true);
        $Property->setValue($this->ObjectStorage, $Expected);

        $this->assertSame($Expected, $this->ObjectStorage->getParent(), 'IObjectStorage::getParent() should equal $_Parent.');
    }

    /**
     * @covers ::setParent
     */
    public function test_setParent()
    {
        $Expected = $this->getMockForAbstractClass('\\BLW\\Type\\IObject');

        // Valid arguments
        $this->assertEquals(IDataMapper::UPDATED, $this->ObjectStorage->setParent($Expected), 'IObjectStorage::setParent() did not return IDataMapper::UPDATED');
        $this->assertEquals(IDataMapper::ONESHOT, $this->ObjectStorage->setParent($Expected), 'IObjectStorage::setParent() should return IDataMapper::ONESHOT');

        $this->assertSame($Expected, $this->ObjectStorage->getParent(), 'IObjectStorage::setParent() Failed to update $_Parent');

        // Invalid arguments
        $this->assertEquals(IDataMapper::INVALID, $this->ObjectStorage->setParent($this->ObjectStorage), 'IObjectStorage::setParent() should return IDataMapper::INVALID');
        $this->assertEquals(IDataMapper::INVALID, $this->ObjectStorage->setParent(null), 'IObjectStorage::setParent() should return IDataMapper::ONESHOT');
   }

   /**
    * @depends test_setParent
    * @covers ::clearParent
    */
    public function test_clearParent()
    {
        $Parent = $this->getMockForAbstractClass('\\BLW\\Type\\IObject');

        $this->assertEquals(IDataMapper::UPDATED, $this->ObjectStorage->clearParent($Parent), 'clearParent did not return IDataMapper::UPDATED');
        $this->assertEquals(IDataMapper::UPDATED, $this->ObjectStorage->setParent($Parent), 'setParent did not return IDataMapper::UPDATED');
        $this->assertEquals(IDataMapper::UPDATED, $this->ObjectStorage->clearParent($Parent), 'clearParent did not return IDataMapper::UPDATED');
        $this->assertNull($this->ObjectStorage->getParent(), 'getParent() should return NULL.');
        $this->assertEquals(IDataMapper::UPDATED, $this->ObjectStorage->setParent($Parent), 'setParent did not return IDataMapper::UPDATED');
   }

   /**
     * @covers ::getID
     */
    public function test_getID()
    {
        $this->ObjectStorage[new stdClass] = 1;
        $this->ObjectStorage[new stdClass] = 1;
        $this->ObjectStorage[new stdClass] = 'test';
        $this->ObjectStorage[new stdClass] = new DOMElement('span', 'test');

        $this->assertEquals('d49b84b773cb6bc2fe49c6c478fa6b4a', strval($this->ObjectStorage->getID()),'IObjectStorage::getID() returned an invalid value');
    }

    /**
     * @covers ::__get
     */
    public function test_get()
    {
        # Make property readable / writable
        $Status = new ReflectionProperty($this->ObjectStorage, '_Status');
        $Status->setAccessible(true);

        # Status
        $this->assertSame($this->ObjectStorage->Status, $Status->getValue($this->ObjectStorage), 'IObjectStorage::$Status should equal IObjectStorage::_Status');

        # Serializer
        $this->assertSame($this->ObjectStorage->Serializer, $this->ObjectStorage->getSerializer(), 'IObjectStorage::$Serializer should equal IObjectStorage::getSerializer()');

        # Parent
        $this->assertSame($this->ObjectStorage->getParent(), $this->ObjectStorage->Parent, 'IObjectStorage::$Parent should equal IObjectStorage::getParent()');

        # ID
        $this->assertSame($this->ObjectStorage->ID, $this->ObjectStorage->getID(), 'IObjectStorage::$ID should equal IObjectStorage::getID()');

        # Undefined
        try {
            $this->ObjectStorage->undefined;
            $this->fail('Failed to generate notice with undefined property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {}

        $this->assertNull(@$this->ObjectStorage->undefined, 'IObjectStorage::$undefined should be NULL');
    }

    /**
     * @covers ::__isset
     */
    public function test_isset()
    {
        # Status
       $this->assertTrue(isset($this->ObjectStorage->Serializer), 'IObjectStorage::$Status should exist');

        # Serializer
        $this->assertTrue(isset($this->ObjectStorage->Serializer), 'IObjectStorage::$Serializer should exist');

        # Parent
        $this->assertFalse(isset($this->ObjectStorage->Parent), 'IObjectStorage::$Parent should not exist');

        # ID
        $this->assertTrue(isset($this->ObjectStorage->ID), 'IObjectStorage::$ID should exist');

        # Undefined
        $this->assertFalse(isset($this->ObjectStorage->undefined), 'IObjectStorage::$undefined should not exist');
    }

    /**
     * @depends test_get
     * @covers ::__set
     */
    public function test_set()
    {
        # Status
        try {
            $this->ObjectStorage->Status = 0;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->ObjectStorage->Status = 0;

        # Serializer
        try {
            $this->ObjectStorage->Serializer = 0;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->ObjectStorage->Serializer = 0;

        # Parent
        $Parent = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->ObjectStorage->Parent = $Parent;

        $this->assertSame($Parent, $this->ObjectStorage->Parent, 'IObjectStorage::$Parent should equal IObjectStorage::getParent()');

        try {
            $this->ObjectStorage->Parent = null;
            $this->fail('Failed to generate notice with invalid value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->ObjectStorage->Parent = null;

        try {
            $this->ObjectStorage->Parent = $Parent;
            $this->fail('Failed to generate notice with oneshot value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # ID
        try {
            $this->ObjectStorage->ID = 'foo';
            $this->fail('Failed to generate notice with readonly value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->ObjectStorage->ID = 'foo';

        # Undefined
        try {
            $this->ObjectStorage->undefined = '';
            $this->fail('Failed to generate notice with undefined property');
        }

        catch (\PHPUnit_Framework_Error $e) {
            $this->assertContains('non-existant property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->ObjectStorage->undefined = '';
    }

    /**
     * @depends test_get
     * @depends test_set
     * @covers ::__unset
     */
    public function test_unset()
    {
        # Parent
        $this->ObjectStorage->Parent = $this->getMockForAbstractClass('\\BLW\Type\IObject');

        unset($this->ObjectStorage->Parent);

        $this->assertNull($this->ObjectStorage->Parent, 'unset(IObjectStorage::$Parent) Did not reset $_Parent');

        # Status
        unset($this->ObjectStorage->Status);

        $this->assertSame(0, $this->ObjectStorage->Status, 'unset(IObjectStorage::$Status) Did not reset $_Status');

        # Undefined
        unset($this->ObjectStorage->undefined);
    }
}
