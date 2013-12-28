<?php
/**
 * ElementTest.php | Dec 30, 2013
 *
 * Copyright (c) 2013-2018 mAsT3RpEE's Zone
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
namespace BLW\Tests\Type;

use BLW\Interfaces\Object as ObjectInterface;
use BLW\Model\Object;
use BLW\Type\DOMElement;

require_once __DIR__ . '/../Config/Element.php';

/**
 * Tests BLW Library Element type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class ElementTest extends \PHPUnit_IteratorTest
{
    const TEST_CLASS    = '\\Element';

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

    public function test_Initialize()
    {
        parent::_Initialize();
    }

    /**
     * @depends test_Initialize
     */
    public function test_GetInstance()
    {
        parent::_GetInstance();
    }

    /**
     * @depends test_GetInstance
     */
    public function test_tag()
    {
        $this->assertEquals('span', self::$Parent->tag());
        self::$Parent->tag('div');
        $this->assertEquals('div', self::$Parent[0]->tagName);
    }

    /**
     * @depends test_GetInstance
     */
    public function test_push()
    {
        parent::_push();
    }

	/**
	 * @depends test_push
	 */
	public function test_seek()
	{
	    parent::_seek();
	}

    /**
	 * @depends test_push
	 */
    public function test_filter()
    {
        $Nodes = self::$Parent->filter('span');

        $this->assertEquals(12, $Nodes->length);

        foreach ($Nodes as $Node) {
            $this->assertEquals('<span></span>', $Node-> C14N());
        }

        $Temp  = self::$Parent;
        $Nodes = $Temp('span');

        $this->assertEquals(12, $Nodes->length);

        foreach ($Nodes as $Node) {
            $this->assertEquals('<span></span>', $Node-> C14N());
        }
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
        $this->assertEquals(self::$Parent, $Serialized);
        $this->assertEquals(self::$Parent->GetHTML(), $Serialized->GetHTML());
    }

	/**
	 * @depends test_serialize
	 */
	public function test_Save()
	{
	    parent::_Save();
	}

	/**
	 * @depends test_Save
	 */
	public function test_Load()
	{
	    parent::_Load();
	}

	public function test_on()
	{
	    parent::_on();
	}

	/**
	 * @depends test_push
	 * @expectedException \BLW\Model\InvalidArgumentException
	 */
	public function test_AddNodeException1()
	{
	    self::$Parent->AddNode(new DOMElement('foo', 'bar'));
	}

	public function test_Shut_Down()
	{
	    parent::_Shut_Down();
	}
}