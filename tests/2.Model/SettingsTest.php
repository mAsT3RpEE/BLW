<?php
/**
 * Settings Test.php | Jan 14, 2014
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
namespace BLW\Tests\Model;

use BLW;

/**
 * Tests ActionParser Module type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class SettingsTest extends \PHPUnit_Framework_TestCase
{
    public function test_Set()
    {
        BLW::Settings()->Set('foo', 1);
        BLW::Settings()->Set('bar', 1);

        if (BLW_PLATFORM == 'standalone') {
            $this->assertArrayHasKey('BLW', $_SESSION);
            $this->assertArrayHasKey('foo', $_SESSION['BLW']);
            $this->assertArrayHasKey('bar', $_SESSION['BLW']);
            $this->assertEquals(1, $_SESSION['BLW']['foo']);
            $this->assertEquals(1, $_SESSION['BLW']['bar']);
        }

        $this->assertEquals(1, BLW::Settings()->Get('foo'));
        $this->assertEquals(1, BLW::Settings()->Get('bar'));
    }

    /**
     * @depends test_Set
     */
    public function test_serialize()
    {
        $Settings   = BLW::Settings();
        $Serialized = unserialize(serialize($Settings));

        $this->assertEquals($Settings, $Serialized);
    }
}