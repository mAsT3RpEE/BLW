<?php
/**
 * SingletonTest.php | Dec 30, 2013
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

require_once __DIR__ . '/../Config/Singleton.php';

/**
 * Tests BLW Library Singleton type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class SingletonTest extends \PHPUnit_ObjectTest
{
    const TEST_CLASS    = '\\Singleton';

    public function test_Initialize()
    {
        parent::_Initialize();
    }

    /**
     * @depends test_Initialize
     */
    public function test_GetInstance()
    {
        $TEST_CLASS = self::TEST_CLASS;
        parent::_GetInstance();
        $this->assertSame(self::$Parent, $TEST_CLASS::GetInstance());
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

	/**
	 * @depends test_GetInstance
	 * @expectedException \BLW\Model\InvalidClassException
	 */
	public function test_SingletonException()
	{
        parent::_SingletonException();
	}

	public function test_Shut_Down()
	{
	    parent::_Shut_Down();
	}
}