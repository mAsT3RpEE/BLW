<?php
/**
 * DecoratorTest.php | Dec 30, 2013
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

require_once __DIR__ . '/../Config/Decorator.php';

/**
 * Tests BLW Library Decorator type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class DecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\Decorator
     */
    private static $Object = NULL;

    public function test_GetInstance()
    {
        $Data         = array('foo' => 1, 'bar' => 2);
        self::$Object = \DecoratedObject::GetInstance($Data);

        $this->assertEquals(1, @self::$Object->GetSubject()->Options->foo);
        $this->assertEquals(2, @self::$Object->GetSubject()->Options->bar);
    }

    /**
     * @depends test_GetInstance
     */
    public function test_DecorateOn()
    {
		$foo = false;
        self::$Object->_on('foo', function() use($foo) {$foo = true;});
        $this->assertArrayHasKey('DECORATE_TEST', $GLOBALS);

        $Output = $GLOBALS['DECORATE_TEST'];

        $this->assertContains('Registering decorator', $Output);
        $this->assertContains('foo', $Output);
        $this->assertContains('Closure', $Output);
    }

    /**
     * @depends test_DecorateOn
     */
    public function test_DecorateDo()
    {
        self::$Object->_do('foo', new \BLW\Model\Event\General(\BLW\Model\Object::GetInstance()));
        $this->assertArrayHasKey('DECORATE_TEST', $GLOBALS);

        $Output = $GLOBALS['DECORATE_TEST'];

        $this->assertContains('Doing decorator', $Output);
        $this->assertContains('foo', $Output);
        $this->assertContains('BLW\\Model\\Event', $Output);
    }

    /**
     * @depends test_DecorateOn
     */
    public function test_DecorateToString()
    {
        $String = (string)(self::$Object->GetSubject());
        $this->assertArrayHasKey('DECORATE_TEST', $GLOBALS);

        $Output = $GLOBALS['DECORATE_TEST'];

        $this->assertContains('strval decorator', $Output);
    }

    /**
     * @depends test_GetInstance
     */
    public function test_serialize()
    {
        self::$Object->foo = 1;
        $Serialized        = unserialize(serialize(self::$Object));
        $this->assertEquals(self::$Object, $Serialized);
    }
}