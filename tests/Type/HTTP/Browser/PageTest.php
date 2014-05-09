<?php
/**
 * PageTest.php | Apr 13, 2014
 *
 * @filesource
 * @license MIT
 * @copyright Copyright (c) 2013-2018, mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * @package BLW\HTTP
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type\HTTP\Browser;

use DateTime;
use ReflectionProperty;
use ReflectionMethod;

use BLW\Type\IDataMapper;

use BLW\Model\GenericURI;
use BLW\Model\DOM\Document;

use BLW\Model\Mediator\Symfony as Mediator;

use BLW\Model\MIME\Head\RFC2616 as RFC2616Head;

use BLW\Model\HTTP\Request\Generic as Request;
use BLW\Model\HTTP\Response\Generic as Response;


/**
 * Test for HTTP Browser page base class
 * @package BLW\HTTP
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\HTTP\Browser\APage
 */
class PageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var mixed $Component
     */
    protected $Component = NULL;

    /**
     * @var \BLW\Type\HTTP\Browser\APage
     */
    protected $Page = NULL;

    protected function setUp()
    {
        $this->Component = new Document;
        $this->Page      = $this->getMockForAbstractClass('\BLW\Type\HTTP\Browser\APage', array($this->Component));

        $this->Page->setMediator(new Mediator);

        $Property       = new ReflectionProperty($this->Page, '_RequestHead');

        $Property->setAccessible(true);
        $Property->setValue($this->Page, new RFC2616Head);

        $Property       = new ReflectionProperty($this->Page, '_ResponseHead');

        $Property->setAccessible(true);
        $Property->setValue($this->Page, new RFC2616Head);

        $Property       = new ReflectionProperty($this->Page, '_Base');

        $Property->setAccessible(true);
        $Property->setValue($this->Page, new GenericURI('http://example.com'));

        unset($Property);
    }

    protected function tearDown()
    {
        $this->Page = NULL;
    }

    public function generateInvalidDates()
    {
        return array(
        	 array(NULL)
            ,array('foo')
            ,array(array())
            ,array(new \stdClass)
        );
    }

    /**
     * @covers ::getID
     */
    public function test_getID()
    {
        $this->assertEquals('http://example.com', $this->Page->getID(), 'IPage::getID() Returned an invalid value');
    }

    /**
     * @covers ::setCreated
     */
    public function test_setCreated()
    {
        $Expected = new DateTime;

        # Valid arguments
        $this->assertSame(IDataMapper::UPDATED, $this->Page->setCreated($Expected), 'IPage::setCreated() Should return IDataMapper::UPDATED');
        $this->assertAttributeSame($Expected, '_Created', $this->Page, 'IPage::setCreated() Failed to update $_Created');

        # Invalid arguments
        for ($a=$this->generateInvalidDates(); list($k, list($Date)) = each($a);) {

            $this->assertSame(IDataMapper::INVALID, $this->Page->setCreated($Date), 'IPage::setCreated() Should return IDataMapper::INVALID');
            $this->assertAttributeNotSame($Date, '_Created', $this->Page, 'IPage::setCreated() Updated $_Created');
        }
    }

    /**
     * @depends test_setCreated
     * @covers ::getCreated
     */
    public function test_getCreated()
    {
        $Expected = new DateTime;

        $this->assertNull($this->Page->getCreated(), 'IPage::getCreated() Should return NULL');
        $this->assertSame(IDataMapper::UPDATED, $this->Page->setCreated($Expected), 'IPage::setCreated() Should return IDataMapper::UPDATED');
        $this->assertSame($Expected, $this->Page->getCreated(), 'IPage::getCreated() Returned an invalid value');
    }

    /**
     * @covers ::setModified
     */
    public function test_setModified()
    {
        $Expected = new DateTime;

        # Valid arguments
        $this->assertSame(IDataMapper::UPDATED, $this->Page->setModified($Expected), 'IPage::setModified() Should return IDataMapper::UPDATED');
        $this->assertAttributeSame($Expected, '_Modified', $this->Page, 'IPage::setModified() Failed to update $_Modified');

        # Invalid arguments
        for ($a=$this->generateInvalidDates(); list($k, list($Date)) = each($a);) {

            $this->assertSame(IDataMapper::INVALID, $this->Page->setModified($Date), 'IPage::setModified() Should return IDataMapper::INVALID');
            $this->assertAttributeNotSame($Date, '_Modified', $this->Page, 'IPage::setModified() Updated $_Modified');
        }
    }

    /**
     * @depends test_setModified
     * @covers ::getModified
     */
    public function test_getModified()
    {
        $Expected = new DateTime;

        $this->assertNull($this->Page->getModified(), 'IPage::getModified() Should return NULL');
        $this->assertSame(IDataMapper::UPDATED, $this->Page->setModified($Expected), 'IPage::setModified() Should return IDataMapper::UPDATED');
        $this->assertSame($Expected, $this->Page->getModified(), 'IPage::getModified() Returned an invalid value');
    }

    /**
     * @covers ::__call
     */
    public function test_call()
    {
        $Called = 0;

        $this->Page->_on('test', function() use(&$Called) {$Called++;});

        # Component function
        $this->assertTrue($this->Page->loadHTML('<div>foo</div>'), 'IPage::validate() Should return true');

        # Undefined function
        $this->Page->test();
        $this->Page->test();

        $this->assertSame(2, $Called, 'IPage::test() Failed to generate event.');

        # No mediator
        $this->Page->clearMediator();

        $this->Page->test();

        $this->assertSame(2, $Called, 'IPage::test() Generated event.');

        # Variable function
        $this->Page->foo = function() use(&$Called) {$Called++;};

        $this->Page->foo();

        $this->assertSame(3, $Called, 'IPage::test() Failed to generate event.');
    }

    /**
     * @covers ::__get
     */
    public function test_get()
    {
        # Status
        $this->assertAttributeSame($this->Page->Status, '_Status', $this->Page, 'IPage::$Status should equal IPage::_Status');

	    # Serializer
        $this->assertSame($this->Page->getSerializer(), $this->Page->Serializer, 'IPage::$Serializer should equal IPage::getSerializer()');

	    # Parent
        $this->assertNULL($this->Page->Parent, 'IPage::$Parent should initially be NULL');

        # ID
        $this->assertSame($this->Page->getID(), $this->Page->ID, 'IPage::$ID should equal IPage::getID()');

        # Component
        $this->assertSame($this->Component, $this->Page->Component, 'IPage::$Component should equal $_Component');

        # Mediator
        $this->assertSame($this->Page->getMediator(), $this->Page->Mediator, 'IPage::$Mediator should equel IPage::getMediator()');

        # MediatorID
        $this->assertSame($this->Page->getMediatorID(), $this->Page->MediatorID, 'IPage::$MediatorID should equal IPage::getMediatorID()');

        # RequestHead
        $this->assertInstanceOf('\\BLW\\Type\\MIME\\IHead', $this->Page->RequestHead, 'IPage::$Request should be an instance of IRequest');

        # ResponseHead
        $this->assertInstanceOf('\\BLW\\Type\\MIME\\IHead', $this->Page->ResponseHead, 'IPage::$Response should be an instance of IResponse');

        # Base
        $this->assertInstanceOf('\\BLW\\Type\\IURI', $this->Page->Base, 'IPage::$Base should be an instance of IURI');

        # Created
        $this->assertSame($this->Page->getCreated(), $this->Page->Created, 'IPage::$Created should be equal to IPage::getCreated()');

        # Modified
        $this->assertSame($this->Page->getModified(), $this->Page->Modified, 'IPage::$Modified should be equal to IPage::getModified()');

        # Document
        $this->assertSame($this->Component, $this->Page->Document, 'IPage::$Document should equal $_Component');

        # Document
        $this->assertNull($this->Page->File, 'IPage::$File should be NULL');

        # Test dynamic property
        $this->assertEquals($this->Component->encoding, $this->Page->encoding, 'IPage::$encoding should equal $_Component::$encoding');

        # Test undefined property
        try {
            $this->Page->undefined;
            $this->fail('Failed to generate notice with undefined property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Undefined property', $e->getMessage(), 'Invalid notice: ' . $e->getMessage());
        }

        $this->assertNull(@$this->Page->bar, 'IPage::$bar should be NULL');
    }

    /**
     * @covers ::__isset
     */
    public function test_isset()
    {
        $this->assertSame(IDataMapper::UPDATED, $this->Page->setCreated(new DateTime), 'IPage::setCreated() Should return IDataMapper::UPDATED');
        $this->assertSame(IDataMapper::UPDATED, $this->Page->setModified(new DateTime), 'IPage::setModified() Should return IDataMapper::UPDATED');

        # Status
        $this->assertTrue(isset($this->Page->Status), 'IPage::$Status should exist');

        # Serializer
        $this->assertTrue(isset($this->Page->Serializer), 'IPage::$Serializer should exist');

        # Parent
        $this->assertFalse(isset($this->Page->Parent), 'IPage::$Parent should not exist');

        # ID
        $this->assertTrue(isset($this->Page->ID), 'IPage::$ID should exist');

        # Component
        $this->assertTrue(isset($this->Page->Component), 'IPage::$Component should exist');

        # Mediator
        $this->assertTrue(isset($this->Page->Mediator), 'IPage::$Mediator should exist');

        # MediatorID
        $this->assertTrue(isset($this->Page->MediatorID), 'IPage::$MediatorID should exist');

        # RequestHead
        $this->assertTrue(isset($this->Page->RequestHead), 'IPage::$RequestHead should exist');

        # ResponseHead
        $this->assertTrue(isset($this->Page->ResponseHead), 'IPage::$ResponseHead should exist');

        # Base
        $this->assertTrue(isset($this->Page->Base), 'IPage::$Base should exist');

        # Created
        $this->assertTrue(isset($this->Page->Created), 'IPage::$Created should exist');

        # Modified
        $this->assertTrue(isset($this->Page->Modified), 'IPage::$Modified should exist');

        # Document
        $this->assertTrue(isset($this->Page->Document), 'IPage::$Document should exist');

        # File
        $this->assertFalse(isset($this->Page->File), 'IPage::$File should exist');

        # Test dynamic property
        $this->assertTrue(isset($this->Page->encoding), 'Page::$encoding should exist');

        # Test undefined property
        $this->assertFalse(isset($this->Page->undefined), 'Page::$undefined shouldn\'t exist');
    }

    /**
     * @covers ::__set
     */
    public function test_set()
    {
        # Status
        try {
            $this->Page->Status = 0;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Page->Status = 0;

        # Serializer
        try {
            $this->Page->Serializer = 0;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Page->Serializer = 0;

        # Parent
        $Parent                = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Page->Parent = $Parent;

        $this->assertSame($Parent, $this->Page->Parent, 'IPage::$Parent should equal IPage::getParent()');

        try {
            $this->Page->Parent = null;
            $this->fail('Failed to generate notice with invalid value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Page->Parent = null;

        try {
            $this->Page->Parent = $Parent;
            $this->fail('Failed to generate notice with oneshot value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # ID
        try {
            $this->Page->ID = 'foo';
            $this->fail('Failed to generate notice with readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Page->ID = 'foo';

        # Mediator
        $Mediator                = $this->getMockForAbstractClass('\\BLW\\Type\\IMediator');
        $this->Page->Mediator = $Mediator;

        $this->assertSame($Mediator, $this->Page->getMediator(), 'IPage::$Mediator failed to call IPage::setMediator()');

        try {
            $this->Page->Mediator = null;
            $this->fail('Failed to generate notice with invalid value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Page->Mediator = null;

        # MediatorID
        try {
            $this->Page->MediatorID = 'foo';
            $this->fail('Failed to generate notice with readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Page->MediatorID = 'foo';

        # Component
        try {
        	$this->Page->Component = $this->Component;
        	$this->fail('Failed generating notice with readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {}

    	@$this->Page->Component = $this->Component;

    	# RequestHead
        try {
        	$this->Page->RequestHead = new Request;
        	$this->fail('Failed generating notice with readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {}

    	@$this->Page->RequestHead = new Request;

    	# ResponseHead
        try {
        	$this->Page->ResponseHead = new Response;
        	$this->fail('Failed generating notice with readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {}

    	@$this->Page->ResponseHead = new Response;

    	# Created
        $Date                = new DateTime;
        $this->Page->Created = $Date;

        $this->assertSame($Date, $this->Page->getCreated(), 'IPage::$Created Failed to update $_Created');

        try {
            $this->Page->Created = null;
            $this->fail('Failed to generate notice with invalid value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Page->Created = null;

        # Modified
        $this->Page->Modified = $Date;

        $this->assertSame($Date, $this->Page->getModified(), 'IPage::$Modified Failed to update $_Modified');

        try {
            $this->Page->Modified = null;
            $this->fail('Failed to generate notice with invalid value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Page->Modified = null;

        # Document
        try {
        	$this->Page->Document = $this->Component;
        	$this->fail('Failed generating notice with readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {}

    	@$this->Page->Document = $this->Component;

        # Test dynamic property
        $this->Page->resolveExternals = true;
        $this->assertEquals(true, $this->Page->resolveExternals, 'IPage::$resolveExternals should equal true.');

        # Undefined property
    	$this->Page->undefined = 1;
    	$this->assertEquals(1, $this->Page->undefined, 'IPage::$undefined was not created');
    }

    /**
     * @covers ::__unset
     */
    public function test_unset()
    {
        # Parent
        $this->Page->setParent($this->getMockForAbstractClass('\\BLW\\Type\\IObject'));
        $this->assertTrue(isset($this->Page->Parent), 'IPage::$Parent Shoult exist');

        unset($this->Page->Parent);

        $this->assertFalse(isset($this->Page->Parent), 'unset(IPage::$Parent) Failed to reset $_Parent');

        # Status
        unset($this->Page->Status);

        $this->assertSame(0, $this->Page->Status, 'unset(ICommand::$Status) Did not reset $_Status');

        # Mediator
        $this->assertTrue(isset($this->Page->Mediator), 'IPage::$Mediator Should exist');

        unset($this->Page->Mediator);

        $this->assertFalse(isset($this->Page->Mediator), 'unset(IPage::$Mediator) Failed to reset $_Mediator');

        # Undefined
        unset($this->Page->undefined);
    }
}