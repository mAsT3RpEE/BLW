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
namespace BLW\Tests\Type;

use ArrayObject;
use PHPUnit_Framework_Error_Notice;
use BadMethodCallException;
use DOMElement;
use BLW\Type\IIterable;

/**
 * Tests BLW Library Adaptor type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\AContainer
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
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
        ));
    }

    protected function tearDown()
    {
        $this->Container = NULL;
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
     * @dataProvider generateItems
     * @covers ::validateValue
     */
    public function test_validateValue($Valid)
    {
        if (!$Valid instanceof IIterable) {
            $this->assertTrue($this->Container->validateValue($Valid), sprintf('Value (%s) should be valid', print_r($Valid, true)));
        }
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

        $this->assertEquals('[IContainer:1,1,test,DOMElement]', strval($this->Container),'(string) IContainer returned an invalid format');
    }

    /**
     * @covers ::getID
     */
    public function test_getID()
    {
        $this->Container['foo']     = 1;
        $this->Container['bar']     = 1;
        $this->Container['test']    = 'test';
        $this->Container['element'] = new \DOMElement('span', 'test');

        $this->assertEquals('cbc3742610837942c7a1af0114247fd0', strval($this->Container->getID()),'IContainer::getID() returned an invalid value');
    }
}