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
namespace BLW\Type;

use ReflectionProperty;


/**
 * Tests BLW Library Adaptor type.
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 *  @coversDefaultClass \BLW\Type\AEvent
 */
class EventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\AEvent
     */
    protected $Event = NULL;

    /**
     * @var \ReflectionProperty
     */
    protected $Subject = NULL;

    protected function setUp()
    {
        $this->Event   = $this->getMockForAbstractClass('\\BLW\\Type\\AEvent');
        $this->Subject = (object) array('foo' => 1, 'bar' => 1);

        $Property = new ReflectionProperty($this->Event, '_Subject');

        $Property->setAccessible(true);
        $Property->setValue($this->Event, $this->Subject);

        $Property = new ReflectionProperty($this->Event, '_Context');

        $Property->setAccessible(true);
        $Property->setValue($this->Event, array('foo' => 'test'));
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
        # Subject
        $this->assertAttributeSame($this->Event->Subject, '_Subject', $this->Event, 'IEvent::$Subject should equal $_Subject');

        # Context
        $this->assertEquals('test', $this->Event->foo, 'IEvent::$foo should equal IEvent::$foo[foo] returned an invalid value');

        # Undefiened
        try {
            $this->Event->undefined;
            $this->fail('Failed to generate notice on undefined property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Undefined property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        $this->assertNull(@$this->Event->undefined, 'IEvent::$undefined should be NULL');
    }

   /**
    * @covers ::__isset
    */
    public function test_isset()
    {
        # Subject
        $this->assertTrue(isset($this->Event->Subject), 'IEvent::$Subject should not exist');

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
        # Subject
        try {
            $this->Event->Subject = NULL;
            $this->fail('Failed to generate notice on readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Event->Subject = NULL;

        #Context
        $this->Event->foo2 = 'test2';

        $this->assertEquals('test2', $this->Event->foo2, 'IEvent::$foo2 failed to update IEvent');

        # Undefined
        $this->Event->undefined = 1;
        $this->assertEquals(1, $this->Event->undefined, 'IEvent::$undefined failed to update IEvent');
    }

    /**
     * @depends test_isset
     * @covers ::__unset()
     */
    public function test_unset()
    {
        # Subject
        try {
            unset($this->Event->Subject);
            $this->fail('Failed to generate notice on readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Event->__unset('Subject');

        # Context
        unset($this->Event->foo);

        $this->assertFalse(isset($this->Event->foo), 'IEvent::$foo should not exist');

        # Undefined
        unset($this->Event->undefined);
    }
}
