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
namespace BLW\Tests\Type;

use SplFileInfo;
use PHPUnit_Framework_Error_Notice;
use PHPUnit_Framework_Error;
use BLW\Type\IDataMapper;
use BLW\Type\ADataMapper;

use BLW\Model\InvalidArgumentException;

/**
 * Tests BLW Library Adaptor type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\IDataMapper
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

    public function generateFields()
    {
        return array(
        	 array(true,  array(0, ADataMapper::createRead($foo), ADataMapper::createWrite($foo)))
            ,array(true,  array('foo', ADataMapper::createRead($foo), ADataMapper::createWrite($foo)))
        	,array(true,  array(new SplFileInfo(__FILE__), ADataMapper::createRead($foo), ADataMapper::createWrite($foo)))
            ,array(false, array(NULL, ADataMapper::createRead($foo), ADataMapper::createWrite($foo)))
            ,array(false, array('foo', NULL, ADataMapper::createWrite($foo)))
            ,array(false, array('foo',  ADataMapper::createRead($foo), NULL))
            ,array(false,  array('foo', ADataMapper::createRead($foo), ADataMapper::createWrite($foo), 'foo'))
        );
    }

    /**
     * @depends test_createRead
     * @depends test_createWrite
     * @dataProvider generateFields
     * @covers ::__setField
     */
    public function test__setField($Valid, array $Params)
    {
        if ($Valid) {

            # Test valid arguments
            $this->assertTrue(
                 call_user_func_array(array($this->DataMapper, '__setField'), $Params)
                ,'IDataMapper::__setField() should return true.'
            );

            $this->assertTrue(isset($this->DataMapper[(string) $Params[0]]), 'IDataMapper::__setField() did not affect ArrayAccess');
        }

        else {

            # Test invalid arguments
            try {
                call_user_func_array(array($this->DataMapper, '__setField'), $Params);
                $this->fail('Failed to generate exception with invalid parameter');
            }

            catch (InvalidArgumentException $e) {}
        }
    }

    /**
     * @depends test__setField
     * @covers ::__loadFields
     */
   public function test__loadFields()
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
        }

        catch (InvalidArgumentException $e) {}
   }

    /**
     * @depends test__loadFields
     * @covers ::offsetGet
     */
    public function test_offsetGet()
    {
        # Load test data
        $Fields = array(
            array('foo', function() use (&$called) {$called = true; return 'test';}, function(){})
        );

        $this->assertNull($this->DataMapper['undefined'], 'IDataMapper[undefined] should return NULL');
        $this->assertTrue($this->DataMapper->__loadFields($Fields), 'IDataMapper::__loadFields() should return true');
        $this->assertNULL($called, '$called should be NULL');
        $this->assertEquals('test', $this->DataMapper['foo'], 'IDataMapper[foo] should return `test`');
        $this->assertTrue($called, '$called should be true');
    }

    /**
     * @depends test__loadFields
     * @covers ::offsetSet
     */
    public function test_offsetSet()
    {
        # Load test data
        $called = false;
        $Fields = array(
             array('foo1', function(){}, function() use (&$called) {$called = true; return IDataMapper::UPDATED;})
            ,array('foo2', function(){}, function() use (&$called) {$called = true; return IDataMapper::READONLY;})
            ,array('foo3', function(){}, function() use (&$called) {$called = true; return IDataMapper::WRITEONLY;})
            ,array('foo4', function(){}, function() use (&$called) {$called = true; return IDataMapper::ONESHOT;})
            ,array('foo5', function(){}, function() use (&$called) {$called = true; return IDataMapper::INVALID;})
            ,array('foo6', function(){}, function() use (&$called) {$called = true; return IDataMapper::UNDEFINED;})
        );

        $this->assertTrue($this->DataMapper->__loadFields($Fields), 'IDataMapper::__loadFields() should return true');

        # Test updatable property
        $this->assertFalse($called, '$called should be false');
        $this->assertEquals(IDataMapper::UPDATED, $this->DataMapper->offsetSet('foo1', NULL), 'IDataMapper::offsetSet() should return IDataMapper::UPDATED');
        $this->assertTrue($called, '$called should be true');

        $called = false;

        # Test readonly property
        $this->assertFalse($called, '$called should be false');
        $this->assertEquals(IDataMapper::READONLY, $this->DataMapper->offsetSet('foo2', NULL), 'IDataMapper::offsetSet() should return IDataMapper::READONLY');
        $this->assertTrue($called, '$called should be true');

        $called = false;

        # Test writeonly property
        $this->assertFalse($called, '$called should be false');
        $this->assertEquals(IDataMapper::WRITEONLY, $this->DataMapper->offsetSet('foo3', NULL), 'IDataMapper::offsetSet() should return IDataMapper::WRITEONLY');
        $this->assertTrue($called, '$called should be true');

        $called = false;

        # Test oneshot property
        $this->assertFalse($called, '$called should be false');
        $this->assertEquals(IDataMapper::ONESHOT, $this->DataMapper->offsetSet('foo4', NULL), 'IDataMapper::offsetSet() should return IDataMapper::ONESHOT');
        $this->assertTrue($called, '$called should be true');

        $called = false;

        # Test invalid property
        $this->assertFalse($called, '$called should be false');
        $this->assertEquals(IDataMapper::INVALID, $this->DataMapper->offsetSet('foo5', NULL), 'IDataMapper::offsetSet() should return IDataMapper::INVALID');
        $this->assertTrue($called, '$called should be true');

        $called = false;

        # Test undefined property
        $this->assertFalse($called, '$called should be false');
        $this->assertEquals(IDataMapper::UNDEFINED, $this->DataMapper->offsetSet('foo6', NULL), 'IDataMapper::offsetSet() should return IDataMapper::UNDEFINED');
        $this->assertTrue($called, '$called should be true');
    }
}