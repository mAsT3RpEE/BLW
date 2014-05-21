<?php
/**
 * DataMapperTest.php | Feb 12, 2014
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

use SplFileInfo;

use BLW\Model\InvalidArgumentException;

/**
 * Tests BLW Library Adaptor type.
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 *  @coversDefaultClass \BLW\Type\ADataMapper
 */
class DataMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\IDataMapper
     */
    protected $DataMapper   = NULL;

    protected function setUp()
    {
        $this->DataMapper  = $this->getMockForAbstractClass('\\BLW\\Type\\ADataMapper');
    }

    protected function tearDown()
    {
        $this->DataMapper   = NULL;
    }

    /**
     * @covers ::getFactoryMethods
     */
    public function test_getFactoryMethods()
    {
        $this->assertNotEmpty($this->DataMapper->getFactoryMethods(), 'IDataMaper::getFactoryMethods() Returned an invalid value');
        $this->assertContainsOnlyInstancesOf('ReflectionMethod', $this->DataMapper->getFactoryMethods(), 'IDataMapper::getFactoryMethods() Returned an invalid value');
    }

    /**
     * @covers ::createRead
     */
    public function test_createRead()
    {
        # Make sure return value is an instance of closure
        $this->assertInstanceOf('Closure', $foo2 = $this->DataMapper->createRead($foo1), 'IDataMapper::createRead() should reurn an isntance of closure');

        # Make sure variable and closure have same value
        $this->assertEquals($foo1, $foo2(), 'IDataMapper::createRead() returned an invalid closure');

        # Update variable
        $foo1 = 'test';

        # Make sure closure reflects update
        $this->assertEquals($foo1, $foo2(), 'IDataMapper::createRead returned an invalid closure');
    }

    /**
     * @covers ::createWrite
     */
    public function test_createWrite()
    {
        # Make sure return value is an instance of closure
        $this->assertInstanceOf('Closure', $foo2 = $this->DataMapper->createWrite($foo1), 'IDataMapper::createWrite() should reurn an isntance of closure');

        # Make sure closure returns IDataMapper::UPDATED
        $this->assertEquals(IDataMapper::UPDATED, $foo2('test'), 'IDataMapper::createWrite() returned an invalid closure');

        # Make sure closure updated variable
        $this->assertEquals('test', $foo1, 'IDataMapper::createWrite() closure did not modify variable');
    }

    public function generateValidFields()
    {
        return array(
             array(0, ADataMapper::createRead($foo), ADataMapper::createWrite($foo))
            ,array('foo', ADataMapper::createRead($foo), ADataMapper::createWrite($foo))
            ,array(new SplFileInfo(__FILE__), ADataMapper::createRead($foo), ADataMapper::createWrite($foo))
        );
    }

    public function generateInvalidFields()
    {
        return array(
             array(NULL, ADataMapper::createRead($foo), ADataMapper::createWrite($foo), 0)
            ,array('foo', NULL, ADataMapper::createWrite($foo), 0)
            ,array('foo',  ADataMapper::createRead($foo), NULL, 0)
        );
    }

    /**
     * @depends test_createRead
     * @depends test_createWrite
     * @covers ::__setField
     */
    public function test_setField()
    {
        # Valid arguments
        foreach ($this->generateValidFields() as $Arguments) {

            list ($name, $read, $write) = $Arguments;

            $this->assertTrue($this->DataMapper->__setField($name, $read, $write), 'IDataMapper::__setField() should return true.');
        }

        # Test invalid arguments
        foreach ($this->generateInvalidFields() as $Arguments) {

            list ($name, $read, $write, $flags) = $Arguments;

            try {
                $this->DataMapper->__setField($name, $read, $write, $flags);
                $this->fail('Failed to generate exception with invalid parameter');
            } catch (InvalidArgumentException $e) {}
        }
    }

    /**
     * @depends test_setField
     * @covers ::__loadFields
     */
   public function test_loadFields()
   {
        $Valid = array(
             array('foo', ADataMapper::createRead($foo), ADataMapper::createWrite($foo))
            ,array('bar', ADataMapper::createRead($bar), ADataMapper::createWrite($bar))
        );

        $Invalid = array(
                array(NULL, ADataMapper::createRead($foo), ADataMapper::createWrite($foo))
                ,array(NULL, ADataMapper::createRead($bar), ADataMapper::createWrite($bar))
        );

        # Test valid arguments
        $this->assertTrue($this->DataMapper->__loadFields($Valid), 'IDataMapper::__loadFields() should return true.');

        # Test invalid arguments
        try {
            $this->DataMapper->__loadFields($Invalid);
            $this->fail('Failed to generate exception with invalid parameter');
        } catch (InvalidArgumentException $e) {}
    }

    public function generateFields(&$called)
    {
        $Empty     = function () {return true;};
        $Updated   = function () use (&$called) {$called++; return IDataMapper::UPDATED;};
        $Readonly  = function () use (&$called) {$called++; return IDataMapper::READONLY;};
        $WriteOnly = function () use (&$called) {$called++; return IDataMapper::WRITEONLY;};
        $OneShot   = function () use (&$called) {$called++; return IDataMapper::ONESHOT;};
        $Invalid   = function () use (&$called) {$called++; return IDataMapper::INVALID;};
        $Undefined = function () use (&$called) {return IDataMapper::UNDEFINED;};

        return array(
             array('foo1', $Empty, $Updated)
            ,array('foo2', $Empty, $Readonly)
            ,array('foo3', $Empty, $WriteOnly)
            ,array('foo4', $Empty, $OneShot)
            ,array('foo5', $Empty, $Invalid)
        );

    }

    /**
     * @depends test_loadFields
     * @covers ::offsetGet
     */
    public function test_offsetExists()
    {
        # Load test data
        $called    = 0;
        $Fields    = $this->generateFields($called);

        $this->assertTrue($this->DataMapper->__loadFields($Fields), 'IDataMapper::__loadFields() should return true');
        $this->assertArrayHasKey('foo1', $this->DataMapper, 'IDataMapper[foo1] should exist');
        $this->assertArrayHasKey('foo2', $this->DataMapper, 'IDataMapper[foo2] should exist');
        $this->assertArrayHasKey('foo3', $this->DataMapper, 'IDataMapper[foo3] should exist');
        $this->assertArrayHasKey('foo4', $this->DataMapper, 'IDataMapper[foo4] should exist');
        $this->assertArrayHasKey('foo5', $this->DataMapper, 'IDataMapper[foo5] should exist');

        # Undefined
        $this->assertArrayNotHasKey('undefined', $this->DataMapper, 'IDataMapper[undefined] should not exist');
    }

   /**
     * @depends test_loadFields
     * @covers ::offsetGet
     */
    public function test_offsetGet()
    {
        # Load test data
        $Empty  = function () {};
        $Read   = function () use (&$called) {$called = true; return 'test';};
        $Fields = array(
            array('foo', $Read, $Empty)
        );

        $this->assertTrue($this->DataMapper->__loadFields($Fields), 'IDataMapper::__loadFields() should return true');
        $this->assertEquals('test', $this->DataMapper['foo'], 'IDataMapper[foo] should return `test`');
        $this->assertTrue($called, 'IDataMapper::offsetGet() Failed to call callback');

        # Undefined
        $this->assertNull($this->DataMapper['undefined'], 'IDataMapper[undefined] should return NULL');
    }

    /**
     * @depends test_loadFields
     * @covers ::offsetSet
     */
    public function test_offsetSet()
    {
        # Load test data
        $called = 0;
        $Fields = $this->generateFields($called);

        $this->assertTrue($this->DataMapper->__loadFields($Fields), 'IDataMapper::__loadFields() should return true');

        # Test updatable property
        $this->assertEquals(IDataMapper::UPDATED, $this->DataMapper->offsetSet('foo1', NULL), 'IDataMapper::offsetSet() should return IDataMapper::UPDATED');
        $this->assertSame(1, $called, 'IDataMapper::offsetSet() Failed to call callback');

        # Test readonly property
        $this->assertEquals(IDataMapper::READONLY, $this->DataMapper->offsetSet('foo2', NULL), 'IDataMapper::offsetSet() should return IDataMapper::READONLY');
        $this->assertSame(2, $called, 'IDataMapper::offsetSet() Failed to call callback');

        # Test writeonly property
        $this->assertEquals(IDataMapper::WRITEONLY, $this->DataMapper->offsetSet('foo3', NULL), 'IDataMapper::offsetSet() should return IDataMapper::WRITEONLY');
        $this->assertSame(3, $called, 'IDataMapper::offsetSet() Failed to call callback');

        # Test oneshot property
        $this->assertEquals(IDataMapper::ONESHOT, $this->DataMapper->offsetSet('foo4', NULL), 'IDataMapper::offsetSet() should return IDataMapper::ONESHOT');
        $this->assertSame(4, $called, 'IDataMapper::offsetSet() Failed to call callback');

        # Test invalid property
        $this->assertEquals(IDataMapper::INVALID, $this->DataMapper->offsetSet('foo5', NULL), 'IDataMapper::offsetSet() should return IDataMapper::INVALID');
        $this->assertSame(5, $called, 'IDataMapper::offsetSet() Failed to call callback');

        # Test undefined property
        $this->assertEquals(IDataMapper::UNDEFINED, $this->DataMapper->offsetSet('foo6', NULL), 'IDataMapper::offsetSet() should return IDataMapper::UNDEFINED');
        $this->assertSame(5, $called, 'IDataMapper::offsetSet() Called callback');
    }

    /**
     * @depends test_loadFields
     * @covers ::offsetGet
     */
    public function test_offsetUnset()
    {
        $called = 0;
        $Fields = $this->generateFields($called);

        $this->assertTrue($this->DataMapper->__loadFields($Fields), 'IDataMapper::__loadFields() should return true');

        $this->assertCount(5, $this->DataMapper, 'IDataMapper should contain 5 items');
        $this->DataMapper->offsetUnset('foo1');
        $this->assertCount(4, $this->DataMapper, 'IDataMapper should contain 5 items');
        $this->DataMapper->offsetUnset('foo2');
        $this->assertCount(3, $this->DataMapper, 'IDataMapper should contain 5 items');
        $this->DataMapper->offsetUnset('foo3');
        $this->assertCount(2, $this->DataMapper, 'IDataMapper should contain 5 items');
        $this->DataMapper->offsetUnset('foo4');
        $this->assertCount(1, $this->DataMapper, 'IDataMapper should contain 5 items');
        $this->DataMapper->offsetUnset('foo5');
        $this->assertCount(0, $this->DataMapper, 'IDataMapper should contain 5 items');

        # Undefined offset
        try {
            $this->DataMapper->offsetUnset('undefined');
            $this->fail('Failed to generate notice with undefined property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {}
    }

    public function generateErrors()
    {
        return array(
             array(IDataMapper::WRITEONLY, 'Cannot modify writeonly')
            ,array(IDataMapper::READONLY, 'Cannot modify readonly')
            ,array(IDataMapper::ONESHOT, 'Cannot modify readonly')
            ,array(IDataMapper::INVALID, 'Invalid value')
            ,array(IDataMapper::UNDEFINED, 'non-existant')
        );
    }

    /**
     * @covers ::getErrorInfo
     */
    public function test_getErrorInfo()
    {
        foreach ($this->generateErrors() as $Arguments) {

            list ($Input, $Expected) = $Arguments;
            list ($Messge, $Level) = $this->DataMapper->getErrorInfo($Input, 'foo', 'bar');

            $this->assertContains($Expected, $Messge, 'IDataMapper::getErrorInfo() Returned an invalid result');
        }

        # Unkown
        $this->assertNull($this->DataMapper->getErrorInfo(-1, 'foo', 'bar'), 'IDataMapper::getErrorInfo() Returned an invalid result');
    }
}
