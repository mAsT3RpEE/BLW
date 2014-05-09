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
namespace BLW\Model\DOM;

use ReflectionProperty;
use ReflectionMethod;
use DOMElement;
use DOMAttr;
use DOMText;
use DOMCdataSection;
use DOMDocument;

use BLW\Model\DOM\Document;
use BLW\Model\DOM\Exception as DOMException;
use BLW\Model\InvalidArgumentException;
use BLW\Model\DOM\Element;


/**
 * Test for base html Object.
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\DOM\Element
 */
class ElementTest  extends \PHPUnit_Framework_TestCase
{
    const HTML = '<html><head><title>Untitled</title></head><body><h1>Heading</h1><p>Paragraph</p></body></html>';

    /**
     * @var \BLW\Model\DOM\Element
     */
    protected $Element = NULL;

    protected function setUp()
    {
        $Document =  new Document('1.0', 'UTF-8', '\\BLW\\Model\\DOM\\Element');

        $Document->loadHTML(self::HTML);

        $this->Element  = $Document->documentElement->lastChild;
    }

    protected function tearDown()
    {
        $this->Element = NULL;
    }

    /**
     * @covers ::getFactoryMethods
     */
    public function test_getFactoryMethods()
    {
        $this->assertNotEmpty($this->Element->getFactoryMethods(), 'IElement::getFactoryMethods() Returned an invalid value');
        $this->assertContainsOnlyInstancesOf('ReflectionMethod', $this->Element->getFactoryMethods(), 'IElement::getFactoryMethods() Returned an invalid value');
    }

    public function generateValidHTML()
    {
        return array(
        	 array(self::HTML, 'html')
            ,array("<DOCTYPE html>".self::HTML, 'html')
            ,array('<div><span>foo</span><span>bar</bar></div>', 'div')
            ,array('<span>foo</span><span>bar</bar>', 'span')
        );
    }

    /**
     * @covers ::createFromString
     */
    public function test_createFromString()
    {
        # Valid HTML
        foreach($this->generateValidHTML() as $Arguments) {
            list($HTML, $Tag) = $Arguments;

            $Element = $this->Element->createFromString($HTML);
            $this->assertInstanceof('DOMElement', $Element, 'IElement::createFromString() Returned an invalid value');
            $this->assertSame($Tag, $Element->tagName, 'IElement::createFromString() Returned an invalid value');
        }

        # Empty element
        $this->assertNull($this->Element->createFromString('foo'), 'IElement::createFromString() should return NULL');

        # Badly encoded HTML
        try {
            $this->Element->createFromString("\xFF");
            $this->fail('Failed to generate exception with bad encoding');
        }

        catch(DOMException $e) {}

        # Invalid HTML
        try {
            $this->Element->createFromString(array());
            $this->fail('Failed to generate exception with invalid HTML');
        }

        catch(InvalidArgumentException $e) {}
    }

    /**
     * @covers ::createDocument
     */
    public function test_createDocument()
    {
        $this->assertInstanceof('DOMDocument', $this->Element->createDocument(), 'IElement::createDocument() Returned an invalid value');
    }

    /**
     * @covers ::getDocument
     */
    public function test_getDocument()
    {
        # DOMelement with Document
        $this->assertSame($this->Element->ownerDocument, $this->Element->getDocument($this->Element), 'IElement::getDocument() Returned an invalid value');

        # Element without Document
        $this->assertInstanceOf('DOMDocument', $this->Element->getDocument(new DOMElement('span', 'foo')), 'IElement::getDocument() Returned an invalid value');
        $this->assertInstanceOf('DOMDocument', $this->Element->getDocument(new DOMAttr('id', 'foo')), 'IElement::getDocument() Returned an invalid value');
        $this->assertInstanceOf('DOMDocument', $this->Element->getDocument(new DOMText('foo')), 'IElement::getDocument() Returned an invalid value');
        $this->assertInstanceOf('DOMDocument', $this->Element->getDocument(new DOMCdataSection('foo')), 'IElement::getDocument() Returned an invalid value');

        # Invalid Node
        try {
            $this->Element->getDocument(new DOMDocument('1.0'));
            $this->fail('Failed to generate exception with invalid arguments');
        }

        catch (DOMException $e) {}
    }

    /**
     * @covers ::getInnerHTML
     */
    public function test_getInnerHTML()
    {
        $Expected = "<h1>Heading</h1>\n<p>Paragraph</p>";

        $this->assertSame($Expected, $this->Element->getInnerHTML(), 'IElement::getInnerHTML() Returned an invalid value');

        # No owner document
        $Element = new Element('span', 'foo');

        $this->assertFalse($Element->getInnerHTML(), 'IElement::getInnerHTML() should return NULL');
    }

