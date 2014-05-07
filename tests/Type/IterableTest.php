<?php
/**
 * IterableTest.php | Feb 12, 2014
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
namespace BLW\Tests\Type;

use BLW\Type\IDataMapper;


/**
 * Tests BLW Library Adaptor type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 *  @coversDefaultClass \BLW\Type\AIterable
 */
class IterableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\IIterable
     */
    protected $Iterable = NULL;

    /**
     * @var \BLW\Type\IObject
     */
    protected $Object   = NULL;

    protected function setUp()
    {
        $this->Object   = $this->getMockForAbstractClass('\\BLW\\Type\\IObject', array(), '', false);
        $this->Iterable = $this->getMockForAbstractClass('\\BLW\\Type\\AIterable');

        $this->Iterable
            ->expects($this->any())
            ->method('getID')
            ->will($this->returnValue('TestIterable'));
    }

    protected function tearDown()
    {
        $this->Object   = NULL;
        $this->Iterable = NULL;
    }

    /**
     * @covers ::getParent
     */
    public function test_getParent()
    {
        $this->assertNull($this->Iterable->getParent(), 'getParent() should initially be NULL.');
    }

    /**
     * @covers ::setParent
     */
    public function test_setParent()
    {
        $this->assertEquals(IDataMapper::UPDATED, $this->Iterable->setParent($this->Object), 'setParent did not return IDataMapper::UPDATED');
        $this->assertEquals(IDataMapper::ONESHOT, $this->Iterable->setParent($this->Object), 'setParent did not return IDataMapper::ONESHOT');

        $this->assertSame($this->Object, $this->Iterable->getParent(), '$this->Object !=== getParent()');
   }

   /**
    * @depends test_setParent
    * @covers ::clearParent
    */
    public function test_clearParent()
    {
        $this->assertEquals(IDataMapper::UPDATED, $this->Iterable->clearParent($this->Object), 'clearParent did not return IDataMapper::UPDATED');
        $this->assertEquals(IDataMapper::UPDATED, $this->Iterable->setParent($this->Object), 'setParent did not return IDataMapper::UPDATED');
        $this->assertEquals(IDataMapper::UPDATED, $this->Iterable->clearParent($this->Object), 'clearParent did not return IDataMapper::UPDATED');
        $this->assertNull($this->Iterable->getParent(), 'getParent() should return NULL.');
        $this->assertEquals(IDataMapper::UPDATED, $this->Iterable->setParent($this->Object), 'setParent did not return IDataMapper::UPDATED');
   }

   /**
    * @covers ::getID
    */
    public function test_getID()
    {
        $this->assertEquals('TestIterable', $this->Iterable->getID(), 'getID did not return `TestIterable`');
   }
}
