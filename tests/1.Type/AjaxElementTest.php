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
namespace BLW\Tests\Type; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

use BLW\Model\Object;
use BLW\Model\Element;

require_once __DIR__ . '/../Config/AjaxElement.php';

/**
 * Tests BLW Library Element functionality.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class AjaxElementTest extends \PHPUnit_IteratorTest
{
    const TEST_CLASS    = '\\AjaxElement';

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
	    $Data = array('hard_init' => 1);

		Object::Initialize($Data);
		Element::Initialize($Data);

		parent::_Initialize();
    }

    /**
     * @depends test_Initialize
     */
    public function test_GetInstance()
    {
        parent::_GetInstance();
        $this->assertStringStartsWith('<span class="ajax" id="Parent">', self::$Parent->GetHTML());
        $this->assertContains('type="text/javascript"', self::$Parent->GetHTML());
        $this->assertContains('var x = 100', self::$Parent->GetHTML());
        $Duplicate = \AjaxElement::GetInstance(self::$Parent);
        $this->assertEquals(self::$Parent->GetHTML(), $Duplicate->GetHTML());
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
    public function test_serialize()
    {
        parent::_serialize();
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
	 * @depends test_GetInstance
	 */
	public function test_doAjax()
	{
		self::$Parent->SetAction('foo', function() {
			return array(
				'status' => 0
				,'results' => array('foo')
			);
		});

		$Action          = new \stdClass;
		$Action->Name    = 'Action.foo';
		$Action->Object  = NULL;
		$Action->Objects = NULL;

		$this->assertEquals('{"status":0,"result":{"status":0,"results":["foo"]},"notices":[],"errors":[]}', self::$Parent->doAJAX(new \BLW\Model\Event\General($Action)));

		$_GET[\BLW\Model\ActionParser::ACTION] = 'SetGlobal';
		$_GET[\BLW\Model\ActionParser::OBJECT] = 'SetGlobal';

		\BLW::Initialize();

		$Test = \AjaxElement::GetInstance(array('ID' => 'SetGlobal'));

        $this->assertArrayHasKey('AJAX_ACTION', $GLOBALS);
        $this->assertEquals('SetGlobal', $GLOBALS['AJAX_ACTION']);
	}

	public function test_Shut_Down()
	{
	    parent::_Shut_Down();
	}
}