<?php
/**
 * SerializableTest.php | Feb 12, 2014
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
use BLW\Type\ISerializer;
use BLW\Model\Serializer\Mock;


/**
 * Tests BLW Library Adaptor type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\ISerializable
 */
class SerializableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\ISerializable
     */
    protected $Serializable = NULL;

    /**
     * @var \BLW\Model\Serializer\Mock
     */
    protected $Serializer   = NULL;

    protected function setUp()
    {
        global $BLW_Serializer;

        $this->Serializer   = new \BLW\Model\Serializer\Mock;
        $this->Serializable = $this->getMockForAbstractClass('\\BLW\\Type\\ASerializable');
        $BLW_Serializer     = $this->Serializer;

        $this->Serializable
            ->expects($this->any())
            ->method('getSerializer')
            ->will($this->returnValue($this->Serializer));
    }

    protected function tearDown()
    {
        global $BLW_Serializer;

        $this->Serializer   = NULL;
        $this->Serializable = NULL;
        $BLW_Serializer     = NULL;
    }

    /**
     * @covers ::serialize
     */
    public function test_serialize()
    {
        $this->Serializable->foo = 1;
        $this->Serializable->bar = 1;
        $this->Serializable->pie = 1;
        $Hash                    = spl_object_hash($this->Serializable);
        $Serialized              = $this->Serializable->serializeWith($this->Serializer, -1);

        $this->assertEquals($Hash, $Serialized, 'ISerializable::serializeWith(MockSerializer) should return spl_object_hash() of object.');
        $this->assertEquals(-1, $this->Serializer->flags);
    }

    /**
     * @covers ::unserialize
     */
    public function test_unserialize()
    {
        $this->Serializable->foo = 1;
        $this->Serializable->bar = 1;
        $this->Serializable->pie = 1;
        $Serialized              = unserialize(serialize($this->Serializable));

        $this->assertEquals($this->Serializable, $Serialized, 'unserialize(serialize(ISerializable)) !== ISerializable spl_object_hash() of object.');
    }

    /**
     * @covers ::serializeWith
     */
    public function test_serializeWith()
    {
        $Hash       = spl_object_hash($this->Serializable);
        $Serialized = $this->Serializable->serializeWith($this->Serializer, -1);

        $this->assertEquals($Hash, $Serialized, 'ISerializable::serializeWith(MockSerializer) should return spl_object_hash() of object.');
        $this->assertEquals(-1, $this->Serializer->flags);
    }

    /**
     * @covers ::unserializeWith
     */
    public function test_unserializeWith()
    {
        $Unserialized            = clone $this->Serializable;
        $this->Serializable->foo = 1;
        $this->Serializable->bar = 1;
        $this->Serializable->pie = 1;
        $Serialized              = $this->Serializable->serializeWith($this->Serializer, 0);

        $this->assertNotEquals($this->Serializable, $Unserialized, '$Unserialized should not equal $this->Serializable');
        $this->assertTrue($Unserialized->unserializeWith($this->Serializer, $Serialized, -1));
        $this->assertEquals($this->Serializable, $Unserialized, '$Unserialized should equal $this->Serializable');
        $this->assertEquals(-1, $this->Serializer->flags);
    }
}