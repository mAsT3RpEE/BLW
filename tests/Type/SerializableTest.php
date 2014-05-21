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
namespace BLW\Type;

use ReflectionProperty;


use BLW\Model\InvalidArgumentException;
use BLW\Model\Serializer\Mock;


/**
 * Tests BLW Library Adaptor type.
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 *  @coversDefaultClass \BLW\Type\ASerializable
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

    final public static function setUpBeforeClass()
    {
        global $BLW_Serializer;

        $BLW_Serializer = new \BLW\Model\Serializer\Mock;
    }

    protected function setUp()
    {
        global $BLW_Serializer;

        $this->Serializable = $this->getMockForAbstractClass('\\BLW\\Type\\ASerializable');
        $this->Serializer   = new \BLW\Model\Serializer\Mock;
    }

    protected function tearDown()
    {
        $this->Serializer   = NULL;
        $this->Serializable = NULL;
    }

    /**
     * @covers ::getSerializer
     * @covers \BLW\Type\ASerializable::getSerializer
     */
    public function test_getSerializer()
    {
        $this->assertInstanceOf('\\BLW\\Type\\ISerializer', $this->Serializable->getSerializer(), 'ISerializer::getSerializer() Should return an instance of ISerializer');
    }

    /**
     * @covers ::clearStatus
     * @covers \BLW\Type\ASerializable::clearStatus
     */
    public function test_clearStatus()
    {
        $this->assertEquals(IDataMapper::UPDATED, $this->Serializable->clearStatus(), 'ISerializer::clearStatus() Should return IDataMapper::UPDATED');

        $Property = new ReflectionProperty($this->Serializable, '_Status');

        $Property->setAccessible(true);
        $this->assertSame(0, $Property->getValue($this->Serializable), 'ISerializer::clearStatus() Failed to reset $_Status');
    }

    /**
     * @covers ::serializeWith
     * @covers \BLW\Type\ASerializable::serializeWith
     */
    public function test_serializeWith()
    {
        @$this->Serializable->foo = 1;
        @$this->Serializable->bar = 1;
        @$this->Serializable->pie = 1;
        $Serialized               = $this->Serializable->serializeWith($this->Serializer, -1);

        $this->assertEquals(spl_object_hash($this->Serializable), $Serialized, 'ISerializable::serializeWith(MockSerializer) should return spl_object_hash() of object.');
        $this->assertEquals(-1, $this->Serializer->flags, 'ISerializable::serializeWith(MockSeraializer) failed to pass serializer flags to serializer');
    }

    /**
     * @covers ::unserializeWith
     * @covers \BLW\Type\ASerializable::unserializeWith
     */
    public function test_unserializeWith()
    {
        $Unserialized             = clone $this->Serializable;
        @$this->Serializable->foo = 1;
        @$this->Serializable->bar = 1;
        @$this->Serializable->pie = 1;
        $Serialized               = $this->Serializable->serializeWith($this->Serializer);

        $this->assertTrue($Unserialized->unserializeWith($this->Serializer, $Serialized, -1));
        $this->assertEquals($this->Serializable, $Unserialized, 'ISerializable::unserializeWith(MockSerializer) should equal $this->Serializable');
        $this->assertEquals(-1, $this->Serializer->flags, 'ISerailizer::unserializeWith(MockSerializer) failed to pass serializer flags to serializer');

        # Invalid args
        $this->assertFalse($this->Serializable->unserializewith($this->Serializer, ''), 'ISerializable::unserializeWith() should return false');

        try {
            $this->Serializable->unserializewith($this->Serializer, null);
            $this->fail('Failed to generate exception with invalid arguments');
        } catch (InvalidArgumentException $e) {}

        try {
            $this->Serializable->unserializewith($this->Serializer, new \SplFileInfo(__FILE__));
            $this->fail('Failed to generate exception with invalid arguments');
        } catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::serialize
     * @covers \BLW\Type\ASerializable::serialize
     */
    public function test_serialize()
    {
        @$this->Serializable->foo = 1;
        @$this->Serializable->bar = 1;
        @$this->Serializable->pie = 1;
        $Start                    = sprintf('C:%d:"%s":', strlen(get_class($this->Serializable)), get_class($this->Serializable));
        $Serialized               = serialize($this->Serializable);

        $this->assertStringStartsWith($Start, $Serialized, 'serialize(ISerializable) returned an invalid value');
        $this->assertStringEndsWith('}', $Serialized, 'serialize(ISerializable) returned an invalid value');
    }

    /**
     * @covers ::unserialize
     * @covers \BLW\Type\ASerializable::unserialize
     */
    public function test_unserialize()
    {
        @$this->Serializable->foo = 1;
        @$this->Serializable->bar = 1;
        @$this->Serializable->pie = 1;
        $Serialized               = unserialize(serialize($this->Serializable));

        $this->assertEquals($this->Serializable, $Serialized, 'unserialize(serialize(ISerializable)) !== ISerializable spl_object_hash() of object.');

        # Invalid String
        $Data = sprintf('C:%d:"%s":0:{}', strlen(get_class($this->Serializable)), get_class($this->Serializable));

        try {
            $this->assertFalse($this->Serializable->unserialize(''), 'ISerializer::unserialize(invalid) Should return false');
        } catch (\UnexpectedValueException $e) {} catch (\RuntimeException $e) {}
    }

    /**
     * @covers ::doSerialize
     * @covers \BLW\Type\ASerializable::doSerialize
     */
    public function test_doSerialize()
    {
        # Nothing to check simply call function and see if an error is generated
        $this->Serializable->doSerialize();
    }

    /**
     * @covers ::doUnserialize
     * @covers \BLW\Type\ASerializable::doUnserialize
     */
    public function test_doUnserialize()
    {
        # Nothing to check simply call function and see if an error is generated
        $this->Serializable->doUnSerialize();
    }
}
