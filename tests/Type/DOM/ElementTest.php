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
namespace BLW\Type\DOM;

use ReflectionProperty;
use DOMNode;
use DOMDocument;
use BLW\Type\IDataMapper;
use BLW\Model\ClassException;

/**
 * Test for base html Object.
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
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
        $Document = new DOMDocument('1.0', 'UTF-8');
        $Element  = $this->getMockForAbstractClass('\\BLW\\Type\\DOM\\AElement', array('span'));

        $Document->registerNodeClass('DOMElement', get_class($Element));
        $Document->loadHTML(self::HTML);

        $this->Element = $Document->documentElement->lastChild;
    }

    protected function tearDown()
    {
        $this->Element = NULL;
    }

    /**
     * @covers ::getParent
     */
    public function test_getParent()
    {
        $Expected = $this->getMockForAbstractClass('\\BLW\\Type\\IObject');
        $Property = new ReflectionProperty($this->Element, '_Parent');

        $Property->setAccessible(true);
        $Property->setValue($this->Element, $Expected);

        $this->assertSame($Expected, $this->Element->getParent(), 'IIterable::getParent() should equal $_Parent.');
    }

    /**
     * @depends test_getParent
     * @covers ::setParent
     */
    public function test_setParent()
    {
        $Expected = $this->getMockForAbstractClass('\\BLW\\Type\\IObject');

        // Valid arguments
        $this->assertEquals(IDataMapper::UPDATED, $this->Element->setParent($Expected), 'IElement::setParent() did not return IDataMapper::UPDATED');
        $this->assertEquals(IDataMapper::ONESHOT, $this->Element->setParent($Expected), 'IElement::setParent() should return IDataMapper::ONESHOT');

        $this->assertSame($Expected, $this->Element->getParent(), 'IElement::setParent() Failed to update $_Parent');

        // Invalid arguments
        $this->assertEquals(IDataMapper::INVALID, $this->Element->setParent($this->Element), 'IElement::setParent() should return IDataMapper::INVALID');
        $this->assertEquals(IDataMapper::INVALID, $this->Element->setParent(null), 'IElement::setParent() should return IDataMapper::ONESHOT');
    }

   /**
    * @depends test_setParent
    * @covers ::clearParent
    */
    public function test_clearParent()
    {
        $Expected = $this->getMockForAbstractClass('\\BLW\\Type\\IObject');

        $this->assertEquals(IDataMapper::UPDATED, $this->Element->clearParent(), 'IElement::clearParent() should return IDataMapper::UPDATED');
        $this->assertEquals(IDataMapper::UPDATED, $this->Element->setParent($Expected), 'IElement::setParent() should return IDataMapper::UPDATED');
        $this->assertEquals(IDataMapper::UPDATED, $this->Element->clearParent(), 'IElement::clearParent() should return IDataMapper::UPDATED');
        $this->assertNull($this->Element->getParent(), 'IElement::clearParent() Failed to reset $_Parent');
        $this->assertEquals(IDataMapper::UPDATED, $this->Element->setParent($Expected), 'IElement::setParent() did not return IDataMapper::UPDATED');
   }

    /**
     * @covers ::offsetExists
     */
    public function test_offsetExists()
    {
        # Valid offset
        $this->assertTrue($this->Element->offsetExists(0), 'IElement[0] should exist');
        $this->assertTrue($this->Element->offsetExists(1), 'IElement[1] should exist');

        # Invalid offset
        try {
            $this->Element->offsetExists('foo');
            $this->fail('Failed to generate exception with invalid offset');
        } catch (\OutOfBoundsException $e) {}

        # undefined offset
        $this->assertFalse($this->Element->offsetExists(100), 'IElement[100] should not exist');
    }

    /**
     * @covers ::offsetGet
     */
    public function test_offsetGet()
    {
        # Valid offset
        $this->assertInstanceof('\\BLW\\Type\\DOM\\IElement', $this->Element[0], 'IElement[0] Returned an invalid value');
        $this->assertEquals('h1', $this->Element[0]->tagName, 'IElement[0] Returned an invalid value');

        $this->assertInstanceof('\\BLW\\Type\\DOM\\IElement', $this->Element[1], 'IElement::offsetGet() Returned an invalid value');
        $this->assertEquals('p', $this->Element[1]->tagName, 'IElement::offsetGet() Returned an invalid value');

        # Invalid offset
        try {
            $this->Element->offsetGet('foo');
            $this->fail('Failed to generate exception with invalid offset');
        } catch (\OutOfBoundsException $e) {}

        # undefined offset
        try {
            $this->Element->offsetGet(100);
            $this->fail('Failed to generate notice with undefined offset');
        } catch (\PHPUnit_Framework_Error_Notice $e) {}

        $this->assertNull(@$this->Element->offsetGet(100), 'IElement[undefined] should be NULL');
    }

    /**
     * @covers ::offsetSet
     */
    public function test_offsetSet()
    {

        # Valid value, valid offset
        $DOM        = new DOMDocument('1.0', 'UTF-8');
        $NewElement = $DOM->createElement('div', 'test');

        $this->assertInstanceof('DOMNode', $this->Element->offsetSet(NULL, $NewElement), 'IElement[] should return added DOMNode');
        $this->assertInstanceof('DOMNode', $this->Element->offsetSet(2, $NewElement), 'IElement[2] should return modified DOMNode');

        # Invalid offset, valid value
        try {
            $this->Element[100] = $NewElement;
            $this->fail('Failed to generate notice with undefined index');

        } catch (\PHPUnit_Framework_Error_Notice $e) {

        }

        $this->assertNull(@$this->Element->offsetSet(100, $NewElement), 'IElement::offsetSet() Returned an invalid value');

        try {
            $this->Element['foo'] = $NewElement;
            $this->fail('Failed to generate notice with undefined index');

        } catch (\OutOfBoundsException $e) {

        }

        # Invalid value, valid offset
        try {
            $this->Element[NULL] = NULL;
            $this->fail('Failed to generate exception with invalid value');

        } catch (\UnexpectedValueException $e) {

        }

        try {
            $this->Element[0] = NULL;
            $this->fail('Failed to generate exception with invalid value');

        } catch (\UnexpectedValueException $e) {

        }

        # Invalid value, invalid offset
        try {
            $this->Element[100] = NULL;
            $this->fail('Failed to generate exception with invalid value');

        } catch (\UnexpectedValueException $e) {

        }

        try {
            $this->Element->offsetSet('foo', NULL);
            $this->fail('Failed to generate exception with invalid value');

        } catch (\UnexpectedValueException $e) {

        }

        # Readonly Node
        $Element = get_class($this->Element);
        $Element = new $Element('span', 'foo');

        try {
            $Element[] = $NewElement;
            $this->fail('Failed to generate exception with readonly element');

        } catch (ClassException $e) {

        }
    }

    /**
     * @covers ::offsetUnset
     */
    public function test_offsetUnset()
    {
        $List = $this->Element->childNodes;

        // Valid index
        $this->assertEquals(2, $List->length, 'IElement is corrupt');
        unset($this->Element[1]);
        $this->assertEquals(1, $List->length, 'IElement::offsetUnset() Failed to remove child');
        unset($this->Element[0]);
        $this->assertEquals(0, $List->length, 'IElement::offsetUnset() Failed to remove child');
        unset($this->Element[0]);

        // Invalid index
        try {
            unset($this->Element['foo']);
            $this->fail('Failed to generate exception with invalid index');
        } catch (\OutOfBoundsException $e) {}

        # Readonly Node
        $Element = get_class($this->Element);
        $Element = new $Element('span', 'foo');

        try {
            unset($Element[0]);
            $this->fail('Failed to generate exception with readonly element');

        } catch (ClassException $e) {

        }
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

    /**
     * @covers ::__get()
     */
    public function test_get()
    {
        $this->Element
            ->expects($this->any())
            ->method('getInnerHTML')
            ->will($this->returnValue('<h1>Heading</h1><p>Paragraph</p>'));

        $this->Element
            ->expects($this->any())
            ->method('getOuterHTML')
            ->will($this->returnValue('<body><h1>Heading</h1><p>Paragraph</p></body>'));

        # ID
        $this->assertSame($this->Element->getID(), $this->Element->ID, 'IElement::$ID should equal IElement::getID()');

        # Parent
        $this->assertSame($this->Element->getParent(), $this->Element->Parent, 'IElement::$Parent should equal IElement::getParent()');

        # innerHTML
        $this->assertSame($this->Element->getInnerHTML(), $this->Element->innerHTML, 'IElement::$innerHTML should equal IElement::getInnerHTML()');

        # outerHTML
        $this->assertSame($this->Element->getOuterHTML(), $this->Element->outerHTML, 'IElement::$outerHTML should equal IElement::getOuterHTML()');

        # Undefined
        try {
            $this->Element->undefined;
            $this->fail('Failed to generate notice with undefined property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {}

        $this->assertNull(@$this->Element->undefined, 'IElement::$undefined should be NULL');
    }


    /**
     * @depends test_get
     * @covers ::__isset
     */
    public function test_isset()
    {
        $this->Element
            ->expects($this->any())
            ->method('getInnerHTML')
            ->will($this->returnValue('<h1>Heading</h1><p>Paragraph</p>'));

        $this->Element
            ->expects($this->any())
            ->method('getOuterHTML')
            ->will($this->returnValue('<body><h1>Heading</h1><p>Paragraph</p></body>'));

        # ID
        $this->assertTrue(isset($this->Element->ID), 'IElement::$Parent should exist');

        # Parent
        $this->assertFalse(isset($this->Element->Parent), 'IElement::$Parent should exist');

        # innerHTML
        $this->assertTrue(isset($this->Element->innerHTML), 'IElement::$innerHTML should exist');

        # outerHTML
        $this->assertTrue(isset($this->Element->outerHTML), 'IElement::$outerHTML should exist');

        # Undefined
        $this->assertFalse(isset($this->Element->undefined), 'IElement::$undefined should not exist');
    }

    /**
     * @depends test_get
     * @covers ::__set
     */
    public function test_set()
    {
        $this->Element
            ->expects($this->any())
            ->method('getInnerHTML')
            ->will($this->returnValue('<span>foo</span>'));

        $this->Element
            ->expects($this->any())
            ->method('setInnerHTML')
            ->will($this->returnValue(true));

        $this->Element
            ->expects($this->any())
            ->method('getOuterHTML')
            ->will($this->returnValue('<body>frooogli</body>'));

        $this->Element
            ->expects($this->any())
            ->method('setOuterHTML')
            ->will($this->returnValue(true));

        # Parent
        $Parent                = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Element->Parent = $Parent;

        $this->assertSame($Parent, $this->Element->Parent, 'IElement::$Parent should equal IElement::getParent()');

        try {
            $this->Element->Parent = null;
            $this->fail('Failed to generate notice with invalid value');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Element->Parent = null;

        try {
            $this->Element->Parent = $Parent;
            $this->fail('Failed to generate notice with oneshot value');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Element->Parent = $Parent;

        # ID
        $this->Element->ID = 'foo';

        $this->assertSame($this->Element->ID, 'foo', 'IElement::$ID should equal `foo');

        try {
            $this->Element->ID = null;
            $this->fail('Failed to generate notice with invalid value');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Element->ID = null;

        # innerHTML
        $this->Element->innerHTML = '<span>foo</span>';

        $this->assertSame('<span>foo</span>', $this->Element->getInnerHTML(), 'IElement::$innerHTML Failed to update Node');

        # outerHTML
        $this->Element->outerHTML = '<body>frooogli</body>';

        $this->assertEquals('<body>frooogli</body>', $this->Element->ownerDocument->documentElement->lastChild->getOuterHTML(), 'IDocument::$outerHTML failed to call setOuterHTML()');

        # Undefined
        try {
            $this->Element->undefined = '';
            $this->fail('Failed to generate notice with undefined property');
        } catch (\PHPUnit_Framework_Error $e) {
            $this->assertContains('non-existant property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Element->undefined = '';
    }

    /**
     * @depends test_get
     * @depends test_set
     * @covers ::__unset
     */
    public function test_unset()
    {
        # Parent
        $this->Element->Parent = $this->getMockForAbstractClass('\\BLW\\Type\\IObject');

        unset($this->Element->Parent);

        $this->assertNull($this->Element->Parent, 'IElement::__unset() Failed to unset $Parent');

        # Undefined
        unset($this->Element->undefined);
    }
}
