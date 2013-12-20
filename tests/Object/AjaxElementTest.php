<?php
/**
 * ElementTest.php | Dec 15, 2013
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
namespace BLW\Tests; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

use BLW\Object;
use BLW\Element;
use BLW\AjaxElement;

/**
 * Tests BLW Library Element functionality.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class AjaxElementTest extends \PHPUnit_Framework_TestCase
{
    const CHILD1 = 1;
    const CHILD2 = 2;
    const CHILD3 = 3;
    const CHILD4 = 4;

    const GRANDCHILD1 = 1;
    const GRANDCHILD2 = 2;
    const GRANDCHILD3 = 1;
    const GRANDCHILD4 = 2;
    const GRANDCHILD5 = 1;
    const GRANDCHILD6 = 2;

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
	    $Data = array('hard_init' => 1);

		Object::init($Data);
		Element::init($Data);
		AjaxElement::init($Data);
	}

	public function test_create()
	{
        // Create Parent
        self::$Parent = AjaxElement::create(array('ID'=>'Parent', 'bar'=>1));

        $this->assertEquals('Parent', self::$Parent->GetID());
        $this->assertEquals(1, self::$Parent->Options->bar);
        $this->assertNull(self::$Parent->parent());
        $this->assertEquals('<span class="ajax"></span>', self::$Parent->GetHTML());

        self::$Parent->onSetID();
        $this->assertEquals('<span class="ajax" id="Parent"></span>', self::$Parent->GetHTML());

        $Duplicate = Element::create(self::$Parent);

        $this->assertNotSame(self::$Parent, $Duplicate);
        $this->assertSame(self::$Parent->GetID(), $Duplicate->GetID());
        $this->assertEquals(self::$Parent->GetHTML(), $Duplicate->GetHTML());
        $this->assertSame(self::$Parent->count(), $Duplicate->count());

        // Create Children
        self::$Child1         = AjaxElement::create(array('ID'=>'Child1',      'Parent'=>self::$Parent, 'bar'=>1));
        self::$Child2         = AjaxElement::create(array('ID'=>'Child2',      'Parent'=>self::$Parent, 'bar'=>1));
        self::$Child3         = AjaxElement::create(array('ID'=>'Child3',      'Parent'=>self::$Parent, 'bar'=>1));
        self::$Child4         = AjaxElement::create(array('ID'=>'Child4',      'Parent'=>self::$Parent, 'bar'=>1));

        foreach (self::$Parent as $Child) if($Child instanceof \BLW\ObjectInterface) {
            $this->assertSame(self::$Parent, $Child->parent());
        }

        // Create GrandChildren
        self::$GrandChild1    = AjaxElement::create(array('ID'=>'GrandChild1', 'bar'=>1));
        self::$GrandChild2    = AjaxElement::create(array('ID'=>'GrandChild2', 'bar'=>1));
        self::$GrandChild3    = AjaxElement::create(array('ID'=>'GrandChild3', 'bar'=>1));
        self::$GrandChild4    = AjaxElement::create(array('ID'=>'GrandChild4', 'bar'=>1));
        self::$GrandChild5    = AjaxElement::create(array('ID'=>'GrandChild5', 'bar'=>1));
        self::$GrandChild6    = AjaxElement::create(array('ID'=>'GrandChild6', 'bar'=>1));

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

        $self = $this;

        // Assert Bar Property
        self::$Parent->walk(function($o, $i) use(&$self) {
            if ($o instanceof \BLW\ObjectInterface) {
                $self->assertEquals(1, $o->Options->bar);
            }
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
        foreach (self::$Parent as $Child) if ($Child instanceof \BLW\ElementInterface) {
            $this->assertSame(self::$Parent, $Child->GetParent());

            if($Child->GetID() == 'Child4') continue;

            foreach ($Child as $GrandChild) if ($GrandChild instanceof \BLW\ElementInterface) {
                $this->assertSame($Child, $GrandChild->GetParent());
            }
        }
	}

	/**
	 * @depends test_create
	 */
	public function test_doAjax()
	{
		self::$Parent->SetAction('foo', function() {
			return array(
				'status' => 0
				,'results' => array('foo')
			);
		});

		$this->assertEquals('{"status":0,"results":["foo"]}', self::$Parent->doAJAX('foo'));
	}

     /**
	 * @depends test_create
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
        $this->assertEquals(self::$Parent->GetHTML(), $Serialized->GetHTML());

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
}