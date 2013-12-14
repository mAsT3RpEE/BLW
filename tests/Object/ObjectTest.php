<?php
/**
 * ObjectTest.php | Dec 13, 2013
 * 
 * Copyright (c) mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 *
 * @filesource
 * @copyright mAsT3RpEE's Zone
 * @license MIT
 */

/**
 * @package BLW\Core
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Tests;

use BLW\ObjectInterface;
use BLW\Object;

/**
 * Tests BLW Library Object functionality.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class ObjectTest extends \PHPUnit_Framework_TestCase
{
    const CHILD1 = 0;
    const CHILD2 = 1;
    const CHILD3 = 2;
    const CHILD4 = 3;
    
    const GRANDCHILD1 = 0;
    const GRANDCHILD2 = 1;
    const GRANDCHILD3 = 0;
    const GRANDCHILD4 = 1;
    const GRANDCHILD5 = 0;
    const GRANDCHILD6 = 1;
    
    private static $Parent         = NULL;
    private static $Child1         = NULL;
    private static $Child2         = NULL;
    private static $Child3         = NULL;
    private static $Child4         = NULL;
    private static $GrandChild1    = NULL;
    private static $GrandChild2    = NULL;
    private static $GrandChild3    = NULL;
    private static $GrandChild4    = NULL;
    private static $GrandChild5    = NULL;
    private static $GrandChild6    = NULL;
    
    public function test_init()
    {
        Object::init(array('foo'=>1,'hard_init'=>true));
        
        // Data tests
        $this->assertArrayHasKey('foo', Object::$DefaultOptions);
        $this->assertArrayNotHasKey('hard_init', Object::$DefaultOptions);
        
        // $self and $base tests
        $this->assertInstanceOf('\\BLW\\ObjectInterface', Object::$base);
        $this->assertInstanceOf('\\BLW\\Object', Object::$base);
        $this->assertSame(Object::$base, Object::$self);
        
        // Options tests
        if (isset(Object::$DefaultOptions['foo'])) {
            $this->assertEquals(1, Object::$base->Options->foo);
            unset(Object::$DefaultOptions['foo']);
        }
    }
    
    /**
     * @depends test_init
     */
    public function test_create()
    {
        // Create Parent
        self::$Parent = Object::create(array('ID'=>'Parent', 'bar'=>1));
        
        $this->assertEquals('Parent', self::$Parent->GetID());
        $this->assertEquals(1, self::$Parent->Options->bar);
        $this->assertNull(self::$Parent->parent());
        
        $Duplicate = Object::create(self::$Parent);
        
		$this->assertNotSame(self::$Parent, $Duplicate);
		$this->assertSame(self::$Parent->GetOptions()->ID, $Duplicate->GetOptions()->ID);
		$this->assertSame(self::$Parent->count(), $Duplicate->count());
		
        // Create Children
        self::$Child1         = Object::create(array('ID'=>'Child1',      'Parent'=>self::$Parent, 'bar'=>1));
        self::$Child2         = Object::create(array('ID'=>'Child2',      'Parent'=>self::$Parent, 'bar'=>1));
        self::$Child3         = Object::create(array('ID'=>'Child3',      'Parent'=>self::$Parent, 'bar'=>1));
        self::$Child4         = Object::create(array('ID'=>'Child4',      'Parent'=>self::$Parent, 'bar'=>1));
        
        foreach (self::$Parent as $Child) {
            $this->assertSame(self::$Parent, $Child->parent());
        }
        
        // Create GrandChildren
        self::$GrandChild1    = Object::create(array('ID'=>'GrandChild1', 'bar'=>1));
        self::$GrandChild2    = Object::create(array('ID'=>'GrandChild2', 'bar'=>1));
        self::$GrandChild3    = Object::create(array('ID'=>'GrandChild3', 'bar'=>1));
        self::$GrandChild4    = Object::create(array('ID'=>'GrandChild4', 'bar'=>1));
        self::$GrandChild5    = Object::create(array('ID'=>'GrandChild5', 'bar'=>1));
        self::$GrandChild6    = Object::create(array('ID'=>'GrandChild6', 'bar'=>1));
    }

	/**
	 * @depends test_create
	 * @expectedException InvalidArgumentException
	 */
	public function test_createException1()
	{
		new Object('foo');
	}

	/**
	 * @depends test_create
	 * @expectedException InvalidArgumentException
	 */
	public function test_createException2()
	{
		new Object(array('ID' => '   '));
	}

	/**
	 * @depends test_create
	 * @expectedException InvalidArgumentException
	 */
	public function test_createException3()
	{
		new Object(new \stdClass);
	}
	
    /**
     * @depends test_create
     */
    public function test_push()
    {
        // Map all objects
        self::$Parent->push(self::$Child1);
        self::$Parent->push(self::$Child2);
        self::$Parent->push(self::$Child3);
        self::$Parent->push(self::$Child4);
        
        self::$Parent[self::CHILD1]->push(self::$GrandChild1);
        self::$Parent[self::CHILD1]->push(self::$GrandChild2);
        self::$Parent[self::CHILD2]->push(self::$GrandChild3);
        self::$Parent[self::CHILD2]->push(self::$GrandChild4);
        self::$Parent[self::CHILD3]->push(self::$GrandChild5);
        self::$Parent[self::CHILD3]->push(self::$GrandChild6);
        self::$Parent[self::CHILD4]->push(self::$GrandChild1);
        self::$Parent[self::CHILD4]->push(self::$GrandChild2);
        
        // Assert Bar Property
        self::$Parent->walk(function($o, $i) {
            $this->assertEquals(1, $o->Options->bar);
        });
        
        // Assert ID's
        $this->assertEquals('Child1', self::$Parent[self::CHILD1]->GetID());
        $this->assertEquals('Child2', self::$Parent[self::CHILD2]->GetID());
        $this->assertEquals('Child3', self::$Parent[self::CHILD3]->GetID());
        $this->assertEquals('Child4', self::$Parent[self::CHILD4]->GetID());
        $this->assertEquals('GrandChild1', self::$Parent[self::CHILD1][self::GRANDCHILD1]->GetID());
        $this->assertEquals('GrandChild2', self::$Parent[self::CHILD1][self::GRANDCHILD2]->GetID());
        $this->assertEquals('GrandChild3', self::$Parent[self::CHILD2][self::GRANDCHILD3]->GetID());
        $this->assertEquals('GrandChild4', self::$Parent[self::CHILD2][self::GRANDCHILD4]->GetID());
        $this->assertEquals('GrandChild5', self::$Parent[self::CHILD3][self::GRANDCHILD5]->GetID());
        $this->assertEquals('GrandChild6', self::$Parent[self::CHILD3][self::GRANDCHILD6]->GetID());
        $this->assertEquals('GrandChild1', self::$Parent[self::CHILD4][self::GRANDCHILD1]->GetID());
        $this->assertEquals('GrandChild2', self::$Parent[self::CHILD4][self::GRANDCHILD2]->GetID());
    
        // Assert Lineage
        foreach (self::$Parent as $Child) {
            $this->assertSame(self::$Parent, $Child->GetParent());
    
            if($Child->GetID() == 'Child4') continue;
    
            foreach ($Child as $GrandChild) {
                $this->assertSame($Child, $GrandChild->GetParent());
            }
        }
    }

	/**
	 * @depends test_push
	 */
	public function test_child()
	{
		$this->assertSame(self::$Child1, self::$Parent->child('Child1'));
		$this->assertSame(self::$Child1, Object::$self);
	}

	/**
	 * @depends test_push
	 */
    public function test_serialize()
    {
        self::$Parent->foo = 1;
        self::$Child1->foo = 1;
        self::$Child2->foo = 1;
        self::$Child3->foo = 1;
        self::$Child4->foo = 1;
        
        $Serialized = unserialize(serialize(self::$Parent));
        
        $this->assertSame(self::$Parent->foo, $Serialized->foo);
        
        foreach (self::$Parent as $k => $v) {
            $this->assertEquals($v, $Serialized[$k]);
        }
    }
	
	/**
	 * @depends test_serialize
	 */
	public function test_Save()
	{
		self::$Parent->Save(sys_get_temp_dir() . '/temp-' .date('l') . '.php');
		
		$this->assertTrue(file_exists(sys_get_temp_dir() . '/temp-' .date('l') . '.php'));
	}
	
	/**
	 * @depends test_Save
	 */
	public function test_Load()
	{
		$Saved = include(sys_get_temp_dir() . '/temp-' .date('l') . '.php');
		
		@unlink(sys_get_temp_dir() . '/temp-' .date('l') . '.php');
		
		$this->assertEquals(self::$Parent, $Saved);
		$this->assertFalse(file_exists(sys_get_temp_dir() . '/temp-' .date('l') . '.php'));
	}
	
	/**
	 * @depends test_create
	 */
	public function test_on()
	{
	    $Called   = '';
 	    $Test     = function() use (&$Called) {
 	        $debug    = debug_backtrace();
 	        $Called   = $debug[1]['function'];
 	    };
	    
	    Object::onCreate($Test);
	    
	    $Object = Object::create()
	       ->on('Test', $Test)
	       ->onSetID($Test)
	       ->onAdd($Test)
	       ->onUpdate($Test)
	       ->onDelete($Test)
	       ->onSerialize($Test)
	    ;
        
	    $this->assertEquals('onCreate', $Called);
	    
	    $Object->on('Test');
	    $this->assertEquals('on', $Called);
	    
	    $Object->SetID('foo');
	    $this->assertEquals('onSetID', $Called);
	     
	    $Object->push(Object::create());
	    $this->assertEquals('onAdd', $Called);
	    
	    $Object[0] = Object::create();
	    $this->assertEquals('onUpdate', $Called);
	    
	    unset($Object[0]);
	    $this->assertEquals('onDelete', $Called);
	    
	    serialize($Object);
	    $this->assertEquals('onSerialize', $Called);
	}
	
	/**
	 * @depends test_push
	 */
	public function test_seek()
	{
	    self::$Parent->seek(self::CHILD2);
	    
	    $this->assertSame(self::$Child2, self::$Parent->current());
	}
	
}