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
namespace BLW\Tests\Model;

use ReflectionProperty;
use ReflectionMethod;

use BLW\Model\DOM\Document;


/**
 * Test for base html Object.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\DOM\Document
 */
class DOMDocumentTest  extends \PHPUnit_Framework_TestCase
{
    const HTML = '<html><head><title>Untitled</title></head><body><h1>Heading</h1><p>Paragraph</p></body></html>';

    /**
     * @var \BLW\Model\DOMDocument
     */
    protected $Document = NULL;

    protected function setUp()
    {
        $this->Document =  new Document('1.0', 'utf-8', 'DOMElement');

        $this->Document->loadHTML(self::HTML);
    }

    protected function tearDown()
    {
        $this->Document = NULL;
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
     * @coversNothing
     */
    public function test_serialize()
    {
        $Serialized = unserialize(serialize($this->Document));

        $this->assertEquals($this->Document, $Serialized, 'unserialize(serialize(IDOMDocument)) should equal IDOMDocument');
    }
}