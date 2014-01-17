<?php
/**
 * SymfonyTest.php | Jan 30, 2013
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
namespace BLW\Tests\Type\Event;

use Event;
use ArrayIterator;

use BLW\Interfaces\Object as ObjectInterface;
use BLW\Model\Object;

require_once __DIR__ . '/../../Config/Event/Symfony.php';

/**
 * Tests Symfony Event type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class SymfonyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\Event
     */
    private static $Event = NULL;

    public function test_create()
    {
        new Event(NULL);

        $Data        = array('foo' => 1, 'bar' => 2);
        self::$Event = new Event(new ArrayIterator($Data), $Data);
        $Test        = new ArrayIterator($Data);

        $this->assertEquals($Test, self::$Event->GetSubject());
    }

    /**
     * @expectedException \BLW\Model\InvalidArgumentException
     */
    public function test_createException1()
    {
        new Event(NULL, NULL);
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function test_callException1()
    {
        self::$Event->foo();
    }

    /**
     * @depends test_create
     */
    public function test_serialize()
    {
        self::$Event->foo = 1;
        $Serialized       = unserialize(serialize(self::$Event));

        $this->assertEquals(self::$Event, $Serialized);
    }
}