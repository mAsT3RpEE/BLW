<?php
/**
 * ContainerTest.php | Feb 12, 2014
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
use ArrayObject;
use BadMethodCallException;
use DOMElement;

use BLW\Type\IIterable;
use BLW\Type\IDataMapper;
use BLW\Type\IContainer;

use BLW\Model\InvalidArgumentException;

/**
 * Tests BLW Library Adaptor type.
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\AContainer
 */
class ContainerTest extends \BLW\Type\SerializableTest
{
    /**
     * @var \BLW\Type\IContainer
     */
    protected $Container = NULL;

    protected function setUp()
    {
        $this->Container = $this->getMockForAbstractClass('\\BLW\\Type\\AContainer', array(
             'DOMNode'
            ,'integer'
            ,'string'
            ,'resource'
        ));

        $this->Serializable = $this->Container;
        $this->Serializer   = new \BLW\Model\Serializer\Mock;
    }

    protected function tearDown()
    {
        $this->Container    = NULL;
        $this->Serializer   = NULL;
        $this->Serializable = NULL;
    }

    public function generateItems()
    {
        return array(
             array(1)
            ,array(new \DOMElement('span', 'test'))
            ,array('test')
            ,array($this->getMockForAbstractClass('\\BLW\\Type\\IIterable'))
        );
    }

    /**
     * @covers ::__construct
     */
    public function test_contruct()
    {
        // Default type
        $Container = $this->getMockForAbstractClass('\\BLW\\Type\\AContainer');

        $this->assertAttributeSame(array(IContainer::DEFAULT_TYPE), '_Types', $Container, 'IContainer::__construct() Failed to set default types');

        $Disordered = array('a', 'z', 'd' , 'b');
        $Ordered    = array('a', 'b', 'd' , 'z');
        $Container  = $this->getMockForAbstractClass('\\BLW\\Type\\AContainer', $Disordered);

        $this->assertAttributeEquals($Ordered, '_Types', $Container, 'IContainer::__construct() Failed to order $_Types');
    }

    /**
     * @covers ::getParent
     */
    public function test_getParent()
    {
        $Expected = $this->getMockForAbstractClass('\\BLW\\Type\\IObject');
        $Property = new ReflectionProperty($this->Container, '_Parent');

        $Property->setAccessible(true);
        $Property->setValue($this->Container, $Expected);

        $this->assertSame($Expected, $this->Container->getParent(), 'IIterable::getParent() should equal $_Parent.');
    }

    /**
     * @covers ::setParent
     */
    public function test_setParent()
    {
        $Expected = $this->getMockForAbstractClass('\\BLW\\Type\\IObject');

        // Valid arguments
        $this->assertEquals(IDataMapper::UPDATED, $this->Container->setParent($Expected), 'IIterable::setParent() did not return IDataMapper::UPDATED');
        $this->assertEquals(IDataMapper::ONESHOT, $this->Container->setParent($Expected), 'IIterable::setParent() should return IDataMapper::ONESHOT');

        $this->assertSame($Expected, $this->Container->getParent(), 'IIterable::setParent() Failed to update $_Parent');

        // Invalid arguments
        $this->assertEquals(IDataMapper::INVALID, $this->Container->setParent($this->Container), 'IIterable::setParent() should return IDataMapper::INVALID');
        $this->assertEquals(IDataMapper::INVALID, $this->Container->setParent(null), 'IIterable::setParent() should return IDataMapper::ONESHOT');
   }

   /**
    * @depends test_setParent
    * @covers ::clearParent
    */
    public function test_clearParent()
    {
        $Parent = $this->getMockForAbstractClass('\\BLW\\Type\\IObject');

        $this->assertEquals(IDataMapper::UPDATED, $this->Container->clearParent($Parent), 'clearParent did not return IDataMapper::UPDATED');
        $this->assertEquals(IDataMapper::UPDATED, $this->Container->setParent($Parent), 'setParent did not return IDataMapper::UPDATED');
        $this->assertEquals(IDataMapper::UPDATED, $this->Container->clearParent($Parent), 'clearParent did not return IDataMapper::UPDATED');
        $this->assertNull($this->Container->getParent(), 'getParent() should return NULL.');
        $this->assertEquals(IDataMapper::UPDATED, $this->Container->setParent($Parent), 'setParent did not return IDataMapper::UPDATED');
   }

   /**
     * @dataProvider generateItems
     * @covers ::validateValue
     */
    public function test_validateValue($Valid)
    {
        $this->assertSame($Valid instanceof \DOMNode || is_scalar($Valid), $this->Container->validateValue($Valid), sprintf('Value (%s) should be valid', print_r($Valid, true)));
    }

