<?php
/**
 * ClassExceptionTest.php | May 13, 2014
 *
 * @filesource
 * @license MIT
 * @copyright Copyright (c) 2013-2018, mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * @package BLW\Core
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model;

use BLW\Model\ClassException;


/**
 * Tests BLW Class exception
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\ClassException
 */
class ClassExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\ClassException
     */
    protected $Exception = NULL;

    protected function setUp()
    {
        try {
            throw new ClassException(-1);
        }

        catch (\Exception $e) {
            $this->Exception = $e;
        }

    }

    protected function tearDown()
    {
        $this->Exception = NULL;
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        $Expected = sprintf('!^.+%s::%s\x28\x29: Current class is currupted. Status: [\x2d\d]+. Caused by [\s\S]+$!', preg_quote(__CLASS__), 'setUp');

        $this->assertRegExp($Expected, strval($this->Exception), 'ClassException::construct() Created an invalid exception');
    }
}