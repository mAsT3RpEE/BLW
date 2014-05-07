<?php
/**
 * ComponentMapableTest.php | Feb 12, 2014
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

use BLW\Type\IDataMapper;
use DOMDocument;
use ReflectionObject;
use PHPUnit_Framework_Error_Notice;
use BadMethodCallException;

/**
 * Tests BLW Library Adaptor type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\AComponentMapable
 */
class ComponentMapableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\IComponentMapable
     */
    protected $ComponentMapable = NULL;

    /**
     * @var \DOMElement
     */
    protected $Component   = NULL;

    protected function setUp()
    {
        $Document               = new DOMDocument("1.0");
        $this->Component        = $Document->createElement('span', 'text');
        $this->ComponentMapable = $this->getMockForAbstractClass('\\BLW\\Type\\AComponentMapable');

        $this->Component->setAttribute('foo', 'checked');

        $Object   = new ReflectionObject($this->ComponentMapable);
        $Property = $Object->getProperty('_Component');

        $Property->setAccessible(true);
        $Property->setValue($this->ComponentMapable, $this->Component);
        $Property->setAccessible(false);

        unset($Property, $Object, $Document);
    }

    protected function tearDown()
    {
        $this->Component        = NULL;
        $this->ComponentMapable = NULL;
    }

    /**
     * @covers ::__call
     */
    public function test__call()
    {
        # Test Valid calls
        $this->assertEquals($this->Component->hasAttribute('foo'), $this->ComponentMapable->hasAttribute('foo'), 'ComponentMapable::hasAttribute() returned invalid value.');
        $this->assertEquals($this->Component->getAttribute('foo'), $this->ComponentMapable->getAttribute('foo'), 'ComponentMapable::getAttribute() returned invalid value.');

        # Test Invalid call
        try {
            $this->ComponentMapable->foo();
            $this->fail('Unable to raise exception on undefined function');
        }

        catch (BadMethodCallException $e) {}
    }

    /**
     * @covers ::__get
     */
    public function test__get()
    {
        # Test dynamic property
        $this->assertEquals('span', $this->ComponentMapable->tagName, 'ComponentMapable::$tagName should equal `span`.');

        # Test undefined property
        try { $this->ComponentMapable->bar; }

        catch (PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Undefined property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }
   }

   /**
    * @covers ::__isset
    */
   public function test__isset()
   {
       # Test dynamic property
       $this->assertTrue(isset($this->ComponentMapable->tagName), 'ComponentMapable::$tagName should exist.');

        # Test undefined property
       $this->assertFalse(isset($this->ComponentMapable->bar), 'ComponentMapable::$bar shouldn\'t exist.');
  }

    /**
     * @covers ::__set
     */
    public function test__set()
    {
        # Test dynamic property
        $this->ComponentMapable->nodeValue = 1;
        $this->assertEquals(1, $this->ComponentMapable->nodeValue, 'ComponentMapable::$nodeValue should equal 1.');
    }
}