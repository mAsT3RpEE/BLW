<?php
/**
 * GenericTest.php | May 14, 2014
 *
 * @filesource
 * @license MIT
 * @copyright Copyright (c) 2013-2018, mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * @package BLW\Command
 * @version 1.0.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model\Command\Input;

use BLW\Model\Command\Input\StdInput as Input;


/**
 * Tests basic command input object
 * @package BLW\Command
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Command\Input\StdInput
 */
class StdInputTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\Command\Input
     */
    protected $Input = NULL;

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        $Input = new Input;

        $this->assertAttributeInstanceOf('\\BLW\\Type\\IStream', '_InStream', $Input, 'Generic::__construct() Failed to set $_Stream');
        $this->assertAttributeInstanceOf('\\BLW\\Type\\IContainer', '_Arguments', $Input, 'Generic::__construct() Failed to set $_Arguments');
        $this->assertAttributeInstanceOf('\\BLW\\Type\\IContainer', '_Options', $Input, 'Generic::__construct() Failed to set $_Options');
    }
}
