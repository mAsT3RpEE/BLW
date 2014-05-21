<?php
/**
 * BootstrapTest.php | May 10, 2014
 *
 * @filesource
 * @license MIT
 * @copyright Copyright (c) 2013-2018, mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 *
 * @package BLW\Core
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW;

/**
 * Tests bootstrap.php include file.
 *
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass CLASSNAME
 */
class BootstrapTest extends \PHPUnit_Framework_TestCase
{
    public function test_bootstrap()
    {
        $this->assertTrue(include(BLW_DIR . '/src/bootstrap.php'), 'bootstrap.php Should return true');
        $this->assertTrue(defined('BLW'), 'bootsrap.php Should have defined BLW');
        $this->assertTrue(defined('BLW_PHAR'), 'bootsrap.php Should have defined BLW');
        $this->assertEquals(dirname(__DIR__), BLW_PHAR, 'BLW_PHAR should equal: '. dirname(__DIR__));
    }
}
