<?php
/**
 * AdaptorTest.php | Dec 30, 2013
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

require_once __DIR__ . '/../Config/Adaptor.php';

/**
 * Tests BLW Library Adaptor type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class AdaptorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\Adaptor
     */
    private static $Object = NULL;

    public function test_GetInstance()
    {
        $Data         = array('foo' => 1, 'bar' => 2);
        self::$Object = \NewArrayObject::GetInstance($Data);

        $this->assertEquals(new \ArrayIterator($Data), self::$Object->GetSubject());
    }


    /**
     * @depends test_GetInstance
     */
    public function test_Subject()
    {
        self::$Object->foo = 1;
        self::$Object->bar = 2;

        $this->assertEquals(1, self::$Object->GetSubject()->foo);
        $this->assertEquals(2, self::$Object->GetSubject()->bar);
    }

    /**
     * @depends test_GetInstance
     */
    public function test_ArrayAccess()
    {
        $this->assertTrue(isset(self::$Object['foo']));
        $this->assertTrue(isset(self::$Object['bar']));

        self::$Object['foo']  = 10;
        self::$Object['bar']  = 20;
        self::$Object['foo'] += 1;
        self::$Object['bar'] += 1;

        $Subject = self::$Object->GetSubject();

        $this->assertEquals(11, $Subject['foo']);
        $this->assertEquals(21, $Subject['bar']);

        unset(self::$Object['foo']);

        $this->assertFalse(isset(self::$Object['foo']));
        $this->assertFalse(isset($Subject['foo']));
    }

    /**
     * @depends test_GetInstance
     */
    public function test_serialize()
    {
        self::$Object->foo = 1;
        $Serialized = unserialize(serialize(self::$Object));
        $this->assertEquals(self::$Object, $Serialized);
    }
}