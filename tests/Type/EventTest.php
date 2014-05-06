<?php
/**
 * EventTest.php | Mar 07, 2014
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

use ReflectionProperty;


/**
 * Tests BLW Library Adaptor type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\IEvent
 */
class EventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\IEvent
     */
    protected $Event = NULL;

    /**
     * @var \ReflectionProperty
     */
    protected $Subject = NULL;

    protected function setUp()
    {
        $this->Event   = $this->getMockForAbstractClass('\\BLW\\Type\\AEvent');
        $this->Subject = new ReflectionProperty($this->Event, '_Subject');
        $this->Context = new ReflectionProperty($this->Event, '_Context');

        $this->Subject->setAccessible(true);
        $this->Context->setAccessible(true);
    }

    protected function tearDown()
    {
        $this->Event   = NULL;
        $this->Subject = NULL;
        $this->Context = NULL;
    }

    /**
     * @covers ::isPropagationStopped
     */
    public function test_isPropagationStopped()
    {
        $this->assertFalse($this->Event->isPropagationStopped(), 'IEvent::isPropagationStopped() should be false');
    }

    /**
     * @depends test_isPropagationStopped
     * @covers ::stopPropagation
     */
    public function test_stopPropagation()
    {
        $this->Event->stopPropagation();

        $this->assertTrue($this->Event->isPropagationStopped(), 'IEvent::isPropagationStopped() should be true');
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertRegExp('!\\x5bIEvent\\x3a.+\\x5d!', @strval($this->Event), '(string) IEvent returned an invalid string');
    }

   /**
    * @covers ::__get
    */
    public function test_get()
    {
        # Test
        $Subject = new \DOMElement('span', 'test');
        $this->Subject->setValue($this->Event, $Subject);
        $this->Context->setValue($this->Event, array('foo' => 'test'));

        # Subject
        $this->assertEquals($Subject, $this->Event->Subject, 'IEvent::__get(Subject) returned an invalid value');

        # Context
        $this->assertEquals('test', $this->Event->foo, 'IEvent::__get(foo) returned an invalid value');

        # Undefiened
        try {
            $this->Event->undefined;
            $this->fail('Failed to generate notice on undefined property');
        }

        catch(\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Undefined property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }
   }

   /**
    * @covers ::__isset
    */
    public function test_isset()
    {
        # Test
        $Subject = new \DOMElement('span', 'test');
        $this->Context->setValue($this->Event, array('foo' => 'test'));

        # Subject
        $this->assertFalse(isset($this->Event->Subject), 'IEvent::$Subject should not exist');

        $this->Subject->setValue($this->Event, $Subject);

        $this->assertTrue(isset($this->Event->Subject), 'IEvent::$Subject should exist');

        # Context
        $this->assertTrue(isset($this->Event->foo), 'IEvent::$foo should exist');

        # Undefiened
        $this->assertFalse(isset($this->Event->undefined), 'IEvent::$undefined should not exist');
   }


   /**
    * @covers ::__set
    */
    public function test_set()
    {
        # Test
        $Subject = new \DOMElement('span', 'test');
        $this->Context->setValue($this->Event, array('foo' => 'test'));

        # Subject
        try {
            $this->Event->Subject = NULL;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        #Context
        $this->Event->foo2 = 'test2';
        $this->Event->foo3 = NULL;

        $this->assertEquals('test2', $this->Event->foo2, 'IEvent::__get(foo2) returned an invalid value');
        $this->assertEquals(NULL, $this->Event->foo3, 'IEvent::__get(foo3) returned an invalid value');
    }

    /**
     * @depends test_isset
     * @covers ::__unset()
     */
    public function test_unset()
    {
        # Test
        $Subject = new \DOMElement('span', 'test');
        $this->Context->setValue($this->Event, array('foo' => 'test'));

        # Subject
        try {
            unset($this->Event->Subject);
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        # Context
        unset($this->Event->foo);
        $this->assertFalse(isset($this->Event->foo), 'IEvent::$foo should not exist');

        # Undefined
        unset($this->Event->undefined);
    }
}