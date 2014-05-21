<?php
/**
 * SubscriberContainerTest.php | May 15, 2014
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
namespace BLW\Model\Mediator;

use BLW\Model\Mediator\Symfony as Mediator;


/**
 * Tests BLW SubscriberContainer Class
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mediator\SubscriberContainer
 */
class SubscriberContainerTest  extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\IMediator
     */
    protected $Mediator = NULL;

    /**
     * @var \BLW\Model\Mediator\SubscriberContainer
     */
    protected $Container = NULL;

    protected function setUp()
    {
        $this->Mediator  = new Mediator;
        $this->Container = new SubscriberContainer($this->Mediator);
    }

    protected function tearDown()
    {
        $this->Container = NULL;
        $this->Mediator  = NULL;
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        $this->assertAttributeEquals(array('\\BLW\\Type\\IEventSubscriber'), '_Types', $this->Container, 'SubscriberContainer::__construct() Failed to set $_Type');
        $this->assertAttributeSame($this->Mediator, '_Mediator', $this->Container, 'SubscriberContainer::__construct() Failed to set $_Mediator');

        # Invalid arguments
    }

    /**
     * @covers ::offsetSet
     */
    public function test_offsetSet()
    {
        $Subscriber = new MockEventSubscriber2;

        $this->Container[0] = $Subscriber;
        $this->Container[]  = $Subscriber;

        $this->assertCount(2, $this->Container, 'SubscriberContainer::offsetSet() Failed to accept subscribers');

        # Invalid arguments
        try {
            $this->Container[] = null;
            $this->fail('Failed to generate exception with invalid value');
        } catch (\UnexpectedValueException $e) {}
    }

    /**
     * @covers ::append
     */
    public function test_append()
    {
        $Subscriber = new MockEventSubscriber2;

        $this->Container->append($Subscriber);
        $this->Container->append($Subscriber);

        $this->assertCount(2, $this->Container, 'SubscriberContainer::offsetSet() Failed to accept subscribers');

        # Invalid arguments
        try {
            $this->Container->append(null);
            $this->fail('Failed to generate exception with invalid value');
        } catch (\UnexpectedValueException $e) {}
    }

    /**
     * @covers ::offsetUnset
     */
    public function test_offsetUnset()
    {
        $Subscriber = new MockEventSubscriber2;

        $this->Container->append($Subscriber);
        $this->Container->append($Subscriber);

        $this->assertCount(2, $this->Container, 'SubscriberContainer::offsetSet() Failed to accept subscribers');

        unset($this->Container[0]);

        $this->assertCount(1, $this->Container, 'SubscriberContainer::offsetSet() Failed to accept subscribers');

        unset($this->Container[1]);

        $this->assertFalse($this->Mediator->isRegistered('pre.foo'), 'SubscriberContainer::offsetUnset() Failed to unsubscribe events');

        # Invalid arguments
        unset($this->Container[100]);
    }
}

class MockEventSubscriber2 implements \BLW\Type\IEventSubscriber
{
    public static function getSubscribedEvents()
    {
        return array('pre.foo' => 'preFoo', 'post.foo' => 'postFoo');
    }
}