    /**
     * @dataProvider generateItems
     * @covers ::offsetSet
     */
    public function test_offsetSet($Valid, $Invalid = array())
    {
        # Test valid
        $this->Container['test'] = $Valid;
        $this->assertSame($Valid, $this->Container['test'], 'IContainer[test] should equal $Valid');

        # Test invalid
        try {
            $this->Container['test'] = $Invalid;
            $this->fail('Unable to generate exception with invalid value');
        }

        catch(\UnexpectedValueException $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid exception: '.$e->getMessage());
        }
    }

    /**
     * @dataProvider generateItems
     * @covers ::append
     */
    public function test_append($Valid, $Invalid = array())
    {
        # Test valid
        $this->Container->append($Valid);
        $this->assertSame($Valid, $this->Container[0], 'IContainer[0] should equal $Valid');

        # Test invalid
        try {
            $this->Container->append($Invalid);
            $this->fail('Unable to generate exception with invalid value');
        }

        catch(\UnexpectedValueException $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid exception: '.$e->getMessage());
        }
    }

    /**
     * @covers ::filter()
     */
    public function test_filter()
    {
        $Test1 = function($v) {return $v instanceof \DOMNode;};
        $Test2 = function($v) {return is_int($v);};

        $Node                       = new \DOMElement('span', 'test');
        $this->Container['foo']     = 1;
        $this->Container['bar']     = 1;
        $this->Container['test']    = 'test';
        $this->Container['element'] = $Node;

        $this->assertSame(array($Node), @$this->Container->filter($Test1), 'IContainer::filter() should return 1 item (DOMNode)');
        $this->assertSame(array(1,1), @$this->Container->filter($Test2), 'IContainer::filter() should return 1 item (int)');

        # Invalid value
        try {
            $this->Container->filter(null);
            $this->fail('Failed to generate exception with invalid arguments');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::each()
     */
    public function test_each()
    {
        $Test1 = function($v) {return is_int($v);};
        $Test2 = array('foo' => true, 'bar' => true, 'test' => false, 'element' => false);

        $this->Container['foo']     = 1;
        $this->Container['bar']     = 1;
        $this->Container['test']    = 'test';
        $this->Container['element'] = new \DOMElement('span', 'test');

        $this->Container->each($Test1, $Results);

        $this->assertEquals($Test2, $Results, 'IContainer::each() should return array(true, true, false, false)');

        # Invalid arguments
        try {
            $this->Container->each(null);
            $this->fail('Failed to generate exception with invalid arguments');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::walk()
     */
    public function test_walk()
    {
        $Test1 = function($v) {return is_int($v);};
        $Test2 = array('foo' => true, 'bar' => true, 'test' => false, 'element' => false, 'child' => array('foo' => true, 'self' => false), 'self' => false);

        $this->Container['foo']          = 1;
        $this->Container['bar']          = 1;
        $this->Container['test']         = 'test';
        $this->Container['element']      = new \DOMElement('span', 'test');
        $this->Container['child']        = $this->getMockForAbstractClass('\\BLW\\Type\\AContainer', array('integer'));
        $this->Container['child']['foo'] = 1;

        $this->Container->walk($Test1, $Results);

        $this->assertEquals($Test2, $Results, 'IContainer::each() should return array(true, true, false, false, array(true, false), false');

        # Invalid arguments
        try {
            $this->Container->walk(null);
            $this->fail('Failed to generate exception with invalid arguments');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->Container['foo']     = 1;
        $this->Container['bar']     = 1;
        $this->Container['test']    = 'test';
        $this->Container['element'] = new \DOMElement('span', 'test');
        $this->Container['resource'] = stream_context_create(array(
            'http' => array(
                'method' => "GET",
                'header' => "Accept-language: en\r\n" .
                "Cookie: foo=bar\r\n"
            )
        ));

        $this->assertEquals('[IContainer:1,1,test,DOMElement,resource]', strval($this->Container),'(string) IContainer returned an invalid format');
    }

    /**
     * @covers ::getID
     */
    public function test_getID()
    {
        $this->Container['foo']      = 1;
        $this->Container['bar']      = 1;
        $this->Container['test']     = 'test';
        $this->Container['element']  = new \DOMElement('span', 'test');
        $this->Container['resource'] = stream_context_create(array(
            'http' => array(
                'method' => "GET",
                'header' => "Accept-language: en\r\n" .
                            "Cookie: foo=bar\r\n"
            )
        ));

        $this->assertEquals('6067805dec5a61fab70ae6011c942cdb', strval($this->Container->getID()),'IContainer::getID() returned an invalid value');
    }
}