<?php
/**
 * MediatableTest.php | Feb 14, 2014
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
use BLW\Type\IEvent;
use BLW\Model\InvalidArgumentException;
use PHPUnit_Framework_Error_Notice;


/**
 * Tests BLW Library Adaptor type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\IMediator
 */
class MediatableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\IMediatable
     */
    protected $Mediatable = NULL;

    /**
     * @var \BLW\Type\IMediator
     */
    protected $Mediator   = NULL;

    /**
     * @var \BLW\Type\IEvent
     */
    protected $Event   = NULL;

    /**
     * @var callable[]
     */
    protected $Callbacks = array();


    public function mock_register($EventName, $Callback, $Priority = 0)
    {
        $this->Callbacks[$EventName] = $Callback;
    }

    public function mock_trigger($EventName, IEvent $Event = NULL)
    {
        if (isset($this->Callbacks[$EventName])) {
            call_user_func($this->Callbacks[$EventName], $Event?: $this->Event);
        }
    }

    protected function setUp()
    {
        $this->Callbacks  = array();
        $this->Mediator   = $this->getMockForAbstractClass('\\BLW\\Type\\IMediator');
        $this->Event      = $this->getMockForAbstractClass('\\BLW\\Type\\IEvent');
        $this->Mediatable = $this->getMockForAbstractClass('\\BLW\\Type\\AMediatable');

        $this->Mediator
            ->expects($this->any())
            ->method('register')
            ->will($this->returnCallback(array($this, 'mock_register')))
        ;

        $this->Mediator
            ->expects($this->any())
            ->method('trigger')
            ->will($this->returnCallback(array($this, 'mock_trigger')))
        ;
    }

    protected function tearDown()
    {
        $this->Callbacks  = NULL;
        $this->Mediator   = NULL;
        $this->Event      = NULL;
        $this->Mediatable = NULL;
    }

    /**
     * @covers ::getMediator
     */
    public function test_getMediator()
    {
        $this->assertNull($this->Mediatable->getMediator(), 'getMediator() should initially be NULL.');
    }

    /**
     * @covers ::setMediator
     */
    public function test_setMediator()
    {
        $this->assertEquals(IDataMapper::UPDATED, $this->Mediatable->setMediator($this->Mediator), 'IMediatable::setMediator() did not return IDataMapper::UPDATED');
        $this->assertEquals(IDataMapper::UPDATED, $this->Mediatable->setMediator($this->Mediator), 'IMediatable::setMediator() did not return IDataMapper::UPDATED');
        $this->assertEquals(IDataMapper::INVALID, $this->Mediatable->setMediator(NULL), 'IMediatable::setMediator() did not return IDataMapper::INVALID');

        $this->assertSame($this->Mediator, $this->Mediatable->getMediator(), 'IMediatable::getMediator() did not return set mediator');
   }

   /**
    * @depends test_setMediator
    * @covers ::clearMediator
    */
   public function test_clearMediator()
   {
       $this->assertEquals(IDataMapper::UPDATED, $this->Mediatable->setMediator($this->Mediator), 'IMediatable::setMediator() did not return IDataMapper::UPDATED');
       $this->assertSame($this->Mediator, $this->Mediatable->getMediator(), 'IMediatable::getMediator() did not return set mediator');
       $this->assertEquals(IDataMapper::UPDATED, $this->Mediatable->clearMediator(), 'IMediatable::clearMediator() did not return IDataMapper::UPDATED');
       $this->assertNULL($this->Mediatable->getMediator(), 'IMediatable::getMediator() should return NULL');
   }

   /**
    * @covers ::getMediatorID
    */
    public function test_getMediatorID()
    {
        $this->assertNotEmpty($this->Mediatable->getMediatorID());
   }

   /**
    * @depends test_setMediator
    * @covers ::_on
    */
    public function test_on()
    {
        $EventNames = array(1,1.0,'foo', new \SplFileInfo(__FILE__));
        $Callback   = function(IEvent $Event) {};

        # Test no mediator error.
        try {
            $this->Mediatable->_on('foo', $Callback);
            $this->fail('Failed generating no mediator notice.');
        }

        catch (PHPUnit_Framework_Error_Notice $e) {}

        # Set mediator
        $this->assertEquals(IDataMapper::UPDATED, $this->Mediatable->setMediator($this->Mediator), 'IMediator::setMediator() did not return IDataMapper::UPDATED');

        # Test invalid EventName exception.
        try {
            $this->Mediatable->_on(NULL, $Callback);
            $this->fail('Failed generating invalid EventName exception.');
        }

        catch (InvalidArgumentException $e) {}

        # Test invalid callback exception.
        try {
            $this->Mediatable->_on('foo', NULL);
            $this->fail('Failed generating invalid callback exception.');
        }

        catch (InvalidArgumentException $e) {}

        # Test valid EventNames
        foreach ($EventNames as $EventName) {
            $this->Mediatable->_on($EventName, $Callback);
        }
   }

   /**
    * @depends test_on
    * @covers ::_do
    */
    public function test_do()
    {
        $EventNames = array(1,1.0,'foo', new \SplFileInfo(__FILE__));
        $Called     = false;
        $Callback   = function(IEvent $Event) use (&$Called) {$Called = true;};

        # Test no mediator error.
        try {
            $this->Mediatable->_do('foo', $this->Event);
            $this->fail('Failed generating no mediator notice.');
        }

        catch (PHPUnit_Framework_Error_Notice $e) {}

        # Set mediator
        $this->assertEquals(IDataMapper::UPDATED, $this->Mediatable->setMediator($this->Mediator), 'setMediator did not return IDataMapper::UPDATED');

        # Test invalid EventName exception.
        try {
            $this->Mediatable->_do(NULL, $this->Event);
            $this->fail('Failed generating invalid EventName exception.');
        }

        catch (InvalidArgumentException $e) {}

        # Test valid EventNames
        foreach ($EventNames as $EventName) {
            $this->Mediatable->_do($EventName, $this->Event);
        }

        # Test logic
        $this->assertFalse($Called, '$Called should initially be false.');
        $this->Mediatable->_on('foo', $Callback);
        $this->Mediatable->_do('foo', $this->Event);
        $this->assertTrue($Called, '$Called should be true.');
    }
}