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
namespace BLW\Type\HTTP;

use DateTime;
use ReflectionProperty;

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
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
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
    protected $Client = NULL;
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

        # Unknown response
        $Request        = new Request;
        $Request->URI   = new GenericURI('http://example.com');
        $Response       = new Response('HTTP', '1.1', 0);
        $Response->URI  = new GenericURI('http://example.com');
        $Page           = $this->Browser->createPage($Request, $Response);

        $this->assertInstanceOf('\\BLW\\Type\\HTTP\\Browser\\IPage', $Page, 'IBrowser::createPage() Returned an invalid result');
        $this->assertInstanceof('\\BLW\\Type\\DOM\\IDocument', $Page->Component, 'IBrowser::$Component Should be an instance of IDocument');

        # Invalid args
    }

    /**
     * @covers ::getID
     */
    public function test_getID()
    {
        $this->assertStringStartsWith('[Browser:', $this->Browser->getID(), 'IBrowser::getID() Returned an invalid value');
        $this->assertStringEndsWith(']', $this->Browser->getID(), 'IBrowser::getID() Returned an invalid value');
    }

    /**
     * @covers ::setLogger
     */
    public function test_setLogger()
    {
        $Logger = new NullLogger;

        $this->assertSame(IDataMapper::UPDATED, $this->Browser->setLogger($Logger), 'IBrowser::setLogger() Should return IDataMapper::UPDATED');
        $this->assertAttributeSame($Logger, 'logger', $this->Browser, 'IBrowser::setLogger() failed to set $logger');
    }

    /**
     * @covers ::setUserAgent
     */
    public function test_setUserAgent()
    {
        $Expected = 'Mozilla/5.0 (Linux; Android 4.1.2; Nexus 7 Build/JZ054K) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.166 Safari/535.19';

        # Valid arguments
        $this->assertSame(IDataMapper::UPDATED, $this->Browser->setUserAgent($Expected), 'IBrowser::setUserAgent() Returned an invalid value');
        $this->assertAttributeSame($Expected, '_UserAgent', $this->Browser, 'IBrowser::setUserAgent() Failed to update $_UserAgent');

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

        # Valid Arguments
        $this->assertSame(IDataMapper::UPDATED, $this->Browser->setPage($Page), 'IBrowser::setPage() Returned an invalid value');
        $this->assertAttributeSame($Page, '_Component', $this->Browser, 'IBrowser::setPage() Failed to update $_Component');

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
     * @covers ::doPageDownload
     */
    public function test_doPageDownload()
    {
        $called = 0;

        $this->Browser->_on('warning', function () use (&$called) {$called++;});

        $Request      = new Request();
        $Request->URI = new GenericURI('http://example.com');
        $Event        = new Event($this->Browser, array('Request' => $Request));

        # Normal request
        $this->Browser->doPageDownload($Event);

        $this->assertCount(1, $this->History, 'IBrowser::doGo() Failed to update $_History');
        $this->assertEquals('http://example.com', strval($this->Browser->Base), 'IBrowser::doGo() Did not navigate to the page specified');
        $this->assertEquals('Heading', $this->Browser->filter('h1')->offsetGet(0)->textContent, 'IBrowser::doGo() Did not load page');

        # Redirect
        $this->Client->Redirect = true;

        $this->Browser->doPageDownload($Event);

        $this->assertCount(2, $this->History, 'IBrowser::doPost() Failed to update $_History');
        $this->assertEquals('http://example.com', strval($this->Browser->Base), 'IBrowser::doPost() Did not navigate to the page specified');
        $this->assertEquals('Heading', $this->Browser->filter('h1')->offsetGet(0)->textContent, 'IBrowser::doGo() Did not load page');

        # Invalid
        $this->Client->Invalid = true;

        $this->Browser->doPageDownload($Event);

        $this->assertCount(3, $this->History, 'IBrowser::doPost() Failed to update $_History');
        $this->assertEquals('about:none', strval($this->Browser->Base), 'IBrowser::doPost() Did not navigate to the page specified');
        $this->assertEquals('Untitled', $this->Browser->filter('title')->offsetGet(0)->textContent, 'IBrowser::createUnknownPage() Returned an invalid IPage');

        # Timeout
        $this->Client->Timeout = true;

        $this->Browser->doPageDownload($Event);

        $this->assertCount(4, $this->History, 'IBrowser::doPost() Failed to update $_History');
        $this->assertEquals('http://example.com', strval($this->Browser->Base), 'IBrowser::doPost() Did not navigate to the page specified');
        $this->assertEquals('408 Request Timeout', $this->Browser->filter('h1')->offsetGet(0)->textContent, 'IBrowser::doPost() Did not load page');

        # Unkown Response
        $Event = new Event($this->Browser, array('Request' => new Request()));

        $this->Browser->doPageDownload($Event);

        $this->assertCount(4, $this->History, 'IBrowser::doPost() Failed to update $_History');
        $this->assertEquals('http://example.com', strval($this->Browser->Base), 'IBrowser::doPost() Did not navigate to the page specified');
        $this->assertEquals('408 Request Timeout', $this->Browser->filter('h1')->offsetGet(0)->textContent, 'IBrowser::doPost() Did not load page');

        # Invalid request
        $Event = new Event($this->Browser, array('Request' => null));

        $this->Browser->doPageDownload($Event);

        $this->assertSame(2, $called, 'IBrowser::doPost() Failed to raise warning with invalid arguments');
    }

    /**
     * @depends test_doPageDownload
     * @covers ::doGo
     * @covers ::_getURI
     */
    public function test_doGo()
    {
        $called = 0;

        $this->Browser->_on('go', array($this->Browser, 'doGo'));
        $this->Browser->_on('Page.Download', array($this->Browser, 'doPageDownload'));
        $this->Browser->_on('Page.Ready', function () use (&$called) {$called++;});

        # Normal browsing
        $this->Browser->go('http://example.com');

        $this->assertCount(1, $this->History, 'IBrowser::doGo() Failed to update $_History');
        $this->assertEquals('http://example.com', strval($this->Browser->Base), 'IBrowser::doGo() Did not navigate to the page specified');
        $this->assertEquals('Heading', $this->Browser->filter('h1')->offsetGet(0)->textContent, 'IBrowser::doGo() Did not load page');
        $this->assertSame(1, $called, 'IBrowser::goGo() Failed to generate Page.Ready event');

        $this->Browser->go(new GenericURI('http://example.com'));
        $this->assertCount(2, $this->History, 'IBrowser::doGo() Failed to update $_History');
        $this->assertEquals('http://example.com', strval($this->Browser->Base), 'IBrowser::doGo() Did not navigate to the page specified');
        $this->assertSame(2, $called, 'IBrowser::goGo() Failed to generate Page.Ready event');

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
     * @depends test_doPageDownload
     * @covers ::doPost
     * @covers ::_getURI
     */
    public function test_doPost()
    {
        $called = 0;

        $this->Browser->_on('post', array($this->Browser, 'doPost'));
        $this->Browser->_on('Page.Download', array($this->Browser, 'doPageDownload'));
        $this->Browser->_on('Page.Ready', function () use (&$called) {$called++;});

        # Normal browsing
        $this->Browser->post('http://example.com', array());

        $this->assertCount(1, $this->History, 'IBrowser::doPost() Failed to update $_History');
        $this->assertEquals('http://example.com', strval($this->Browser->Base), 'IBrowser::doPost() Did not navigate to the page specified');
        $this->assertEquals('Heading', $this->Browser->filter('h1')->offsetGet(0)->textContent, 'IBrowser::doPost() Did not load page');
        $this->assertSame(1, $called, 'IBrowser::goGo() Failed to generate Page.Ready event');

        $this->Browser->post(new GenericURI('http://example.com'), array('foo' => 1));
        $this->assertCount(2, $this->History, 'IBrowser::doPost() Failed to update $_History');
        $this->assertEquals('http://example.com', strval($this->Browser->Base), 'IBrowser::doPost() Did not navigate to the page specified');
        $this->assertSame(2, $called, 'IBrowser::goGo() Failed to generate Page.Ready event');

        # Invalid URI
        $this->Browser->post('', array());

        $this->assertEquals('about:none', strval($this->Browser->Base), 'IBrowser::doPost() Did not navigate to the page specified');
        $this->assertEquals('Untitled', $this->Browser->filter('title')->offsetGet(0)->textContent, 'IBrowser::doPost() Did not load page');

        # Invalid Post data
        $this->Browser->post('http://example.com', null);

        # Invalid arguments
        $this->Browser->post(NULL, array());

        $Event = new Event($this->Browser);

        $this->Browser->doPost($Event);
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
        $this->Browser->_on('Page.Download', array($this->Browser, 'doPageDownload'));
        $this->Browser->_on('back', array($this->Browser, 'doBack'));
        $this->Browser->_on('notice', function ($e) use (&$LastNotice) {$LastNotice = $e->Arguments[0];});
        $this->Browser->_on('debug', function ($e) use (&$LastDebug) {$LastDebug = $e->Arguments[0];});

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
        $this->Browser->_on('Page.Download', array($this->Browser, 'doPageDownload'));
        $this->Browser->_on('back', array($this->Browser, 'doBack'));
        $this->Browser->_on('forward', array($this->Browser, 'doForward'));
        $this->Browser->_on('notice', function ($e) use (&$LastNotice) {$LastNotice = $e->Arguments[0];});
        $this->Browser->_on('debug', function ($e) use (&$LastDebug) {$LastDebug = $e->Arguments[0];});

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
        $this->assertTrue($this->Browser->debug(new \SplFileInfo(__FILE__)), 'IBrowser::debug() should return true');
        $this->assertTrue($this->Browser->debug(null));
    }

    /**
     * @covers ::doNotice
     */
    public function test_doNotice()
    {
        $this->Browser->_on('notice', array($this->Browser, 'doNotice'));
        $this->assertTrue($this->Browser->notice('test'), 'IBrowser::debug() should return true');
        $this->assertTrue($this->Browser->notice(new \SplFileInfo(__FILE__)), 'IBrowser::debug() should return true');
        $this->assertTrue($this->Browser->notice(null));
        $this->assertTrue($this->Browser->notice());
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
        } catch (\PHPUnit_Framework_Error_Notice $e) {}

        @$this->Browser->warning('test');

        try {
            $this->Browser->warning(new \SplFileInfo(__FILE__));
            $this->fail('IBrowser::doWarning() Failed to generate notice');
        } catch (\PHPUnit_Framework_Error_Notice $e) {}

        @$this->Browser->warning(new \SplFileInfo(__FILE__));

        $this->Browser->warning(null);
        $this->Browser->warning();
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
        } catch (\PHPUnit_Framework_Error_Warning $e) {}

        @$this->Browser->error('test');

        try {
            $this->Browser->error(new \SplFileInfo(__FILE__));
            $this->fail('IBrowser::doError() Failed to generate warning');
        } catch (\PHPUnit_Framework_Error_Warning $e) {}

        @$this->Browser->error(new \SplFileInfo(__FILE__));

        $this->Browser->error(null);
        $this->Browser->error();
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
        } catch (\RuntimeException $e) {}

        try {
            $this->Browser->exception(new \SplFileInfo(__FILE__));
            $this->fail('IBrowser::doException() Failed to generate exception');
        } catch (\RuntimeException $e) {}

        $this->Browser->exception(null);
        $this->Browser->exception();
    }

    /**
     * @covers ::__get
     */
    public function test_get()
    {
        # Status
        $this->assertAttributeSame($this->Browser->Status, '_Status', $this->Browser, 'IBrowser::$Status should equal IBrowser::_Status');

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
        try {
            $this->Browser->undefined;
            $this->fail('Failed to generate notice with undefined property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Undefined property', $e->getMessage(), 'IBrowser::$undefined is undefined and should raise a notice');
        }

        $this->assertNull(@$this->Browser->undefined, 'IBrowser::$undefined should return NULL');
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
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Browser->Status = 0;

        # Serializer
        try {
            $this->Browser->Serializer = 0;
            $this->fail('Failed to generate notice on readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Browser->Serializer = 0;

        # Parent
        $Parent                = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Browser->Parent = $Parent;

        $this->assertSame($Parent, $this->Browser->Parent, 'IBrowser::$Parent should equal IBrowser::getParent()');

        try {
            $this->Browser->Parent = null;
            $this->fail('Failed to generate notice with invalid value');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Browser->Parent = null;

        try {
            $this->Browser->Parent = $Parent;
            $this->fail('Failed to generate notice with oneshot value');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # ID
        try {
            $this->Browser->ID = 'foo';
            $this->fail('Failed to generate notice with readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Browser->ID = 'foo';

        # Component
        try {
            $this->Browser->Component = $this->Component;
            $this->fail('Failed generating notice with readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {}

        @$this->Browser->Component = $this->Component;

        # Client
        try {
            $this->Browser->Client = 'foo';
            $this->fail('Failed to generate notice on readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Browser->Client = 'foo';

        # RequestFactory
        try {
            $this->Browser->RequestFactory = 'foo';
            $this->fail('Failed to generate notice on readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Browser->RequestFactory = 'foo';

        # Engines
        try {
            $this->Browser->Engines = 'foo';
            $this->fail('Failed to generate notice on readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Browser->Engines = 'foo';

        # Plugins
        try {
            $this->Browser->Plugins = 'foo';
            $this->fail('Failed to generate notice on readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Browser->Plugins = 'foo';

        # UserAgent
        $this->Browser->UserAgent = 'foo';
        $this->assertEquals('foo', $this->Browser->getUserAgent(), 'IBrowser::$UserAgent failed to update $_UserAgent');

        try {
            $this->Browser->UserAgent = null;
            $this->fail('Failed to generate notice with invalid value');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Browser->UserAgent = null;

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
        # Parent
        $this->Browser->Parent = $this->getMockForAbstractClass('\\BLW\Type\IObject');

        unset($this->Browser->Parent);

        $this->assertNull($this->Browser->Parent, 'unset(IBrowser::$Parent) Did not reset $_Parent');

        # Status
        unset($this->Browser->Status);

        $this->assertSame(0, $this->Browser->Status, 'unset(IBrowser::$Status) Did not reset $_Status');

        # Undefined
        unset($this->Browser->undefined);
    }
}
