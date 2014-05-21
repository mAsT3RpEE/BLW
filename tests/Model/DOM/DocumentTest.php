<?php
/**
 * DocumentTest.php | Apr 2, 2014
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

use BLW\Type\IDataMapper;


/**
 * Test for base html Object.
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\DOM\Document
 */
class DocumentTest  extends \BLW\Type\SerializableTest
{
    const HTML = '<html><head><title>Untitled</title></head><body><h1>Heading</h1><p>Paragraph</p></body></html>';

    /**
     * @var \BLW\Model\DOM\Document
     */
    protected $Document = NULL;

    protected function setUp()
    {
        $this->Document =  new Document('1.0', 'utf-8', 'DOMElement');

        $this->Document->loadHTML(self::HTML);

        $this->Serializable = $this->Document;
        $this->Serializer   = new \BLW\Model\Serializer\Mock;
    }

    protected function tearDown()
    {
        $this->Document     = NULL;
        $this->Serializer   = NULL;
        $this->Serializable = NULL;
    }

    /**
     * @covers ::filterXPath
     */
    public function test_filterXPath()
    {
        $Container = $this->Document->filterXPath('/html/body/*');

        $this->assertCount(2, $Container, 'IDOMDocument::filterXPath() Returned an invalid value');
        $this->assertEquals('<h1>Heading</h1>', $Container[0]->C14N(), 'IDOMDocument::filterXPath() returned invalid element list');
    }

    /**
     * @covers ::filter
     */
    public function test_invoke()
    {
        $Container = $this->Document->filter('body *');

        $this->assertCount(2, $Container, 'IDOMDocument::filterXPath() Returned an invalid value');
        $this->assertEquals('<h1>Heading</h1>', $Container[0]->C14N(), 'IDOMDocument::filterXPath() returned invalid element list');
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertContains(self::HTML, @strval($this->Document), '(string) IDOMDOcument returned an invalid value');
    }

    /**
     * @covers ::getParent
     */
    public function test_getParent()
    {
        $Expected = $this->getMockForAbstractClass('\\BLW\\Type\\IObject');
        $Property = new ReflectionProperty($this->Document, '_Parent');

        $Property->setAccessible(true);
        $Property->setValue($this->Document, $Expected);

        $this->assertSame($Expected, $this->Document->getParent(), 'IDocument::getParent() should equal $_Parent.');
    }

    /**
     * @covers ::setParent
     */
    public function test_setParent()
    {
        $Expected = $this->getMockForAbstractClass('\\BLW\\Type\\IObject');

        // Valid arguments
        $this->assertEquals(IDataMapper::UPDATED, $this->Document->setParent($Expected), 'IDocument::setParent() did not return IDataMapper::UPDATED');
        $this->assertEquals(IDataMapper::ONESHOT, $this->Document->setParent($Expected), 'IDocument::setParent() should return IDataMapper::ONESHOT');

        $this->assertSame($Expected, $this->Document->getParent(), 'IDocument::setParent() Failed to update $_Parent');

        // Invalid arguments
        $this->assertEquals(IDataMapper::INVALID, $this->Document->setParent($this->Document), 'IDocument::setParent() should return IDataMapper::INVALID');
        $this->assertEquals(IDataMapper::INVALID, $this->Document->setParent(null), 'IDocument::setParent() should return IDataMapper::ONESHOT');
   }

   /**
    * @depends test_setParent
    * @covers ::clearParent
    */
    public function test_clearParent()
    {
        $Parent = $this->getMockForAbstractClass('\\BLW\\Type\\IObject');

        $this->assertEquals(IDataMapper::UPDATED, $this->Document->clearParent($Parent), 'clearParent did not return IDataMapper::UPDATED');
        $this->assertEquals(IDataMapper::UPDATED, $this->Document->setParent($Parent), 'setParent did not return IDataMapper::UPDATED');
        $this->assertEquals(IDataMapper::UPDATED, $this->Document->clearParent($Parent), 'clearParent did not return IDataMapper::UPDATED');
        $this->assertNull($this->Document->getParent(), 'getParent() should return NULL.');
        $this->assertEquals(IDataMapper::UPDATED, $this->Document->setParent($Parent), 'setParent did not return IDataMapper::UPDATED');
   }

   /**
    * @covers ::getID
    */
    public function test_getID()
    {
        $this->assertNotEmpty($this->Document->getID(), 'IDocument::getID() Returned an invalid value');
        $this->assertInternalType('string', $this->Document->getID(), 'IDocument::getID() returned an invalid value');
   }
}
