<?php
/**
 * ObjectTest.php | Dec 30, 2013
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

use ReflectionProperty;
use PHPUnit_Framework_Error_Notice;
use BLW\Type\IDataMapper;
use BLW\Model\InvalidArgumentException;


/**
 * Tests BLW Library Object type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 *  @coversDefaultClass \BLW\Type\AObject
 */
class ObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\IDataMapper
     */
    protected $DataMapper = NULL;

    /**
     * @var \BLW\Type\IObject
     */
    protected $Object = NULL;

    public function setUp()
    {
        $this->DataMapper = $this->getMockForAbstractClass('\\BLW\\Type\\IDataMapper');
        $this->Object     = $this->getMockForAbstractClass('\\BLW\\Type\\AObject', array($this->DataMapper, 'Test'));

        $Status = new ReflectionProperty($this->Object, '_Status');
        $Status->setAccessible(true);
        $Status->setValue($this->Object, -1);
    }

    public function tearDown()
    {
        $this->DataMapper = NULL;
        $this->Object     = NULL;
    }

    /**
     * @covers ::getID
     */
    public function test_getID()
    {
        $this->assertEquals('Test', $this->Object->getID(), 'ID should initially equal `Test`');
    }

    public function generateInputs()
    {
        return array(
             array(NULL)
            ,array(1)
            ,array(1.0)
            ,array('bar')
            ,array(true)
            ,array(false)
            ,array(new \SplFileInfo(__FILE__))
        );

    }

    /**
	 * @dataProvider generateInputs
     * @covers ::createID
     */
    public function test_createID($Input)
    {
        foreach (range(0,10) as $i) {
            $this->assertRegExp('/BLW_[0-9a-z]+/', $this->Object->createID($Input), 'Invalid ID generated');
        }
    }

    /**
     * @depends test_getID
     * @covers ::__construct
     */
    public function test__construct()
    {
        # Test invalid ID
        try {
            $foo = $this->getMockForAbstractClass('\\BLW\\Type\\AObject', array($this->DataMapper, array()));
            $this->fail('Unable to generate exception on invalid $ID');
        }

        catch(InvalidArgumentException $e) {}

        #Test NULL ID
        $foo = $this->getMockForAbstractClass('\\BLW\\Type\\AObject', array($this->DataMapper, NULL));
        $this->assertRegExp('/BLW_[0-9a-f]+/', $foo->getID(), 'ID should match the pattern: `/BLW_[0-9a-f]+/`');

        #Test string ID
        $foo = $this->getMockForAbstractClass('\\BLW\\Type\\AObject', array($this->DataMapper, 'bar'));
        $this->assertEquals('bar', $foo->getID(), 'ID should be `bar`');
    }

	/**
	 * @depends test__construct
     * @covers ::getInstance
	 */
	public function test_getInstance()
	{
	    $class = get_class($this->Object);

	    $this->assertInstanceOf('\\BLW\\Type\\IObject', $class::getInstance($this->DataMapper), '$this->Object should be an instance of IObject');
	}

	public function generateIDs()
	{
	    return array(
	    	 array(1,       NULL)
            ,array(1.0,     array())
            ,array('bar',   new \DOMNode)
            ,array(true,    NULL)
            ,array(false,   NULL)
            ,array(new \SplFileInfo(__FILE__), NULL)
	    );
	}

	/**
	 * @dataProvider generateIDs
	 * @depends test_getID
     * @covers ::setID
	 */
	public function test_setID($Valid, $Invalid)
	{
	    $this->assertNotEquals($Valid, $this->Object->getID(), 'ID should not initially equal $ID');
	    $this->assertEquals(IDataMapper::UPDATED, $this->Object->setID($Valid), 'setID should return IDataMapper::UPDATED');
	    $this->assertEquals($Valid, $this->Object->getID(), 'ID should now equal $ID');

	    # Test invalid ID
	    $this->assertEquals(IDataMapper::INVALID, $this->Object->setID($Invalid), 'setID should return IDataMapper::INVALID');
	}

	/**
	 * @covers ::clearStatus
	 */
	public function test_clearStatus()
	{
	    # Make property readable / writable
	    $Status = new ReflectionProperty($this->Object, '_Status');
	    $Status->setAccessible(true);

	    # Set value of status
	    $this->assertEquals(-1, $Status->getValue($this->Object), 'IObject::$_Status should equal -1');

	    # Clear value
	    $this->assertEquals(IDataMapper::UPDATED, $this->Object->clearStatus(), 'IObject::clearStatus() should return IDataMapper::UPDATED');
	    $this->assertEquals(0, $Status->getValue($this->Object), 'IObject::$_Status should be 0');

	    $Status->setValue($this->Object, -1);
	    $Status->setAccessible(false);
	}

    /**
     * @covers ::__get
     */
    public function test__get()
    {
	    # Make property readable / writable
	    $Status = new ReflectionProperty($this->Object, '_Status');
	    $Status->setAccessible(true);

	    # Status
        $this->assertSame($this->Object->Status, $Status->getValue($this->Object), 'IObject::$Status should equal IObject::_Status');

	    # Serializer
	    $this->assertSame($this->Object->Serializer, $this->Object->getSerializer(), 'IObject::$Serializer should equal IObject::getSerializer()');

	    # Parent
        $this->assertNULL($this->Object->Parent, 'IObject::$Parent should initially be NULL');

        # ID
        $this->assertSame($this->Object->ID, $this->Object->getID(), 'IObject::$ID should equal IObject::getID()');

	    # Test undefined property
        try {
            $this->Object->bar;
//          $this->fail('IObject::$bar is undefined and should raise a notice');
        }

        catch (PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Undefined property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }
   }

   /**
    * @covers ::__isset
    */
   public function test__isset()
   {
        # Status
       $this->assertTrue(isset($this->Object->Serializer), 'IObject::$Status should exist');

	    # Serializer
	    $this->assertTrue(isset($this->Object->Serializer), 'IObject::$Serializer should exist');

	    # Parent
        $this->assertFalse(isset($this->Object->Parent), 'IObject::$Parent should not exist');

	    # ID
        $this->assertTrue(isset($this->Object->ID), 'IObject::$ID should exist');

        # Test undefined property
       $this->assertFalse(isset($this->Object->bar), 'IObject::$bar shouldn\'t exist');
  }

    /**
     * @covers ::__set
     */
    public function test__set()
    {
        # Status
        try {
            $this->Object->Status = 0;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        # Serializer
        try {
            $this->Object->Serializer = 0;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        # Parent
        $this->Object->Parent = clone $this->Object;
        $this->assertSame($this->Object->Parent, $this->Object->getParent(), 'IObject::$Parent should equal IObject::getParent');
        $this->assertTrue(isset($this->Object->Parent), 'IObject::$Parent should exist');

	    # ID
        $this->Object->ID = 'foo';
        $this->assertSame($this->Object->ID, 'foo', 'IObject::$ID should equal `foo');
    }

    /**
     * @covers ::__toString
     */
	public function test__toString()
	{
	    $this->assertNotEmpty(@strval($this->Object), '(string) IObject should not be empty');
	}
}
