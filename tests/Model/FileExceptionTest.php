<?php
/**
 * FileExceptionTest.php | May 13, 2014
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

use BLW\Model\FileException;


/**
 * Tests BLW filesystem exception
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\FileException
 */
class FileExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\FileException
     */
    protected $Exception = NULL;

    protected function setUp()
    {
        try {
            throw new FileException(__FILE__);
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
        $Expected = sprintf('!^.+%s::%s\x28\x29: File error: \x28%s\x29 with access \x28\d+\x29. Caused by [\s\S]+$!', preg_quote(__CLASS__), 'setUp', preg_quote(__FILE__));

        $this->assertRegExp($Expected, strval($this->Exception), 'FileException::construct() Created an invalid exception');
        $this->assertSame(__FILE__, $this->Exception->File, 'FileException::construct() Failed to set file');
    }
}