    /**
     * @depends test_getInnerHTML
     * @covers ::setInnerHTML
     */
    public function test_setInnerHTML()
    {
        # Valid HTML
        $this->assertInstanceof('DOMNode', $this->Element->setInnerHTML('<span>foo</span><span>foo</span>'), 'IElement::setInnerHTML() should return an DOMNode');
        $this->assertEquals(2, $this->Element->childNodes->length, 'Element::setInnerHTML() Did not update DOM');
        $this->assertInstanceof('DOMNode', $this->Element->setInnerHTML('<span>foo</span><span>foo</span><span>foo</span>'), 'IElement::setInnerHTML() should return an DOMNode');
        $this->assertEquals(3, $this->Element->childNodes->length, 'Element::setInnerHTML() Did not update DOM');
        $this->assertInstanceof('DOMNode', $this->Element->setInnerHTML(''), 'IElement::setInnerHTML() should return an DOMNode');
        $this->assertEquals(0, $this->Element->childNodes->length, 'Element::setInnerHTML() Did not update DOM');

        # Invalid Encoding
        try {
            $this->Element->setInnerHTML("\xFF");
            $this->fail('Failed to generate exception with invalid HTML');
        }

        catch (DOMException $e) {}

        # Invalid HTML

        // TODO Invalid HTML test

        # No owner document
        $Element = new Element('span', 'foo');

        try {
            $Element->setInnerHTML('<foo>bar</foo>');
            $this->fail('Failed to generate exception with readonly element');
        }

        catch (DOMException $e) {}
    }

    /**
     * @covers ::getOuterHTML
     */
    public function test_getOuterHTML()
    {
        $Expected = "<body>\n<h1>Heading</h1>\n<p>Paragraph</p>\n</body>";
        $this->assertEquals($Expected, $this->Element->getOuterHTML(), 'IElement::getOuterHTML() Returned an invalid value');

        # No owner document
        $Element = new Element('span', 'foo');

        $this->assertFalse($Element->getOuterHTML(), 'IElement::getOuterHTML() should return NULL');
    }

    /**
     * @depends test_getOuterHTML
     * @covers ::setOuterHTML
     */
    public function test_setOuterHTML()
    {
        # Valid HTML
        $Replacement = $this->Element->setOuterHTML('<body>foo</body>');

        $this->assertSame($this->Element, $Replacement, 'IElement::setOuterHTML() Returned an invalid value');

        # Invalid Encoding
        try {
            $this->Element->setOuterHTML("\xFF");
            $this->fail('Failed to generate exception with invalid HTML');
        }

        catch (DOMException $e) {}

        # Invalid HTML
        $this->assertFalse($this->Element->setOuterHTML(''), 'IElement::setOuterHTML() should return FALSE');

        try {
            $this->Element->setOuterHTML(NULL);
            $this->fail('Failed to generate exception with invalid HTML');
        }

        catch (InvalidArgumentException $e) {}

        # No owner document
        $Element = new Element('span', 'foo');

        try {
            $Element->setOuterHTML('<foo>bar</foo>');
            $this->fail('Failed to generate exception with readonly element');
        }

        catch (DOMException $e) {}
    }

    /**
     * @covers ::append
     */
    public function test_append()
    {
        $Document = new \DOMDocument('1.0');
        $Node     = $Document->createTextNode('foo');

        $this->assertEquals($this->Element, $this->Element->append($Node), 'IElement::append() Returned an invalid result');
        $this->assertSame('foo', $this->Element[2]->C14N(), 'IElement::append() Failed to add node to element');

        # No owner document
        $Element = new Element('span', 'foo');

        try {
            $Element->append($Node);
            $this->fail('Failed to generate exception with readonly element');
        }

        catch (DOMException $e) {}
    }

    /**
     * @covers ::prepend
     */
    public function test_prepend()
    {
        $Document = new \DOMDocument('1.0');
        $Node     = $Document->createTextNode('foo');

        $this->assertEquals($this->Element, $this->Element->prepend($Node), 'IElement::prepend() Returned an invalid result');
        $this->assertEquals('foo', $this->Element[0]->C14N(), 'IElement::prepend() Failed to add node to element');

        # No children
        while ($this->Element->firstChild)
            $this->Element->removeChild($this->Element->firstChild);

        $this->assertEquals($this->Element, $this->Element->prepend($Node), 'IElement::prepend() Returned an invalid result');
        $this->assertEquals('foo', $this->Element[0]->C14N(), 'IElement::prepend() Failed to add node to element');

        # No owner document
        $Element = new Element('span', 'foo');

        try {
            $Element->prepend($Node);
            $this->fail('Failed to generate exception with readonly element');
        }

        catch (DOMException $e) {}
    }

