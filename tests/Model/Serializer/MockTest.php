<?php
/**
 * MockTest.php | Feb 12, 2014
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
namespace BLW\Model\Serializer;

use BLW\Type\ISerializer;
use BLW\Model\Serializer\Mock as Serializer;

/**
 * Tests BLW Library object serializer.
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Serializer\Mock
 */
class MockTest extends \PHPUnit_Framework_TestCase
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

        $this->Serializer                        = new Serializer;
        $this->Serializable                      = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Serializable->Child1              = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Serializable->Child2              = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Serializable->Child3              = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Serializable->Child4              = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Serializable->Child1->GrandChild1 = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Serializable->Child1->GrandChild2 = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Serializable->Child2->GrandChild3 = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Serializable->Child2->GrandChild4 = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Serializable->Child3->GrandChild5 = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Serializable->Child3->GrandChild6 = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Serializable->Child4->GrandChild1 = $this->Serializable->Child1->GrandChild1;
        $this->Serializable->Child4->GrandChild1 = $this->Serializable->Child1->GrandChild2;

        $this->Serializable->Child1->setParent($this->Serializable);
        $this->Serializable->Child2->setParent($this->Serializable);
        $this->Serializable->Child3->setParent($this->Serializable);
        $this->Serializable->Child4->setParent($this->Serializable);

        $this->Serializable->Child1->GrandChild1->setParent($this->Serializable->Child1);
        $this->Serializable->Child1->GrandChild2->setParent($this->Serializable->Child1);
        $this->Serializable->Child2->GrandChild3->setParent($this->Serializable->Child1);
        $this->Serializable->Child2->GrandChild4->setParent($this->Serializable->Child1);
        $this->Serializable->Child3->GrandChild5->setParent($this->Serializable->Child1);
        $this->Serializable->Child3->GrandChild6->setParent($this->Serializable->Child1);
    }

    protected function tearDown()
    {
        $this->Serializable = NULL;
        $this->Serializer   = NULL;
    }

    /**
     * @covers ::encode
     */
    public function test_encode()
    {
        $this->Serializable->foo = 1;
        $this->Serializable->bar = 1;
        $this->Serializable->pie = 1;
        $Serialized              = $this->Serializer->encode($this->Serializable);

        $this->assertNotEmpty($Serialized, 'ISerializer::encode() should not return an empty value');
        $this->assertEquals(spl_object_hash($this->Serializable), $Serialized, 'ISerializer::encode() should return spl_object_hash() of object');
    }

    /**
     * @covers ::decode
     */
    public function test_decode()
    {
        $Serialized              = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Serializable->foo = 1;
        $this->Serializable->bar = 1;
        $this->Serializable->pie = 1;

        $this->assertTrue($this->Serializer->decode($Serialized, $this->Serializer->encode($this->Serializable)), 'ISerializer::decode() should return TRUE');
        $this->assertEquals($this->Serializable, $Serialized, 'ISerializer::decode($Object, ISerializer::encocode()) Does not create an exact copy of the object');

        # Invalid data
        $this->assertFalse($this->Serializer->decode($Serialized, serialize(100)), 'ISerializer::decode() should return FALSE');
    }
}
