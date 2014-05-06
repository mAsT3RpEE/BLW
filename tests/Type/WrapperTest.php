<?php
/**
 * WrapperTest.php | Feb 12, 2014
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
use ReflectionMethod;
use ReflectionProperty;
use PHPUnit_Framework_Error_Notice;
use BadMethodCallException;

/**
 * Tests BLW Library Adaptor type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\IWrapper
 */
class WrapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\IWrapper
     */
    protected $Wrapper = NULL;

    /**
     * @var \DOMElement
     */
    protected $Component = NULL;

    protected function setUp()
    {
        $Document           = new DOMDocument("1.0");
        $this->Component    = $Document->createElement('span', 'text');
        $this->Wrapper      = $this->getMockForAbstractClass('\\BLW\\Type\\AWrapper', array($this->Component));

        $this->Component->setAttribute('foo', 'checked');

        $Status = new ReflectionProperty($this->Wrapper, '_Status');
        $Status->setAccessible(true);
        $Status->setValue($this->Wrapper, -1);

        unset($Document);
    }

    protected function tearDown()
    {
        $this->Component   = NULL;
        $this->Wrapper     = NULL;
    }


    /**
     * @covers ::getInstance
     */
    public function test__getInstance()
    {
        $Copy = $this->Wrapper->getInstance($this->Component);

        $Status = new ReflectionProperty($Copy, '_Status');
        $Status->setAccessible(true);
        $Status->setValue($Copy, -1);

        $this->assertEquals($Copy, $this->Wrapper, 'IWrapper::getInstance() returned invalid value');
    }

    /**
     * @covers ::__call
     */
    public function test__call()
    {
        # Test Valid calls
        $this->assertEquals($this->Component->hasAttribute('foo'), $this->Wrapper->hasAttribute('foo'), 'Wrapper::hasAttribute() returned invalid value');
        $this->assertEquals($this->Component->getAttribute('foo'), $this->Wrapper->getAttribute('foo'), 'Wrapper::getAttribute() returned invalid value');

        # Test Invalid call
        try {
            $this->Wrapper->foo();
            $this->fail('Unable to raise exception on undefined function');
        }

        catch (BadMethodCallException $e) {}
    }

    /**
     * @covers ::__get
     */
    public function test__get()
    {
	    # Make property readable / writable
	    $Status = new ReflectionProperty($this->Wrapper, '_Status');
	    $Status->setAccessible(true);

	    # Status
        $this->assertSame($this->Wrapper->Status, $Status->getValue($this->Wrapper), 'IWrapper::$Status should equal IWrapper::_Status');

	    # Serializer
	    $this->assertSame($this->Wrapper->Serializer, $this->Wrapper->getSerializer(), 'IWrapper::$Serializer should equal IWrapper::getSerializer()');

	    # Parent
        $this->assertNULL($this->Wrapper->Parent, 'IWrapper::$Parent should initially be NULL');

        # ID
        $this->assertSame($this->Wrapper->ID, $this->Wrapper->getID(), 'IWrapper::$ID should equal IWrapper::getID()');

        # Test dynamic property
        $this->assertEquals('span', $this->Wrapper->tagName, 'Wrapper::$tagName should equal `span`');

        # Test undefined property
        try { $this->Wrapper->bar; }

        catch (PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Undefined property', $e->getMessage(), 'Wrapper::$bar is undefined and should raise a notice');
        }
   }

   /**
    * @covers ::__isset
    */
   public function test__isset()
   {
        # Status
       $this->assertTrue(isset($this->Wrapper->Status), 'IWrapper::$Status should exist');

	    # Serializer
	    $this->assertTrue(isset($this->Wrapper->Serializer), 'IWrapper::$Serializer should exist');

	    # Parent
        $this->assertFalse(isset($this->Wrapper->Parent), 'IWrapper::$Parent should not exist');

	    # ID
        $this->assertEquals(isset($this->Wrapper->ID), $this->Wrapper->getID() !== NULL, 'IWrapper::$ID should exist');

       # Test dynamic property
       $this->assertTrue(isset($this->Wrapper->tagName), 'Wrapper::$tagName should exist');

        # Test undefined property
       $this->assertFalse(isset($this->Wrapper->bar), 'Wrapper::$bar shouldn\'t exist');
  }

    /**
     * @covers ::__set
     */
    public function test__set()
    {
        # Status
        try {
            $this->Wrapper->Status = 0;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        # Serializer
        try {
            $this->Wrapper->Serializer = 0;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        # Parent
        $this->Wrapper->Parent = $this->getMockForAbstractClass('\\BLW\\Type\\IObject');
        $this->assertSame($this->Wrapper->Parent, $this->Wrapper->getParent(), 'IWrapper::$Parent should equal IWrapper::getParent');
        $this->assertTrue(isset($this->Wrapper->Parent), 'IWrapper::$Parent should exist');

	    # ID
        try {
            $this->Wrapper->ID = 'foo';
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        # Test dynamic property
        $this->Wrapper->nodeValue = 1;
        $this->assertEquals(1, $this->Wrapper->nodeValue, 'Wrapper::$nodeValue should equal 1.');
    }

    /**
     * @covers ::__toString
     */
    public function test__toString()
    {
        $this->assertRegExp("/\\x5b[\\x30-\\x39\\x41-\\x5a\\x61-\\x7a\\x5f]+\\x3aDOMElement\\x5d/", @strval($this->Wrapper), 'strval(IWrapper) should equal `[IWrapper::DOMNode]`');
    }
}