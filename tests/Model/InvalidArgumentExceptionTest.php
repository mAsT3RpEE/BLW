<?php
/**
 * InvalidArgumentTest.php | May 13, 2014
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



/**
 * Tests BLW invalid argument exception
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\InvalidArgumentException
 */
class InvalidArgumentExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\InvalidArgumentException
     */
    protected $Exception = NULL;

    protected function setUp()
    {
        try {
            throw new InvalidArgumentException(100);
        } catch (\Exception $e) {
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
        $Expected = sprintf('!^.+%s::%s\x28\x29: Argument 101 is invalid. Caused by [\s\S]+$!', preg_quote(__CLASS__), 'setUp');

        $this->assertRegExp($Expected, strval($this->Exception), 'InvalidArgumentException::construct() Created an invalid exception');
    }
}
