<?php
/**
 * BrowserTest.php | Apr 17, 2014
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
namespace BLW\Tests\Type\HTTP;

use DateTime;
use ReflectionProperty;
use ReflectionMethod;

use BLW\Type\IDataMapper;

use BLW\Model\GenericContainer;
use BLW\Model\GenericURI;
use BLW\Model\Mediator\Symfony as Mediator;

use BLW\Model\HTTP\Event;
use BLW\Model\HTTP\Client\Mock as Client;
use BLW\Model\HTTP\RequestFactory;
use BLW\Model\HTTP\Request\Generic as Request;
use BLW\Model\HTTP\Response\Generic as Response;
use BLW\Model\HTTP\Browser\Page\HTML as HTMLPage;

use BLW\Model\DOM\Document;

use BLW\Model\MIME\ContentType;
use BLW\Model\MIME\Head\RFC2616 as RFC2616Head;
use Psr\Log\NullLogger;


/**
 * Test for HTTP Browser base class
 * @package BLW\HTTP
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\HTTP\ABrowser
 */
class BrowserTest  extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\HTTP\Browser\IPage
     */
    protected $Component = NULL;

    /**
     * @var \BLW\Type\IMediator
     */
    protected $Mediator = NULL;

    /**
     * @var \BLW\Type\HTTP\IClient
     */
    protected  $Client = NULL;
    /**
     * @var \BLW\Type\IContainer
     */
    protected $History = NULL;

    /**
     * @var \BLW\Type\HTTP\ABrowser
     */
    protected $Browser = NULL;

    protected function setUp()
    {
        $this->Client    = new Client;
        $this->Mediator  = new Mediator;
        $this->Component = new HTMLPage(new Document, new GenericURI('http:/a/b/c/d?query#fragment'), new RFC2616Head, new RFC2616Head, $this->Mediator);
        $this->History   = new GenericContainer('\\BLW\\Type\\HTTP\\Browser\\IPage');
        $this->Browser   = $this->getMockForAbstractClass('\\BLW\\Type\\HTTP\\ABrowser', array($this->Component));

        $this->Browser->setLogger(new NullLogger);

        $Property = new ReflectionProperty($this->Browser, '_Mediator');

        $Property->setAccessible(true);
        $Property->setValue($this->Browser, $this->Mediator);

        $Property = new ReflectionProperty($this->Browser, '_Client');

        $Property->setAccessible(true);
        $Property->setValue($this->Browser, $this->Client);

        $Property = new ReflectionProperty($this->Browser, '_History');

        $Property->setAccessible(true);
        $Property->setValue($this->Browser, $this->History);

        $Property = new ReflectionProperty($this->Browser, '_RequestFactory');

        $Property->setAccessible(true);
        $Property->setValue($this->Browser, new RequestFactory);

        $Property = new ReflectionProperty($this->Browser, '_Engines');

        $Property->setAccessible(true);
        $Property->setValue($this->Browser, new GenericContainer);

        $Property = new ReflectionProperty($this->Browser, '_Plugins');

        $Property->setAccessible(true);
        $Property->setValue($this->Browser, new GenericContainer);
    }

    protected function tearDown()
    {
        $this->Browser = NULL;
        $this->History = NULL;
    }

    /**
     * @covers ::getFactoryMethods
     */
    public function test_getFactoryMethods()
    {
        $this->assertGreaterThan(4, count($this->Browser->getFactoryMethods()), 'IBrowser::getFactoryMethods() Returned an invalid value');
    }

    /**
     * @covers ::createHeaders
     */
    public function test_createHeaders()
    {
        $Headers = $this->Browser->createHeaders();

        $this->assertInstanceOf('\\BLW\\Type\\IContainer', $Headers, 'IBrowser::createHeaders() Returned an invalid value');
        $this->assertGreaterThan(1, count($Headers), 'IBrowser::createHeaders() Returned an invalid value');
        $this->assertArrayHasKey('User-Agent', $Headers, 'IBrowser::createHeaders() Returned an invalid value');
    }

    /**
     * @covers ::createStatusPage
     */
    public function test_createStatusPage()
    {
        $Request        = new Request;
        $Request->URI   = new GenericURI('http://example.com');
        $Response       = new Response('HTTP', '1.1', 404);
        $Response->URI  = new GenericURI('http://example.com');

        $Page           = $this->Browser->createStatusPage($Request, $Response);

        $this->assertInstanceOf('\\BLW\\Type\\HTTP\\Browser\\IPage', $Page, 'IBrowser::createStatus() Returned an invalid result');
        $this->assertInstanceof('\\BLW\\Type\\DOM\\IDocument', $Page->Component, 'IBrowser::createStatus()::$Component Should be an instance of IDocument');
        $this->assertEquals('404 Not Found', $Page->filter('title')->offsetGet(0)->textContent, 'IBrowser::createStatusPage() Returned an invalid IPage');
    }

    /**
     * @covers ::createTimeoutPage
     */
    public function test_createTimeoutPage()
    {
        $Request        = new Request;
        $Request->URI   = new GenericURI('http://example.com');

        $Page           = $this->Browser->createTimeoutPage($Request);

        $this->assertInstanceOf('\\BLW\\Type\\HTTP\\Browser\\IPage', $Page, 'IBrowser::createTimeoutPage() Returned an invalid result');
        $this->assertInstanceof('\\BLW\\Type\\DOM\\IDocument', $Page->Component, 'IBrowser::createTimeoutPage()::$Component Should be an instance of IDocument');
        $this->assertEquals('408 Request Timeout', $Page->filter('title')->offsetGet(0)->textContent, 'IBrowser::createTimeoutPage() Returned an invalid IPage');
    }

    /**
     * @covers ::createUnknownPage
     */
    public function test_createUnknownPage()
    {
        $Page           = $this->Browser->createUnknownPage();

        $this->assertInstanceOf('\\BLW\\Type\\HTTP\\Browser\\IPage', $Page, 'IBrowser::createUnknownPage() Returned an invalid result');
        $this->assertInstanceof('\\BLW\\Type\\DOM\\IDocument', $Page->Component, 'IBrowser::createUnknownPage()::$Component Should be an instance of IDocument');
        $this->assertEquals('Untitled', $Page->filter('title')->offsetGet(0)->textContent, 'IBrowser::createUnknownPage() Returned an invalid IPage');
    }

    /**
     * @depends test_createStatusPage
     * @depends test_createUnknownPage
     * @covers ::createPage
     */
    public function test_createPage()
    {
        $Request        = new Request;
        $Request->URI   = new GenericURI('http://example.com');

        # Text document
        $Response                           = new Response('HTTP', '1.1', 200);
        $Response->URI                      = new GenericURI('http://example.com');
        $Response->Header['Content-Type']   = new ContentType('text/html');
        $Response->Body['Content']          = '<html><head><title>Test</title></head><body><h1>Test</h1></body></html>';

        $Page = $this->Browser->createPage($Request, $Response);

        $this->assertInstanceOf('\\BLW\\Type\\HTTP\\Browser\\IPage', $Page, 'IBrowser::createPage() Returned an invalid result');
        $this->assertInstanceof('\\BLW\\Type\\DOM\\IDocument', $Page->Component, 'IBrowser::$Component Should be an instance of IDocument');

        # File document
        $Response->Header['Content-Type']   = 'application/octet-stream';

        $Page = $this->Browser->createPage($Request, $Response);

        $this->assertInstanceOf('\\BLW\\Type\\HTTP\\Browser\\IPage', $Page, 'IBrowser::createPage() Returned an invalid result');
        $this->assertInstanceof('\\BLW\\Type\\IFile', $Page->Component, 'IBrowser::$Component Should be an instance of IDocument');

        # No response content type
        unset($Response->Header['Content-Type']);

        $Page = $this->Browser->createPage($Request, $Response);

        $this->assertInstanceOf('\\BLW\\Type\\HTTP\\Browser\\IPage', $Page, 'IBrowser::createPage() Returned an invalid result');
        $this->assertInstanceof('\\BLW\\Type\\DOM\\IDocument', $Page->Component, 'IBrowser::$Component Should be an instance of IDocument');

        # Empty Response body
        $Response->Header['Content-Type']   = new ContentType('text/html');

        unset($Response->Body['Content']);

        $Page = $this->Browser->createPage($Request, $Response);

        $this->assertInstanceOf('\\BLW\\Type\\HTTP\\Browser\\IPage', $Page, 'IBrowser::createPage() Returned an invalid result');
        $this->assertInstanceof('\\BLW\\Type\\DOM\\IDocument', $Page->Component, 'IBrowser::$Component Should be an instance of IDocument');

        # Invalid args
    }

    /**
     * @covers ::setUserAgent
     */
    public function test_setUserAgent()
    {
        $Expected = 'Mozilla/5.0 (Linux; Android 4.1.2; Nexus 7 Build/JZ054K) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.166 Safari/535.19';
        $Property = new ReflectionProperty($this->Browser, '_UserAgent');

        $Property->setAccessible(true);

        # Valid arguments
        $this->assertSame(IDataMapper::UPDATED, $this->Browser->setUserAgent($Expected), 'IBrowser::setUserAgent() Returned an invalid value');
        $this->assertSame($Expected, $Property->getValue($this->Browser), 'IBrowser::setUserAgent() Failed to update $_UserAgent');

        # Invalid arguments
        $this->assertSame(IDataMapper::INVALID, $this->Browser->setUserAgent(NULL), 'IBrowser::setUserAgent() Returned an invalid value');
    }

    /**
     * @depends test_setUserAgent
     * @covers ::getUserAgent
     */
    public function test_getUserAgent()
    {
        $Expected = 'Mozilla/5.0 (Linux; Android 4.1.2; Nexus 7 Build/JZ054K) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.166 Safari/535.19';

        # Valid arguments
        $this->assertSame(IDataMapper::UPDATED, $this->Browser->setUserAgent($Expected), 'IBrowser::setUserAgent() Returned an invalid value');
        $this->assertSame($Expected, $this->Browser->getUserAgent(), 'IBrowser::getUserAgent() Returned an invalid value');

        # Invalid arguments
    }

    /**
     * @covers ::setPage
     */
    public function test_setPage()
    {
        $Page       = new HTMLPage(new Document, new GenericURI(''), new RFC2616Head, new RFC2616Head);
        $Property   = new ReflectionProperty($this->Browser, '_Component');

        $Property->setAccessible(true);

        # Valid Arguments
        $this->assertSame(IDataMapper::UPDATED, $this->Browser->setPage($Page), 'IBrowser::setPage() Returned an invalid value');
        $this->assertSame($Page, $Property->getValue($this->Browser), 'IBrowser::setPage() Failed to update $_Component');

        # Invalid arguments
        $this->assertSame(IDataMapper::INVALID, $this->Browser->setPage(NULL), 'IBrowser::setPage() Returned an invalid value');
    }

    /**
     * @covers ::addHistory
     */
    public function test_addHistory()
    {
        $Page       = new HTMLPage(new Document, new GenericURI(''), new RFC2616Head, new RFC2616Head);

        # Empty History
        $this->assertCount(0, $this->History, 'IBrowser::$_History should initially be empty');
        $this->assertSame(IDataMapper::UPDATED, $this->Browser->addHistory($Page), 'IBrowser::addHistory() Page returned an invalid value');
        $this->assertCount(1, $this->History, 'IBrowser::addHistory() Failed to update $_History');
        $this->assertSame($Page, $this->History[0], 'IBrowser::addHistory() Failed to update $_History');

        # Full History
        $this->assertSame(IDataMapper::UPDATED, $this->Browser->addHistory(clone $Page), 'IBrowser::addHistory() Page returned an invalid value');
        $this->assertSame(IDataMapper::UPDATED, $this->Browser->addHistory(clone $Page), 'IBrowser::addHistory() Page returned an invalid value');
        $this->assertSame(IDataMapper::UPDATED, $this->Browser->addHistory(clone $Page), 'IBrowser::addHistory() Page returned an invalid value');
        $this->assertCount(4, $this->History, 'IBrowser::addHistory() Failed to update $_History');
        $this->assertSame(IDataMapper::UPDATED, $this->Browser->addHistory($Page), 'IBrowser::addHistory() Page returned an invalid value');
        $this->assertCount(4, $this->History, 'IBrowser::addHistory() Failed to update $_History');
        $this->assertSame($Page, $this->History[3], 'IBrowser::addHistory() Failed to update $_History');

        # Invalid Arguments
        $this->assertSame(IDataMapper::INVALID, $this->Browser->addHistory(NULL), 'IBrowser::addHistory() Returned an invalid value');
    }

    /**
     * @covers ::doGo
     */
    public function test_doGo()
    {
        $this->Browser->_on('go', array($this->Browser, 'doGo'));

        # Normal browsing
        $this->Browser->go('http://example.com');

        $this->assertCount(1, $this->History, 'IBrowser::doGo() Failed to update $_History');
        $this->assertEquals('http://example.com', strval($this->Browser->Base), 'IBrowser::doGo() Did not navigate to the page specified');
        $this->assertEquals('Heading', $this->Browser->filter('h1')->offsetGet(0)->textContent, 'IBrowser::doGo() Did not load page');

        $this->Browser->go(new GenericURI('http://example.com'));
        $this->assertCount(2, $this->History, 'IBrowser::doGo() Failed to update $_History');
        $this->assertEquals('http://example.com', strval($this->Browser->Base), 'IBrowser::doGo() Did not navigate to the page specified');

        # Timeout
        $this->Client->Timeout = true;

        $this->Browser->go('http://example.com');

        $this->assertCount(3, $this->History, 'IBrowser::doGo() Failed to update $_History');
        $this->assertEquals('http://example.com', strval($this->Browser->Base), 'IBrowser::doGo() Did not navigate to the page specified');
        $this->assertEquals('408 Request Timeout', $this->Browser->filter('h1')->offsetGet(0)->textContent, 'IBrowser::doGo() Did not load page');

        # Invalid URI
        $this->Browser->go('');

        $this->assertEquals('about:none', strval($this->Browser->Base), 'IBrowser::doGo() Did not navigate to the page specified');
        $this->assertEquals('Untitled', $this->Browser->filter('title')->offsetGet(0)->textContent, 'IBrowser::doGo() Did not load page');

        # Invalid arguments
        $this->Browser->go(NULL);

        $Event = new Event($this->Browser);

        $this->Browser->doGo($Event);
    }

    /**
     * @depends test_doGo
     * @covers ::doBack
     */
    public function test_doBack()
    {
        $LastNotice = '';
        $LastDebug  = '';

        $this->Browser->_on('go', array($this->Browser, 'doGo'));
        $this->Browser->_on('back', array($this->Browser, 'doBack'));
        $this->Browser->_on('notice', function($e) use(&$LastNotice) {$LastNotice = $e->Arguments[0];});
        $this->Browser->_on('debug', function($e) use(&$LastDebug) {$LastDebug = $e->Arguments[0];});

        # Empty history
        $this->Browser->back();

        $this->assertContains('Tried to', $LastNotice, 'IBrowser::doBack() Failed to generate notice');
        $this->assertEquals('', $LastDebug, 'IBrowser::doBack() Generated debug info');

        # History with only 1 item

        $this->Browser->go('http://example.com');

        $LastNotice = '';
        $LastDebug  = '';

        $this->Browser->back();

        $this->assertContains('Tried to', $LastNotice, 'IBrowser::doBack() Failed to generate notice');
        $this->assertEquals('', $LastDebug, 'IBrowser::doBack() Generated debug info');

        # History with 2 items
        $this->Browser->go('http://example.com');

        $LastNotice = '';
        $LastDebug  = '';

        $this->Browser->back();

        $this->assertContains('http://example.com', $LastDebug, 'IBrowser::doBack() Failed to generate debug info');
        $this->assertEquals('', $LastNotice, 'IBrowser::doBack() Generated notice');

        $LastNotice = '';
        $LastDebug  = '';

        $this->Browser->back();

        $this->assertContains('Tried to', $LastNotice, 'IBrowser::doBack() Failed to generate notice');
        $this->assertEquals('', $LastDebug, 'IBrowser::doBack() Generated debug info');

        # Invalid arguments
    }

    /**
     * @depends test_doBack
     * @covers ::doForward
     */
    public function test_doForward()
    {
        $LastNotice = '';
        $LastDebug  = '';

        $this->Browser->_on('go', array($this->Browser, 'doGo'));
        $this->Browser->_on('back', array($this->Browser, 'doBack'));
        $this->Browser->_on('forward', array($this->Browser, 'doForward'));
        $this->Browser->_on('notice', function($e) use(&$LastNotice) {$LastNotice = $e->Arguments[0];});
        $this->Browser->_on('debug', function($e) use(&$LastDebug) {$LastDebug = $e->Arguments[0];});

        # Empty history
        $this->Browser->forward();

        $this->assertContains('Tried to', $LastNotice, 'IBrowser::doForward() Failed to generate notice');
        $this->assertEquals('', $LastDebug, 'IBrowser::doForward() Generated debug info');

        # History with only 1 item

        $this->Browser->go('http://example.com');

        $LastNotice = '';
        $LastDebug  = '';

        $this->Browser->forward();

        $this->assertContains('Tried to', $LastNotice, 'IBrowser::doForward() Failed to generate notice');
        $this->assertEquals('', $LastDebug, 'IBrowser::doForward() Generated debug info');

        # History with 2 items
        $this->Browser->go('http://example.com');

        $LastNotice = '';
        $LastDebug  = '';

        $this->Browser->forward();

        $this->assertContains('Tried to', $LastNotice, 'IBrowser::doForward() Failed to generate notice');
        $this->assertEquals('', $LastDebug, 'IBrowser::doForward() Generated debug info');

        $this->Browser->back();

        $LastNotice = '';
        $LastDebug  = '';

        $this->Browser->forward();

        $this->assertContains('http://example.com', $LastDebug, 'IBrowser::doForward() Failed to generate debug info');
        $this->assertEquals('', $LastNotice, 'IBrowser::doForward() Generated notice');

        $LastNotice = '';
        $LastDebug  = '';

        $this->Browser->forward();

        $this->assertContains('Tried to', $LastNotice, 'IBrowser::doForward() Failed to generate notice');
        $this->assertEquals('', $LastDebug, 'IBrowser::doForward() Generated debug info');

        # Invalid arguments
    }

    /**
     * @covers ::doDebug
     */
    public function test_doDebug()
    {
        $this->Browser->_on('debug', array($this->Browser, 'doDebug'));
        $this->assertTrue($this->Browser->debug('test'), 'IBrowser::debug() should return true');
    }

    /**
     * @covers ::doNotice
     */
    public function test_doNotice()
    {
        $this->Browser->_on('notice', array($this->Browser, 'doNotice'));
        $this->assertTrue($this->Browser->warning('test'), 'IBrowser::debug() should return true');
    }

    /**
     * @covers ::doWarning
     */
    public function test_doWarning()
    {
        $this->Browser->_on('warning', array($this->Browser, 'doWarning'));

        try {
            $this->Browser->warning('test');
            $this->fail('IBrowser::doWarning() Failed to generate notice');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {}
    }

    /**
     * @covers ::doError
     */
    public function test_doError()
    {
        $this->Browser->_on('error', array($this->Browser, 'doError'));

        try {
            $this->Browser->error('test');
            $this->fail('IBrowser::doError() Failed to generate warning');
        }

        catch (\PHPUnit_Framework_Error_Warning $e) {}
    }

    /**
     * @covers ::doException
     */
    public function test_doException()
    {
        $this->Browser->_on('exception', array($this->Browser, 'doException'));

        try {
            $this->Browser->exception('test');
            $this->fail('IBrowser::doException() Failed to generate exception');
        }

        catch (\RuntimeException $e) {}
    }

    /**
     * @covers ::__get
     */
    public function test_get()
    {
	    # Make property readable / writable
	    $Status = new ReflectionProperty($this->Browser, '_Status');
	    $Status->setAccessible(true);

	    # Status
        $this->assertSame($this->Browser->Status, $Status->getValue($this->Browser), 'IBrowser::$Status should equal IBrowser::_Status');

	    # Serializer
	    $this->assertSame($this->Browser->Serializer, $this->Browser->getSerializer(), 'IBrowser::$Serializer should equal IBrowser::getSerializer()');

	    # Parent
        $this->assertNULL($this->Browser->Parent, 'IBrowser::$Parent should initially be NULL');

        # ID
        $this->assertSame($this->Browser->ID, $this->Browser->getID(), 'IBrowser::$ID should equal IBrowser::getID()');

        # Component
        $this->assertSame($this->Component, $this->Browser->Component, 'IBrowser::$Component should equal $_Component');

        # Client
        $this->assertSame($this->Client, $this->Browser->Client, 'IBrowser::$Client should equeal $_Client');

        # RequestFactory
        $this->assertInstanceOf('\\BLW\\Type\\IFactory', $this->Browser->RequestFactory, 'IBrowser::$RequestFactory should equal $_RequestFactory');

        # Engines
        $this->assertInstanceOf('\\BLW\\Type\\IContainer', $this->Browser->Engines, 'IBrowser::$Engines should equal $_Engines');

        # Plugins
        $this->assertInstanceOf('\\BLW\\Type\\IContainer', $this->Browser->Plugins, 'IBrowser::$Plugins should equal $_Plugins');

        # User Agent
        $this->Browser->setUserAgent('foo');
        $this->assertSame('foo', $this->Browser->UserAgent, 'IBrowser::$UserAgent should equal `foo`');

        # Test component property
        $this->assertEquals($this->Browser->Component->Base, $this->Browser->Base, 'IBrowser::$tagName should equal `span`');

        # Test undefined property
        try { $this->Browser->bar; }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Undefined property', $e->getMessage(), 'IBrowser::$bar is undefined and should raise a notice');
        }
   }

   /**
    * @covers ::__isset
    */
   public function test_isset()
   {
        # Status
       $this->assertTrue(isset($this->Browser->Status), 'IBrowser::$Status should exist');

	    # Serializer
	    $this->assertTrue(isset($this->Browser->Serializer), 'IBrowser::$Serializer should exist');

	    # Parent
        $this->assertFalse(isset($this->Browser->Parent), 'IBrowser::$Parent should not exist');

	    # ID
        $this->assertEquals(isset($this->Browser->ID), $this->Browser->getID() !== NULL, 'IBrowser::$ID should exist');

        # Component
        $this->assertTrue(isset($this->Browser->Component), 'IBrowser::$Component should exist');

        # Client
        $this->assertTrue(isset($this->Browser->Client), 'IBrowser::$Client should exist');

        # RequestFactory
        $this->assertTrue(isset($this->Browser->RequestFactory), 'IBrowser::$RequestFactory should exist');

        # Engines
        $this->assertTrue(isset($this->Browser->Engines), 'IBrowser::$Engines should exist');

        # Plugins
        $this->assertTrue(isset($this->Browser->Plugins), 'IBrowser::$Plugins should exist');

        # User Agent
        $this->Browser->setUserAgent('foo');
        $this->assertTrue(isset($this->Browser->UserAgent), 'IBrowser::$UserAgent should exist');

       # Test component property
       $this->assertTrue(isset($this->Browser->Base), 'IBrowser::$Base should exist');

        # Test undefined property
       $this->assertFalse(isset($this->Browser->bar), 'IBrowser::$bar shouldn\'t exist');
  }

    /**
     * @covers ::__set
     */
    public function test_set()
    {
        # Status
        try {
            $this->Browser->Status = 0;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        # Serializer
        try {
            $this->Browser->Serializer = 0;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        # Parent
        $this->Browser->Parent = $this->getMockForAbstractClass('\\BLW\\Type\\IObject');
        $this->assertSame($this->Browser->Parent, $this->Browser->getParent(), 'IBrowser::$Parent should equal IBrowser::getParent');
        $this->assertTrue(isset($this->Browser->Parent), 'IBrowser::$Parent should exist');

	    # ID
        try {
            $this->Browser->ID = 'foo';
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

	    # Component
        try {
            $this->Browser->Component = 'foo';
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

	    # Client
        try {
            $this->Browser->Client = 'foo';
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

	    # RequestFactory
        try {
            $this->Browser->RequestFactory = 'foo';
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

	    # Engines
        try {
            $this->Browser->Engines = 'foo';
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

	    # Plugins
        try {
            $this->Browser->Plugins = 'foo';
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        # UserAgent
        $this->Browser->UserAgent = 'foo';
        $this->assertEquals('foo', $this->Browser->getUserAgent(), 'IBrowser::$UserAgent ');

        # Test component property
        $Now                    = new DateTime;
        $this->Browser->Created = $Now;

        $this->assertEquals($Now, $this->Browser->Created, 'IBrowser::$Created failed to update.');

        # Test undefined property
        $this->Browser->undefined = 1;
        $this->assertEquals(1, $this->Browser->undefined, 'IBrowser::$undefined failed to update');
    }

    /**
     * @covers ::__unset
     */
    public function test_unset()
    {
        unset($this->Browser->undefined);
    }
}