    /**
     * @covers ::replace
     */
    public function test_replace()
    {
        $Document = new \DOMDocument('1.0');

        $Document->registerNodeClass('DOMElement', '\\BLW\\Model\\DOM\\Element');
        $Document->loadHTML('<body><div><span>foo</span></div></body>');

        $Node     = $Document->lastChild->lastChild;

        $this->assertEquals($Node, $this->Element->replace($Node), 'IElement::replace() Returned an invalid result');
        $this->assertEquals($Node, $this->Element->ownerDocument->lastChild->lastChild, 'IElement::replace() Failed to add node to element');

        # No owner document
        $Element = new Element('span', 'foo');

        try {
            $Element->replace($Node);
            $this->fail('Failed to generate exception with readonly element');
        }

        catch (DOMException $e) {}
    }

    /**
     * @covers ::wrapInner
     */
    public function test_wrapInner()
    {
        $Expected = "<body><div>\n<h1>Heading</h1>\n<p>Paragraph</p>\n</div></body>";
        $Document = new \DOMDocument('1.0');

        $Document->registerNodeClass('DOMElement', '\\BLW\\Model\\DOM\\Element');

        $Element = $Document->createElement('div');

        $this->assertEquals($this->Element, $this->Element->wrapInner($Element), 'IElement::wrapInner() Returned an invalid result');
        $this->assertEquals($Expected, $this->Element->getOuterHTML(), 'IElement::wrapInner() Failed to update element');

        # No owner document
        $Element = new Element('span', 'foo');

        try {
            $Element->wrapInner($Element);
            $this->fail('Failed to generate exception with readonly element');
        }

        catch (DOMException $e) {}
    }

    /**
     * @covers ::wrapOuter
     */
    public function test_wrapOuter()
    {
        $Expected = "<body>\n<div><h1>Heading</h1></div>\n<p>Paragraph</p>\n</body>";
        $Document = new \DOMDocument('1.0');

        $Document->registerNodeClass('DOMElement', '\\BLW\\Model\\DOM\\Element');

        $Element = $Document->createElement('div');
        $Heading = $this->Element->firstChild;

        $this->assertEquals($Heading, $Heading->wrapOuter($Element), 'IElement::wrapOuter() Returned an invalid result');
        $this->assertEquals($Expected, $this->Element->getOuterHTML(), 'IElement::wrapOuter() Failed to update element');

        # No owner document
        $Element = new Element('span', 'foo');

        try {
            $Element->wrapOuter($Element);
            $this->fail('Failed to generate exception with readonly element');
        }

        catch (DOMException $e) {}
    }

    /**
     * @covers ::filterXPath
     */
    public function test_filterXPath()
    {
        $Container = $this->Element->filterXPath('*');

        $this->assertCount(2, $Container, 'IElement::filterXPath() Returned an invalid value');
        $this->assertEquals('<h1>Heading</h1>', $Container[0]->C14N(), 'IElement::filterXPath() returned invalid element list');

        $Container = $this->Element->filterXPath('h1');

        $this->assertCount(1, $Container, 'IElement::filterXPath() Returned an invalid value');
        $this->assertEquals('<h1>Heading</h1>', $Container[0]->C14N(), 'IElement::filterXPath() returned invalid element list');
    }

    /**
     * @covers ::filter
     */
    public function test_filter()
    {
        $Container = $this->Element->filter('h1');

        $this->assertCount(1, $Container, 'IElement::filterXPath() Returned an invalid value');
        $this->assertEquals('<h1>Heading</h1>', $Container[0]->C14N(), 'IElement::filterXPath() returned invalid element list');
    }

    /**
     * @covers BLW\Type\DOM\AElement::__get()
     */
    public function test_get()
    {
        # Document
        $this->assertSame($this->Element->getDocument($this->Element), $this->Element->Document, 'IElement::$Document should equal IElement::getDocument()');
    }


    /**
     * @depends test_get
     * @covers BLW\Type\DOM\AElement::__isset
     */
    public function test_isset()
    {
        # Document
        $this->assertTrue(isset($this->Element->Document), 'IElement::$Document should exist');
    }

    /**
     * @depends test_get
     * @covers BLW\Type\DOM\AElement::__set
     */
    public function test_set()
    {
        # Document
        try {
            $this->Element->Document = new \DOMDocument('1.0', 'UTF-8');
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }
   }
}