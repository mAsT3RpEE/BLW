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
namespace BLW\Tests\Model\DOM;

use ReflectionProperty;
use ReflectionMethod;
use DOMElement;
use BLW\Model\DOM\Document;
use BLW\Model\DOM\Exception as DOMException;
use BLW\Model\InvalidArgumentException;


/**
 * Test for base html Object.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
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
            $this->assertInstanceof('DOMElement', $Element, 'IDOMElement::createFromString() Returned an invalid value');
            $this->assertSame($Tag, $Element->tagName, 'IDOMElement::createFromString() Returned an invalid value');
        }

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
        $this->assertInstanceof('DOMDocument', $this->Element->createDocument(), 'IDOMElement::createDocument() Returned an invalid value');
    }

    /**
     * @covers ::getDocument
     */
    public function test_getDocument()
    {
        # DOMelement with Document
        $this->assertSame($this->Element->ownerDocument, $this->Element->getDocument($this->Element), 'IDOMElement::getDocument() Returned an invalid value');

        # Element without Document
        $this->assertInstanceOf('DOMDocument', $this->Element->getDocument(new DOMElement('span', 'foo')), 'IDOMElement::getDocument() Returned an invalid value');
    }

    /**
     * @covers ::getInnerHTML
     */
    public function test_getInnerHTML()
    {
        $Expected = "<h1>Heading</h1>\n<p>Paragraph</p>";

        $this->assertSame($Expected, $this->Element->getInnerHTML(), 'IDOMElement::getInnerHTML() Returned an invalid value');
    }

    /**
     * @depends test_getInnerHTML
     * @covers ::setInnerHTML
     */
    public function test_setInnerHTML()
    {
        # Valid HTML
        $this->Element->setInnerHTML('<span>foo</span><span>foo</span>');

        $this->assertEquals(2, $this->Element->childNodes->length, 'Element::setInnerHTML() Did not update DOM');

        $this->Element->setInnerHTML('<span>foo</span><span>foo</span><span>foo</span>');

        $this->assertEquals(3, $this->Element->childNodes->length, 'Element::setInnerHTML() Did not update DOM');

        # Invalid Encoding
        try {
            $this->Element->setInnerHTML("\xFF");
            $this->fail('Failed to generate exception with invalid HTML');
        }

        catch (DOMException $e) {}

        # Invalid HTML
        try {
            $this->Element->setInnerHTML(NULL);
            $this->fail('Failed to generate exception with invalid HTML');
        }

        catch (InvalidArgumentException $e) {}


    }

    /**
     * @covers ::getOuterHTML
     */
    public function test_getOuterHTML()
    {
        $Expected = "<body>\n<h1>Heading</h1>\n<p>Paragraph</p>\n</body>";
        $this->assertEquals($Expected, $this->Element->getOuterHTML(), 'IDOMElement::getOuterHTML() Returned an invalid value');
    }

    /**
     * @depends test_getOuterHTML
     * @covers ::setOuterHTML
     */
    public function test_setOuterHTML()
    {
        # Valid HTML
        $Replacement = $this->Element->setOuterHTML('<body>foo</body>');

        $this->assertSame($this->Element, $Replacement, 'IDOMElement::setOuterHTML() Returned an invalid value');

        # Invalid Encoding
        try {
            $this->Element->setInnerHTML("\xFF");
            $this->fail('Failed to generate exception with invalid HTML');
        }

        catch (DOMException $e) {}

        # Invalid HTML
        try {
            $this->Element->setInnerHTML(NULL);
            $this->fail('Failed to generate exception with invalid HTML');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::append
     */
    public function test_append()
    {
        $Document = new \DOMDocument('1.0');
        $Node     = $Document->createTextNode('foo');

        $this->assertEquals($this->Element, $this->Element->append($Node), 'IDOMElement::append() Returned an invalid result');
        $this->assertSame('foo', $this->Element[2]->C14N(), 'IDOMElement::append() Failed to add node to element');
    }

    /**
     * @covers ::prepend
     */
    public function test_prepend()
    {
        $Document = new \DOMDocument('1.0');
        $Node     = $Document->createTextNode('foo');

        $this->assertEquals($this->Element, $this->Element->prepend($Node), 'IDOMElement::prepend() Returned an invalid result');
        $this->assertEquals('foo', $this->Element[0]->C14N(), 'IDOMElement::prepend() Failed to add node to element');
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

        $this->assertEquals($Node, $this->Element->replace($Node), 'IDOMElement::replace() Returned an invalid result');
        $this->assertEquals($Node, $this->Element->ownerDocument->lastChild->lastChild, 'IDOMElement::replace() Failed to add node to element');
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

        $this->assertEquals($this->Element, $this->Element->wrapInner($Element), 'IDOMElement::wrapInner() Returned an invalid result');
        $this->assertEquals($Expected, $this->Element->getOuterHTML(), 'IDOMElement::wrapInner() Failed to update element');
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

        $this->assertEquals($Heading, $Heading->wrapOuter($Element), 'IDOMElement::wrapOuter() Returned an invalid result');
        $this->assertEquals($Expected, $this->Element->getOuterHTML(), 'IDOMElement::wrapOuter() Failed to update element');
    }

    /**
     * @covers ::filterXPath
     */
    public function test_filterXPath()
    {
        $Container = $this->Element->filterXPath('*');

        $this->assertCount(2, $Container, 'IDOMElement::filterXPath() Returned an invalid value');
        $this->assertEquals('<h1>Heading</h1>', $Container[0]->C14N(), 'IDOMElement::filterXPath() returned invalid element list');

        $Container = $this->Element->filterXPath('h1');

        $this->assertCount(1, $Container, 'IDOMElement::filterXPath() Returned an invalid value');
        $this->assertEquals('<h1>Heading</h1>', $Container[0]->C14N(), 'IDOMElement::filterXPath() returned invalid element list');
    }

    /**
     * @covers ::filter
     */
    public function test_filter()
    {
        $Container = $this->Element->filter('h1');

        $this->assertCount(1, $Container, 'IDOMElement::filterXPath() Returned an invalid value');
        $this->assertEquals('<h1>Heading</h1>', $Container[0]->C14N(), 'IDOMElement::filterXPath() returned invalid element list');
    }

    /**
     * @depends test_getDocument
     * @depends test_getInnerHTML
     * @depends test_getOuterHTML
     */
    public function test_get()
    {
        # ID
        $this->assertSame($this->Element->getID(), $this->Element->ID, 'IElement::$ID should equal IElement::getID()');

        # Parent
        $this->assertSame($this->Element->getParent(), $this->Element->Parent, 'IElement::$Parent should equal IElement::getParent()');

        # Document
        $this->assertSame($this->Element->getDocument($this->Element), $this->Element->Document, 'IElement::$Document should equal IElement::getDocument()');

        # innerHTML
        $this->assertSame($this->Element->getInnerHTML(), $this->Element->innerHTML, 'IElement::$innerHTML should equal IElement::getInnerHTML()');

        # outerHTML
        $this->assertSame($this->Element->getOuterHTML(), $this->Element->outerHTML, 'IElement::$outerHTML should equal IElement::getOuterHTML()');

        # Undefined
        try {
        	$this->Element->undefined;
        	$this->fail('Failed to generate notice with undefined property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {}
    }


    /**
     * @depends test_get
     * @covers BLW\Type\DOM\AElement::__isset
     */
    public function test_isset()
    {
        # ID
        $this->assertTrue(isset($this->Element->ID), 'IElement::$Parent should exist');

        # Parent
        $this->assertFalse(isset($this->Element->Parent), 'IElement::$Parent should exist');

        # Document
        $this->assertTrue(isset($this->Element->Document), 'IElement::$Document should exist');

        # innerHTML
        $this->assertTrue(isset($this->Element->innerHTML), 'IElement::$innerHTML should exist');

        # outerHTML
        $this->assertTrue(isset($this->Element->outerHTML), 'IElement::$outerHTML should exist');

        # Undefined
        $this->assertFalse(isset($this->Element->undefined), 'IElement::$undefined should not exist');
    }

    /**
     * @depends test_get
     * @covers BLW\Type\DOM\AElement::__set
     */
    public function test_set()
    {
        # Parent
        $this->Element->Parent = $this->getMockForAbstractClass('\\BLW\\Type\\IObject');
        $this->assertSame($this->Element->Parent, $this->Element->getParent(), 'IElement::$Parent should equal IElement::getParent');
        $this->assertTrue(isset($this->Element->Parent), 'IElement::$Parent should exist');

	    # ID
        $this->Element->ID = 'foo';
        $this->assertSame($this->Element->ID, 'foo', 'IElement::$ID should equal `foo');

        # Document
        try {
            $this->Element->Document = new \DOMDocument('1.0', 'UTF-8');
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        # innerHTML
        try { $this->Element->innerHTML = '<span>foo</span>'; }

        catch (\RuntimeException $e) {}

        # outerHTML
        $this->Element->outerHTML = '<body>frooogli</body>';

        $this->assertEquals('<body>frooogli</body>', $this->Element->ownerDocument->documentElement->lastChild->getOuterHTML(), 'IDocument::$outerHTML failed to call setOuterHTML()');

        # Undefined
        try {
            $this->Element->undefined = '';
            $this->fail('Failed to generate notice with undefined property');
        }

        catch (\PHPUnit_Framework_Error $e) {
            $this->assertContains('non-existant property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }
    }

    /**
     * @depends test_get
     * @covers BLW\Type\DOM\AElement::__unset
     */
    public function test_unset()
    {
        # Undefined
        unset($this->Argument->undefined);
    }
}