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
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model\Command\Output;

use BLW\Model\Command\Output\Generic as Output;

/**
 * Tests basic command input object
 * @package BLW\Command
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Command\Output\Generic
 */
class GenericTest  extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\Command\Output
     */
    protected $Output = NULL;

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        $Stream = $this->getMockForAbstractClass('\\BLW\\Type\\AStream');
        $Output = new Output($Stream, $Stream);

        $this->assertAttributeSame($Stream, '_OutStream', $Output, 'Generic::__construct() Failed to set $_OutStream');
        $this->assertAttributeSame($Stream, '_ErrStream', $Output, 'Generic::__construct() Failed to set $_ErrStream');
    }
}
