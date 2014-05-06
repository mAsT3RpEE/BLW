<?php
/**
 * SymfonyTest.php | Mar 06, 2014
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
Namespace BLW\Tests\Model\Mediator;

use BLW\Type\IMediator;
use BLW\Type\IEventSubscriber;
use BLW\Type\IEvent;
use BLW\Model\Mediator\Symfony as Mediator;

use ReflectionObject;
use PHPUnit_Framework_Error_Notice;
use PHPUnit_Framework_Error;
use BLW\Model\Event\Generic as GenericEvent;

class CallableClass
{
    public function __invoke()
    {
    }
}

class MockEventListener
{
    public $preFooInvoked = false;
    public $postFooInvoked = false;

    /* Callback methods */

    public function preFoo(IEvent $e)
    {
        $this->preFooInvoked = true;
    }

    public function postFoo(IEvent $e)
    {
        $this->postFooInvoked = true;

        $e->stopPropagation();
    }
}

class MockWithDispatcher
{
    public $Event;
    public $Name;
    public $Mediator;

    public function foo(IEvent $e, $Name, $Mediator)
    {
        $this->Event    = $e;
        $this->Name     = $Name;
        $this->Mediator = $Mediator;
    }
}

class MockEventSubscriber implements IEventSubscriber
{
    public static function getSubscribedEvents()
    {
        return array('pre.foo' => 'preFoo', 'post.foo' => 'postFoo');
    }
}

class MockEventSubscriberWithPriorities implements IEventSubscriber
{
    public static function getSubscribedEvents()
    {
        return array('pre.foo' => array('preFoo', 10), 'post.foo' => array('postFoo'));
    }
}

class MockEventSubscriberWithMultipleCallbacks implements IEventSubscriber
{
    public static function getSubscribedEvents()
    {
        return array('pre.foo' => array(
            array('preFoo1'),
            array('preFoo2', 10)
        ));
    }
}

/**
 * Tests BLW Library Mediator type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mediator\Symfony
 */
