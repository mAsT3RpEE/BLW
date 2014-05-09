<?php
/**
 * MediatorTest.php | Feb 14, 2014
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
namespace BLW\Type;

/**
 * Tests BLW Library Iterable trait.
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 *  @coversDefaultClass \BLW\Type\AMediator
 */
class MediatorTest extends \BLW\Type\IterableTest
{
    /**
     * @var \BLW\Type\AMediator
     */
    protected $Mediator = NULL;

    protected function setUp()
    {
        $this->Mediator = $this->getMockForAbstractClass('\\BLW\\Type\\AMediator');
        $this->Iterable = $this->Mediator;

        $this->Iterable
        ->expects($this->any())
        ->method('getID')
        ->will($this->returnValue('TestIterable'));
    }

    protected function tearDown()
    {
        $this->Mediator = NULL;
        $this->Iterable = NULL;
    }
}
