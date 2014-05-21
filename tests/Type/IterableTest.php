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
namespace BLW\Type;

use ReflectionProperty;


/**
 * Tests BLW Library Adaptor type.
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 *  @coversDefaultClass \BLW\Type\AIterable
 */
class IterableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\AIterable
     */
    protected $Iterable = NULL;

    protected function setUp()
    {
        $this->Iterable = $this->getMockForAbstractClass('\\BLW\\Type\\AIterable');

        $this->Iterable
            ->expects($this->any())
            ->method('getID')
            ->will($this->returnValue('TestIterable'));
    }

    protected function tearDown()
    {
        $this->Iterable = NULL;
    }

    /**
     * @covers ::getParent
     * @covers \BLW\Type\AIterable::getParent
     */
    public function test_getParent()
    {
        $Expected = $this->getMockForAbstractClass('\\BLW\\Type\\IObject');
        $Property = new ReflectionProperty($this->Iterable, '_Parent');

        $Property->setAccessible(true);
        $Property->setValue($this->Iterable, $Expected);

        $this->assertSame($Expected, $this->Iterable->getParent(), 'IIterable::getParent() should equal $_Parent.');
    }

    /**
     * @covers ::setParent
     * @covers \BLW\Type\AIterable::setParent
     */
    public function test_setParent()
    {
        $Expected = $this->getMockForAbstractClass('\\BLW\\Type\\IObject');

        // Valid arguments
        $this->assertEquals(IDataMapper::UPDATED, $this->Iterable->setParent($Expected), 'IIterable::setParent() did not return IDataMapper::UPDATED');
        $this->assertEquals(IDataMapper::ONESHOT, $this->Iterable->setParent($Expected), 'IIterable::setParent() should return IDataMapper::ONESHOT');

        $this->assertSame($Expected, $this->Iterable->getParent(), 'IIterable::setParent() Failed to update $_Parent');

        // Invalid arguments
        $this->assertEquals(IDataMapper::INVALID, $this->Iterable->setParent($this->Iterable), 'IIterable::setParent() should return IDataMapper::INVALID');
        $this->assertEquals(IDataMapper::INVALID, $this->Iterable->setParent(null), 'IIterable::setParent() should return IDataMapper::ONESHOT');
   }

   /**
    * @depends test_setParent
    * @covers ::clearParent
    * @covers \BLW\Type\AIterable::clearParent
    */
    public function test_clearParent()
    {
        $Parent = $this->getMockForAbstractClass('\\BLW\\Type\\IObject');

        $this->assertEquals(IDataMapper::UPDATED, $this->Iterable->clearParent($Parent), 'clearParent did not return IDataMapper::UPDATED');
        $this->assertEquals(IDataMapper::UPDATED, $this->Iterable->setParent($Parent), 'setParent did not return IDataMapper::UPDATED');
        $this->assertEquals(IDataMapper::UPDATED, $this->Iterable->clearParent($Parent), 'clearParent did not return IDataMapper::UPDATED');
        $this->assertNull($this->Iterable->getParent(), 'getParent() should return NULL.');
        $this->assertEquals(IDataMapper::UPDATED, $this->Iterable->setParent($Parent), 'setParent did not return IDataMapper::UPDATED');
   }

   /**
    * @covers ::getID
    * @covers \BLW\Type\AIterable::getID
    */
    public function test_getID()
    {
        $this->assertNotEmpty($this->Iterable->getID(), 'IIterable::getID() Returned an invalid value');
        $this->assertInternalType('string', $this->Iterable->getID(), 'IIterable::getID() returned an invalid value');
   }
}
