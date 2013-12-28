<?php
/**
 * IteratorTest.php | Dec 30, 2013
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

require_once __DIR__ . '/../Config/Iterator.php';

/**
 * Tests BLW Library Iterator type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class IteratorTest extends \PHPUnit_IteratorTest
{
    const TEST_CLASS    = '\\BLWIterator';

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
	 * @depends test_on
	 * @expectedException \OutOfRangeException
	 */
	public function test_ArrayAccess1()
	{
	    parent::$Parent['foo'];
	}


	/**
	 * @depends test_on
	 * @expectedException \OutOfRangeException
	 */
	public function test_ArrayAccess2()
	{
	    self::$Parent[100];
	}

	/**
	 * @depends test_on
	 * @expectedException \BLW\Model\InvalidArgumentException
	 */
	public function test_seekException1()
	{
	    parent::$Parent->seek('foo');
	}


	/**
	 * @depends test_on
	 * @expectedException \OutOfBoundsException
	 */
	public function test_seekException2()
	{
	    self::$Parent->seek(100);
	}

	public function test_Shut_Down()
	{
	    parent::_Shut_Down();
	}
}