class Symfony extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\IMediator
     */
    protected $Mediator = NULL;

    /**
     * @var MockEventListener
     */
    protected $Callback = NULL;

    protected function setUp()
    {
        $this->Mediator = new Mediator;
        $this->Listener = new MockEventListener;
    }

    protected function tearDown()
    {
        $this->Mediator = NULL;
        $this->Listener = NULL;
    }

    /**
     * @covers ::__create
     */
    public function test_create()
    {
        $this->assertEquals(array(), $this->Mediator->getCallbacks('test'), 'Action `foo` should have no Callbacks');
        $this->assertFalse($this->Mediator->isRegistered('pre.Foo'));
        $this->assertFalse($this->Mediator->isRegistered('post.Foo'));
    }

    /**
     * @covers ::register
     */
    public function test_register()
    {
        # Register Listener
        $this->assertTrue($this->Mediator->register('pre.foo',  array($this->Listener, 'preFoo')), 'IMediator::register() should return true');
        $this->assertTrue($this->Mediator->register('post.foo', array($this->Listener, 'postFoo')), 'IMediator::register() should return true');
    }

    /**
     * @depends test_register
     * @covers ::isRegistered
     */
    public function test_isRegistered()
    {
        # Register Listener
        $this->assertTrue($this->Mediator->register('pre.foo',  array($this->Listener, 'preFoo')), 'IMediator::register() should return true');
        $this->assertTrue($this->Mediator->register('post.foo', array($this->Listener, 'postFoo')), 'IMediator::register() should return true');

        $this->assertTrue($this->Mediator->isRegistered('pre.foo'), 'IMediator::isRegistered(pre.foo) should return true');
        $this->assertTrue($this->Mediator->isRegistered('post.foo'), 'IMediator::isRegistered(post.foo) should return true');
        $this->assertFalse($this->Mediator->isRegistered('undefined'), 'IMediator::isRegistered(undefined) should return false');
    }

    /**
     * @depends test_isRegistered
     * @covers ::deregister
     */
    public function test_deregister()
    {
        # Register Listener
        $this->assertTrue($this->Mediator->register('pre.foo',  array($this->Listener, 'preFoo')), 'IMediator::register() should return true');
        $this->assertTrue($this->Mediator->register('post.foo', array($this->Listener, 'postFoo')), 'IMediator::register() should return true');

        # Test deregister
        $this->Mediator->deregister('pre.foo', array($this->Listener, 'preFoo'));

        $this->assertFalse($this->Mediator->isRegistered('pre.foo'), 'IMediator::isRegistered() should now return false');

        # Test deregister undefined
        $this->Mediator->deregister('undefined', array($this->Listener, 'preFoo'));
    }

    /**
     * @depends test_register
     * @covers ::getCallbacks
     */
    public function test_getCallbacks()
    {
        # Register Listener
        $this->assertTrue($this->Mediator->register('pre.foo',  array($this->Listener, 'preFoo')), 'IMediator::register() should return true');
        $this->assertTrue($this->Mediator->register('post.foo', array($this->Listener, 'postFoo')), 'IMediator::register() should return true');

        # Test if callbacks are returned by getCallbacks
        $Test = $this->Mediator->getCallbacks('pre.foo');
        $this->assertCount(1, $Test, 'IMediator::getCallbacks() should return an array with 1 item');
        $this->assertTrue(is_callable($Test[0]), 'IMediator::getCallbacks() should return an array of callables');

        $Test = $this->Mediator->getCallbacks('post.foo');
        $this->assertCount(1, $Test, 'IMediator::getCallbacks() should return an array with 1 item');
        $this->assertTrue(is_callable($Test[0]), 'IMediator::getCallbacks() should return an array of callables');

        # Test unregistered callbacks
        $Test = $this->Mediator->getCallbacks('undefined');
        $this->assertCount(0, $Test, 'IMediator::getCallbacks() should return an empty array');

        # Test if priotity sorting
        $Listener1 = new MockEventListener;
        $Listener2 = new MockEventListener;
        $Listener3 = new MockEventListener;
        $Listener4 = new MockEventListener;
        $Listener5 = new MockEventListener;
        $Listener6 = new MockEventListener;

        $Listener1->Name = '1';
        $Listener2->Name = '2';
        $Listener3->Name = '3';
        $Listener4->Name = '4';
        $Listener5->Name = '5';
        $Listener6->Name = '6';

        $this->Mediator->register('ordered1.foo', array($Listener1, 'preFoo'), -10);
        $this->Mediator->register('ordered1.foo', array($Listener2, 'preFoo'), 10);
        $this->Mediator->register('ordered1.foo', array($Listener3, 'preFoo'));

        $expected = array(
            array($Listener2, 'preFoo'),
            array($Listener3, 'preFoo'),
            array($Listener1, 'preFoo'),
        );

        $this->assertSame($expected, $this->Mediator->getCallbacks('ordered1.foo'), 'IMediator::getCallbacks() did not order callbacks by priority');

        $this->Mediator->register('ordered2.foo', array($Listener1, 'preFoo'), -10);
        $this->Mediator->register('ordered2.foo', array($Listener2, 'preFoo'));
        $this->Mediator->register('ordered2.foo', array($Listener3, 'preFoo'), 10);
        $this->Mediator->register('ordered2.foo', array($Listener4, 'preFoo'), -10);
        $this->Mediator->register('ordered2.foo', array($Listener5, 'preFoo'));
        $this->Mediator->register('ordered2.foo', array($Listener6, 'preFoo'), 10);

        $expected = array(
             array($Listener3, 'preFoo')
            ,array($Listener6, 'preFoo')
            ,array($Listener2, 'preFoo')
            ,array($Listener5, 'preFoo')
            ,array($Listener1, 'preFoo')
            ,array($Listener4, 'preFoo')
        );

        $this->assertSame($expected, $this->Mediator->getCallbacks('ordered2.foo'), 'IMediator::getCallbacks() did not order callbacks by priority');
    }

    /**
     * @depends test_getCallbacks
     * @covers ::trigger
     */
    public function test_trigger()
    {
        # Register Listener
        $this->assertTrue($this->Mediator->register('pre.foo',  array($this->Listener, 'preFoo')), 'IMediator::register() should return true');
        $this->assertTrue($this->Mediator->register('post.foo', array($this->Listener, 'postFoo')), 'IMediator::register() should return true');

        # Test trigger Callback
        $this->Mediator->trigger('pre.foo');

        $this->assertTrue($this->Listener->preFooInvoked, 'IDataMapper::trigger() did not call registered callback');
        $this->assertFalse($this->Listener->postFooInvoked, 'IDataMapper::trigger() called an unregistered callback');

        # Test trigger closure
        $invoked  = 0;
        $Callback = function () use (&$invoked) {$invoked++;};

        $this->assertTrue($this->Mediator->register('pre.foo', $Callback), 'IMediator::register() should return true');
        $this->assertTrue($this->Mediator->register('post.foo', $Callback), 'IMediator::register() should return true');

        $this->Mediator->trigger('pre.foo');
        $this->assertEquals(1, $invoked, 'IMediator::trigger() did not call registered callback');
        $this->Mediator->trigger('pre.foo');
        $this->assertEquals(2, $invoked, 'IMediator::trigger() did not call registered callback');

        # Test stop event propagation

        $OtherListener = new MockEventListener;

        ########################################################################
        # postFoo() stops the propagation, so only one Callback should
        # be executed
        ########################################################################

        # Manually set priority to enforce $this->Listener to be called first
        $this->Mediator->register('post.foo', array($OtherListener, 'preFoo'));

        $this->Mediator->trigger('post.foo');

        $this->assertTrue($this->Listener->postFooInvoked, 'IDataMapper::tigger() did not call registered callback');
        $this->assertFalse($OtherListener->postFooInvoked, 'IDataMapper::trigger() ');
    }

    /**
     * @depends test_isRegistered
     * @covers ::addSubscriber
     */
    public function testAddSubscriber()
    {
        # Test normal subscriber
        $EventSubscriber = new MockEventSubscriber;

        $this->Mediator->addSubscriber($EventSubscriber);

        $this->assertTrue($this->Mediator->isRegistered('pre.foo'), 'IMediator::addSubscriber() did not register subscriber action');
        $this->assertTrue($this->Mediator->isRegistered('post.foo'), 'IMediator::addSubscriber() did not register subscriber action');

        # Test subscriber with  priorities
        $EventSubscriber = new MockEventSubscriberWithPriorities();

        $this->Mediator->addSubscriber($EventSubscriber);

        $Callbacks = $this->Mediator->getCallbacks('pre.foo');

        $this->assertCount(2, $Callbacks, 'IMediator::addSubscriber() did not register subscriber action');
        $this->assertSame($EventSubscriber, $Callbacks[0][0],'IMediator::addSubscriber() did not register subscriber action');
        $this->assertEquals('preFoo', $Callbacks[0][1],'IMediator::addSubscriber() did not register subscriber action');

        # Test subscriber with multiple callbacks
        $EventSubscriber = new MockEventSubscriberWithMultipleCallbacks();

        $this->Mediator->addSubscriber($EventSubscriber);

        $Callbacks = $this->Mediator->getCallbacks('pre.foo');

        $this->assertTrue($this->Mediator->isRegistered('pre.foo'));
        $this->assertCount(4, $Callbacks, 'IMediator::addSubscriber() did not register subscriber action');
        $this->assertEquals('preFoo2', $Callbacks[1][1],'IMediator::addSubscriber() did not register subscriber action');
        $this->assertEquals('preFoo1', $Callbacks[3][1],'IMediator::addSubscriber() did not register subscriber action');
    }

    /**
     * @depends test_isRegistered
     * @covers ::remSubscriber
     */
    public function test_remSubscriber()
    {
        # Test normal subscriber
        $EventSubscriber = new MockEventSubscriber;

        $this->Mediator->addSubscriber($EventSubscriber);
        $this->Mediator->remSubscriber($EventSubscriber);

        $this->assertFalse($this->Mediator->isRegistered('pre.foo'), 'Event name pre.foo should have no subscribers');
        $this->assertFalse($this->Mediator->isRegistered('post.foo'), 'Event name post.foo should have no subscribers');

        # Test subscriber with  priorities
        $EventSubscriber = new MockEventSubscriberWithPriorities;

        $this->Mediator->addSubscriber($EventSubscriber);
        $this->Mediator->remSubscriber($EventSubscriber);

        $this->assertFalse($this->Mediator->isRegistered('pre.foo'), 'Event name pre.foo should have no subscribers');

        # Test subscriber with multiple callbacks
        $EventSubscriber = new MockEventSubscriberWithMultipleCallbacks;

        $this->Mediator->addSubscriber($EventSubscriber);
        $this->Mediator->remSubscriber($EventSubscriber);

        $this->assertFalse($this->Mediator->isRegistered('pre.foo'), 'Event name pre.foo should have no subscribers');
    }

    /**
     * @depends test_trigger
     * @covers ::_Dispatch
     */
    public function test_Dispatch()
    {
        # Test call parameters
        $Test  = new MockWithDispatcher;
        $Event = new GenericEvent;

        $this->Mediator->register('foo', array($Test, 'foo'));
        $this->Mediator->trigger('foo', $Event);

        $this->assertSame($Event, $Test->Event, 'IMediator::trigger() did not pass event object to registered callback');
        $this->assertSame('foo', $Test->Name, 'IMediator::trigger() did not pass event name to registered callback');
        $this->assertSame($this->Mediator, $Test->Mediator, 'IMediator::trigger() did not pass event dispatcher to registered callback');
    }

    /**
     * @see https://bugs.php.net/bug.php?id=62976
     *
     * This bug affects:
     *  - The PHP 5.3 branch for versions < 5.3.18
     *  - The PHP 5.4 branch for versions < 5.4.8
     *  - The PHP 5.5 branch is not affected
     */
    public function test_Bug62976()
    {
        $Mediator = new Mediator;

        $Mediator->register('bug.62976', new CallableClass());
        $Mediator->deregister('bug.62976', function () {});

        $this->assertTrue($Mediator->isRegistered('bug.62976'), 'Bug 62976 seems to be affecting mediator');
    }
}
