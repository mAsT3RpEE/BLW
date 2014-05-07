<?php
/**
 * SerializerTest.php | Feb 12, 2014
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
use BLW\Type\ISerializer;
use BLW\Type\ISerializable;


/**
 * Tests BLW Library Serializer type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 *  @coversDefaultClass \BLW\Type\ASerializer
 */
class SerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\ISerializer
     */
    protected $Serializer = NULL;

    /**
     * @var \BLW\Type\ISerializable
     */
    protected $Serializable = NULL;

    protected function setUp()
    {
        $this->Serializer                        = $this->getMockForAbstractClass('\\BLW\\Type\\ASerializer');
        $this->Serializable                      = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Serializable->Child1              = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Serializable->Child2              = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Serializable->Child3              = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Serializable->Child4              = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Serializable->Child1->GrandChild1 = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Serializable->Child1->GrandChild2 = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Serializable->Child2->GrandChild3 = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Serializable->Child2->GrandChild4 = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Serializable->Child3->GrandChild5 = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Serializable->Child3->GrandChild6 = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Serializable->Child4->GrandChild1 = $this->Serializable->Child1->GrandChild1;
        $this->Serializable->Child4->GrandChild1 = $this->Serializable->Child1->GrandChild2;

        $this->Serializable->Child1->setParent($this->Serializable);
        $this->Serializable->Child2->setParent($this->Serializable);
        $this->Serializable->Child3->setParent($this->Serializable);
        $this->Serializable->Child4->setParent($this->Serializable);

        $this->Serializable->Child1->GrandChild1->setParent($this->Serializable->Child1);
        $this->Serializable->Child1->GrandChild2->setParent($this->Serializable->Child1);
        $this->Serializable->Child2->GrandChild3->setParent($this->Serializable->Child1);
        $this->Serializable->Child2->GrandChild4->setParent($this->Serializable->Child1);
        $this->Serializable->Child3->GrandChild5->setParent($this->Serializable->Child1);
        $this->Serializable->Child3->GrandChild6->setParent($this->Serializable->Child1);
    }

    protected function tearDown()
    {
        $this->Serializer   = NULL;
        $this->Serializable = NULL;
    }

    /**
     * @covers ::export
     */
    public function test_export()
    {
        $Expected = new \ArrayObject(array(
             'Child1' => $this->Serializable->Child1
            ,'Child2' => $this->Serializable->Child2
            ,'Child3' => $this->Serializable->Child3
            ,'Child4' => $this->Serializable->Child4
        ));

        $this->assertGreaterThan(4, $this->Serializer->export($this->Serializable, $Exported), 'ISerializer::export() should return 4');
        $this->assertEquals($Expected, $Exported['_DataMapper'], 'ISerializer::export() did not export object');
        $this->assertEquals($this->Serializable->getID(), $Exported['_ID'], 'ISerializer::export() did not export object');
        $this->assertEquals($this->Serializable->getParent(), $Exported['_Parent'], 'ISerializer::export() did not export object');
    }

    /**
     * @depends test_export
     * @covers ::import
     */
    public function test_import()
    {
        $New = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');

        $this->Serializer->export($this->Serializable, $Exported);
        $this->Serializer->import($New, $Exported);

        $this->assertEquals($this->Serializable, $New, 'ISerializer::import() did not import all parameters');
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertRegExp('!\\x5b.+Serializer\\x5d!', @strval($this->Serializer), '(string) ISerializer produced an invalid string');
    }

    /**
     * @covers ::getID
     */
    public function test_getID()
    {
        $this->assertNotEmpty($this->Serializer->getID(), 'ISerializer::getID() returned an invalid ID');
    }

    /**
     * @covers ::__get
     */
    public function test__get()
    {
	    # Make property readable / writable
	    $Status = new \ReflectionProperty($this->Serializer, '_Status');
	    $Status->setAccessible(true);

	    # Status
        $this->assertSame($this->Serializer->Status, $Status->getValue($this->Serializer), 'ISerializer::$Status should equal ISerializer::_Status');

	    # Serializer
	    $this->assertSame($this->Serializer->Serializer, $this->Serializer->getSerializer(), 'ISerializer::$Serializer should equal ISerializer::getSerializer()');

	    # Parent
        $this->assertNULL($this->Serializer->Parent, 'ISerializer::$Parent should initially be NULL');

        # ID
        $this->assertSame($this->Serializer->ID, $this->Serializer->getID(), 'ISerializer::$ID should equal ISerializer::getID()');

	    # Test undefined property
        try {
            $this->Serializer->bar;
            $this->fail('ISerializer::$bar is undefined and should raise a notice');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Undefined property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }
   }

   /**
    * @covers ::__isset
    */
   public function test__isset()
   {
        # Status
       $this->assertTrue(isset($this->Serializer->Serializer), 'ISerializer::$Status should exist');

	    # Serializer
	    $this->assertTrue(isset($this->Serializer->Serializer), 'ISerializer::$Serializer should exist');

	    # Parent
        $this->assertFalse(isset($this->Serializer->Parent), 'ISerializer::$Parent should not exist');

	    # ID
        $this->assertTrue(isset($this->Serializer->ID), 'ISerializer::$ID should exist');

        # Test undefined property
       $this->assertFalse(isset($this->Serializer->bar), 'ISerializer::$bar shouldn\'t exist');
  }

    /**
     * @covers ::__set
     */
    public function test__set()
    {
        # Status
        try {
            $this->Serializer->Status = 0;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        # Serializer
        try {
            $this->Serializer->Serializer = 0;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        # Parent
        $this->Serializer->Parent = $this->getMockForAbstractClass('\\BLW\\Type\\IObject');
        $this->assertSame($this->Serializer->Parent, $this->Serializer->getParent(), 'ISerializer::$Parent should equal ISerializer::getParent');
        $this->assertTrue(isset($this->Serializer->Parent), 'ISerializer::$Parent should exist');

	    # ID
        try {
            $this->Serializer->ID = 'foo';
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        # Undefined property
        try {
            $this->Serializer->bar = 0;
            $this->fail('Failed to generate notice on undefined property');
        }

        catch (\PHPUnit_Framework_Error $e) {
            $this->assertContains('Cannot modify non-existant property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }
    }
}
