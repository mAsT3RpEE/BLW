<?php
/**
 * ElementTest.php | Apr 2, 2014
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
namespace BLW\Tests\Type\DOM;

use ReflectionProperty;
use ReflectionMethod;
use DOMNode;
use DOMDocument;
use BLW\Type\IDataMapper;

/**
 * Test for base html Object.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\DOM\AElement
 */
class ElementTest  extends \PHPUnit_Framework_TestCase
{
    const HTML = '<html><head><title>Untitled</title></head><body><h1>Heading</h1><p>Paragraph</p></body></html>';

    /**
     * @var \BLW\Type\DOM\AElement
     */
    protected $Element = NULL;

    protected function setUp()
    {
        $Element  = $this->getMockForAbstractClass('\\BLW\\Type\\DOM\\AElement', array('span'));
        $Document = new DOMDocument('1.0', 'UTF-8');

        $Document->registerNodeClass('DOMElement', get_class($Element));

        $Document->loadHTML(self::HTML);

        $this->Element = $Document->documentElement->lastChild;
    }

    protected function tearDown()
    {
        $this->Element = NULL;
    }

    /**
     * @covers ::offsetGet
     */
    public function test_offsetGet()
    {
        # Valid offset
        $this->assertInstanceof('\\BLW\\Type\\DOM\\IElement', $this->Element->offsetGet(0), 'IElement::offsetGet() Returned an invalid value');
        $this->assertEquals('h1', $this->Element->offsetGet(0)->tagName, 'IElement::offsetGet() Returned an invalid value');

        $this->assertInstanceof('\\BLW\\Type\\DOM\\IElement', $this->Element->offsetGet(1), 'IElement::offsetGet() Returned an invalid value');
        $this->assertEquals('p', $this->Element->offsetGet(1)->tagName, 'IElement::offsetGet() Returned an invalid value');

        # Invalid offset
        try {
        	$this->Element->offsetGet('foo');
        	$this->fail('Failed to generate exception with invalid offset');
        }

        catch (\OutOfBoundsException $e) {}

        # undefined offset
        try {
        	$this->Element->offsetGet(100);
        	$this->fail('Failed to generate notice with undefined offset');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {}
    }

    /**
     * @covers ::offsetExists
     */
    public function test_offsetExists()
    {
        # Valid offset
        $this->assertTrue($this->Element->offsetExists(0), 'IElement::offsetExists() should return true');
        $this->assertTrue($this->Element->offsetExists(1), 'IElement::offsetExists() should return false');

        # Invalid offset
        try {
        	$this->Element->offsetGet('foo');
        	$this->fail('Failed to generate exception with invalid offset');
        }

        catch (\OutOfBoundsException $e) {}

        # undefined offset
        $this->assertFalse($this->Element->offsetExists(100), 'IElement::offsetExists() should return true');
    }

    /**
     * @covers ::offsetSet
     */
    public function test_offsetSet()
    {

        # Valid value, valid offset
        $NewElement = $this->Element->ownerDocument->createElement('div', 'test');

        $this->assertInstanceof('DOMNode', $this->Element->offsetSet(NULL, $NewElement), 'IElement::offsetExists() should return added DOMNode');
        $this->assertInstanceof('DOMNode', $this->Element->offsetSet(2, $NewElement), 'IElement::offsetExists() should return added DOMNode');

        # Invalid offset, valid value
        try {
            $this->Element->offsetSet(100, $NewElement);
            $this->fail('Failed to generate notice with undefined index');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {}

        try {
            $this->Element->offsetSet('foo', $NewElement);
            $this->fail('Failed to generate notice with undefined index');
        }

        catch (\OutOfBoundsException $e) {}

        # Invalid value, valid offset
        try {
            $this->Element->offsetSet(NULL, NULL);
            $this->fail('Failed to generate exception with invalid value');
        }

        catch (\UnexpectedValueException $e) {}

        try {
            $this->Element->offsetSet(0, NULL);
            $this->fail('Failed to generate exception with invalid value');
        }

        catch (\UnexpectedValueException $e) {}

        # Invalid value, invalid offset
        try {
            $this->Element->offsetSet(100, NULL);
            $this->fail('Failed to generate exception with invalid value');
        }

        catch (\UnexpectedValueException $e) {}

        try {
            $this->Element->offsetSet('foo', NULL);
            $this->fail('Failed to generate exception with invalid value');
        }

        catch (\UnexpectedValueException $e) {}
    }

    /**
     * @covers ::offsetUnset
     */
    public function test_offsetUnset()
    {
        $List = $this->Element->childNodes;

        $this->assertEquals(2, $List->length, 'IElement is corrupt');
        $this->Element->offsetUnset(0);
        $this->assertEquals(1, $List->length, 'IElement::offsetUnset() Failed to remove child');
        $this->Element->offsetUnset(0);
        $this->assertEquals(0, $List->length, 'IElement::offsetUnset() Failed to remove child');
        $this->Element->offsetUnset(0);
    }

    /**
     * @covers ::count
     */
    public function test_count()
    {
        $this->assertSame($this->Element->childNodes->length, $this->Element->count(), 'IElement::count() Returned an invalid value');
    }

    /**
     * @covers ::getIterator()
     */
    public function test_getIterator()
    {
        $this->assertInstanceOf('Iterator', $this->Element->getIterator(), 'IElement::getIterator() Returned an invalid value');
        $this->assertCount(2, $this->Element->getIterator(), 'IElement::getIterator() Returned an invalid Iterator');
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $Expected = "<span>test</span>";

        $this->Element
            ->expects($this->any())
            ->method('getOuterHTML')
            ->will($this->returnValue('<span>test</span>'));

        $this->assertEquals($Expected, @strval($this->Element), '(string) IElement returned an invalid value');
    }

    /**
     * @covers ::getID
     */
    public function test_getID()
    {
        $this->assertSame('', $this->Element->getID(), 'IElement::getID() Returned an invalid value');
    }

    /**
     * @depends test_getID
     * @covers ::setID
     */
    public function test_setID()
    {
        # Valid arguments
        $this->assertSame(IDataMapper::UPDATED, $this->Element->setID('foo'), 'IElement::setID() Returned an invalid value');
        $this->assertSame('foo', $this->Element->getID(), 'IElement::getID() Failed to update ID');

        # Invalid arguments
        $this->assertSame(IDataMapper::INVALID, $this->Element->setID(array()), 'IElement::setID() Returned an invalid value');
    }
}