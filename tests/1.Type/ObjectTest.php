<?php
/**
 * ObjectTest.php | Dec 30, 2013
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

require_once __DIR__ . '/../Config/Object.php';

/**
 * Tests BLW Library Object type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class ObjectTest extends \PHPUnit_ObjectTest
{
    const TEST_CLASS    = '\\Object';

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
	 * @expectedException \BLW\Model\InvalidArgumentException
	 */
	public function test_createException1()
	{
		new Object('foo');
	}

	/**
	 * @depends test_GetInstance
	 * @expectedException \BLW\Model\InvalidArgumentException
	 */
	public function test_createException2()
	{
		new Object(array('ID' => '   '));
	}

	/**
	 * @depends test_GetInstance
	 * @expectedException \BLW\Model\InvalidArgumentException
	 */
	public function test_createException3()
	{
		new Object(new \stdClass);
	}

	/**
	 * @depends test_GetInstance
	 * @expectedException \BLW\Model\InvalidArgumentException
	 */
	public function test_SetIDException()
	{
		self::$Parent->SetID('asdfs sadf sa asfd 24 4');
	}

	/**
	 * @depends test_GetInstance
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

	public function test_Shut_Down()
	{
	    parent::_Shut_Down();
	}
}