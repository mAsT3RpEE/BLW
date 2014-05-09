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
namespace BLW\Type;

use ArrayObject;
use ReflectionProperty;
use BLW\Type\IDataMapper;
use BLW\Model\InvalidArgumentException;


/**
 * Tests BLW Library Object type.
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 *  @coversDefaultClass \BLW\Type\AObject
 */
class ObjectTest extends \BLW\Type\IterableTest
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
        $this->Object     = $this->getMockForAbstractClass('\\BLW\\Type\\AObject', array($this->DataMapper, 'Test'), '', true, true, true, array('serialize'));
        $this->Iterable   = $this->Object;

        $Status = new ReflectionProperty($this->Object, '_Status');

        $Status->setAccessible(true);
        $Status->setValue($this->Object, -1);

        $this->Object
            ->expects($this->any())
            ->method('serialize')
            ->will($this->returnValue('foo'));

        $this->DataMapper
            ->expects($this->any())
            ->method('offsetGet')
            ->will($this->returnCallback(function($name){

                if ($name == 'foo')
                    return 1;

                elseif ($name == 'callable')
                    return function(){return -1;};

                else
                    trigger_error('Undefined property '. $name);
            }));

        $this->DataMapper
            ->expects($this->any())
            ->method('offsetExists')
            ->will($this->returnCallback(function($name){

                if ($name == 'foo' || $name == 'callable')
                    return true;
                else
                    return false;
            }));

        $this->DataMapper
            ->expects($this->any())
            ->method('offsetSet')
            ->will($this->returnCallback(function($name){

                switch($name)
                {
                    case 'foo':
                        return IDataMapper::UPDATED;
                    case 'readonly':
                        return IDataMapper::READONLY;
                    case 'invalid':
                        return IDataMapper::INVALID;
                    case 'undefined':
                        return IDataMapper::UNDEFINED;
                }

            }));
    }

    public function tearDown()
    {
        $this->DataMapper = NULL;
        $this->Object     = NULL;
        $this->Iterable   = NULL;
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
        # valid value
        foreach (range(0,10) as $i) {
            $this->assertRegExp('/BLW_[0-9a-z]+/', $this->Object->createID($Input), 'Invalid ID generated');
        }

        # Invalid value
        try {
            $this->Object->createID(array());
            $this->fail('Failed to generate exception with invalid arguments');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        #Test NULL ID
        $foo = $this->getMockForAbstractClass('\\BLW\\Type\\AObject', array($this->DataMapper, NULL));

        $this->assertRegExp('/BLW_[0-9a-f]+/', $foo->getID(), 'ID should match the pattern: `/BLW_[0-9a-f]+/`');

        #Test string ID
        $foo = $this->getMockForAbstractClass('\\BLW\\Type\\AObject', array($this->DataMapper, 'bar'));

        $this->assertEquals('bar', $foo->getID(), 'ID should be `bar`');

        # Test invalid ID
        try {
            $foo = $this->getMockForAbstractClass('\\BLW\\Type\\AObject', array($this->DataMapper, array()));
            $this->fail('Unable to generate exception on invalid $ID');
        }

        catch(InvalidArgumentException $e) {}
    }

	/**
	 * @depends test_construct
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
	 * @covers ::__call
	 */
	public function test_call()
	{
	    # Variable functions
	    $this->assertSame(-1, $this->Object->callable(), 'IObject::__call() Failed to convert variable function');

	    # Undefined
	    try {
	        $this->Object->undefined();
	        $this->fail('Failed to generate warning with undefined method');
	    }

	    catch (\PHPUnit_Framework_Error_Warning $e) {
	        $this->assertContains('non-existant method', $e->getMessage(), 'Invalid warning: '. $e->getMessage());
	    }

        @$this->Object->undefined();
	}

    /**
     * @depends test_construct
     * @covers ::__get
     */
    public function test_get()
    {
	    # Status
        $this->assertSame($this->readAttribute($this->Object, '_Status'), $this->Object->Status, 'IObject::$Status should equal IObject::_Status');

	    # Serializer
	    $this->assertSame($this->Object->getSerializer(), $this->Object->Serializer, 'IObject::$Serializer should equal IObject::getSerializer()');

	    # Parent
        $this->assertSame($this->Object->getParent(), $this->Object->Parent, 'IObject::$Parent should equal IObject::getParent()');

        # ID
        $this->assertSame( $this->Object->getID(), $this->Object->ID, 'IObject::$ID should equal IObject::getID()');

        # DataMapped
        $this->assertSame(1, $this->Object->foo, 'IObject::$foo should be 1');

        # Undefined
        try {
            $this->Object->undefined;
            $this->fail('Failed to generate notice with undefined property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {}

        $this->assertNull(@$this->Object->undefined, 'IObject::$undefined should be NULL');
    }

   /**
     * @depends test_construct
     * @covers ::__isset
     */
    public function test_isset()
    {
        # Status
       $this->assertTrue(isset($this->Object->Serializer), 'IObject::$Status should exist');

	    # Serializer
	    $this->assertTrue(isset($this->Object->Serializer), 'IObject::$Serializer should exist');

	    # Parent
        $this->assertFalse(isset($this->Object->Parent), 'IObject::$Parent should not exist');

	    # ID
        $this->assertTrue(isset($this->Object->ID), 'IObject::$ID should exist');

        # DataMapped
        $this->assertTrue(isset($this->Object->foo), 'IObject::$foo should exist');

        # Undefined
        $this->assertFalse(isset($this->Object->undefined), 'IObject::$undefined should not exist');
    }

    /**
     * @depends test_get
     * @covers ::__set
     */
    public function test_set()
    {
        # Status
        try {
            $this->Object->Status = 0;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Object->Status = 0;

        # Serializer
        try {
            $this->Object->Serializer = 0;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Object->Serializer = 0;

        # Parent
        $Parent                = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Object->Parent = $Parent;

        $this->assertSame($Parent, $this->Object->Parent, 'IObject::$Parent should equal IObject::getParent()');

        try {
            $this->Object->Parent = null;
            $this->fail('Failed to generate notice with invalid value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Object->Parent = null;

        try {
            $this->Object->Parent = $Parent;
            $this->fail('Failed to generate notice with oneshot value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # ID
        $this->Object->ID = 'foo';

        $this->assertSame($this->Object->ID, 'foo', 'IObject::$ID should equal `foo');

        try {
            $this->Object->ID = null;
            $this->fail('Failed to generate notice with invalid value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Object->ID = null;

        # Undefined
        try {
            $this->Object->undefined= null;
            $this->fail('Failed to generate notice with undefined property');
        }

        catch (\PHPUnit_Framework_Error_Warning $e) {
            $this->assertContains('non-existant property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Object->undefined = null;

        # Readonly
        try {
            $this->Object->readonly= null;
            $this->fail('Failed to generate notice with readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Object->readonly = null;

        # Invalid
        try {
            $this->Object->invalid = null;
            $this->fail('Failed to generate notice with readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Object->invalid = null;
    }

    /**
     * @depends test_get
     * @depends test_set
     * @covers ::__unset
     */
    public function test_unset()
    {
        # Parent
        $this->Object->Parent = $this->getMockForAbstractClass('\\BLW\Type\AObject');

        unset($this->Object->Parent);

        $this->assertNull($this->Object->Parent, 'unset(IObject::$Parent) Did not reset $_Parent');

        # Status
        unset($this->Object->Status);

        $this->assertSame(0, $this->Object->Status, 'unset(IObject::$Status) Did not reset $_Status');

        # Undefined
        unset($this->Object->undefined);
    }

    /**
     * @covers ::__toString
     */
	public function test__toString()
	{
        global $BLW_Serializer;

        $BLW_Serializer = new \BLW\Model\Serializer\Mock;

        $this->assertNotEmpty(strval($this->Object), '(string) IObject should not be empty');
	    $this->assertInternalType('string', $this->Object->__toString(), 'IObject::__toString() Returned an invalid value');
	}
}
