<?php
/**
 * MediatableWrapperTest.php | Feb 14, 2014
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
 * Tests BLW Library Mediatable trait.
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 *  @coversDefaultClass \BLW\Type\AMediatableWrapper
 */
class MediatableWrapperTest extends MediatableTest
{
    protected function setUp()
    {
        $this->Callbacks  = array();
        $this->Mediator   = $this->getMockForAbstractClass('\\BLW\\Type\\IMediator');
        $this->Event      = $this->getMockForAbstractClass('\\BLW\\Type\\IEvent');
        $this->Mediatable = $this->getMockForAbstractClass('\\BLW\\Type\\AMediatableWrapper', array(new \SplFileInfo(__FILE__)));

        $this->Mediator
            ->expects($this->any())
            ->method('register')
            ->will($this->returnCallback(array($this, 'mock_register')));

        $this->Mediator
            ->expects($this->any())
            ->method('trigger')
            ->will($this->returnCallback(array($this, 'mock_trigger')));
    }
}
