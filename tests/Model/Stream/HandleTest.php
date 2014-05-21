<?php
/**
 * HandleTest.php | May 15, 2014
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
namespace BLW\Model\Stream;

use BLW\Model\Stream\Handle as Stream;
use BLW\Model\InvalidArgumentException;


/**
 * Tests BLW resource stream
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Stream\Handle
 */
class HandleTest  extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        $Stream = new Stream(fopen("data:text/plain,line1\r\nline2\r\nline3\r\n", 'r'));

        $this->assertAttributeInternalType('resource', '_fp', $Stream, 'Handle::__construct() Failed to set file pointer');

        # Invalid arguments
        try {
            new Stream(null);
            $this->fail('Failed to generate exception with invalid arguments');
        } catch (InvalidArgumentException $e) {}
    }
}
