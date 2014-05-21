<?php
/**
 * PhantomJSTest.php | Apr 20, 2014
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
namespace BLW\Model\HTTP\Browser\Engine;

use ReflectionProperty;

use BLW\Model\GenericFile;
use BLW\Model\InvalidArgumentException;
use BLW\Model\GenericURI;
use BLW\Model\Config\Generic as Config;
use BLW\Model\GenericEvent as Event;

use BLW\Model\HTTP\Client\CURL;
use BLW\Model\HTTP\Browser\Nexus;
use BLW\Model\HTTP\Browser\Engine\PhantomJS as Engine;
use BLW\Model\HTTP\Request\Generic as Request;
use BLW\Type\HTTP\IRequest;


/**
 * Tests PhantomJS HTTP Browser engine
 * @package BLW\HTTP
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\HTTP\Browser\Engine\PhantomJS
 */
class PhantomJSTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\HTTP\IBrowser
     */
    protected $Browser = NULL;

    /**
     * @var \BLW\Model\HTTP\Browser\Engine\PhantomJS
     */
    protected $Engine = NULL;

    public function generateValidArgs()
    {
        $JSONConfig = dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR;

        return array(
            array(
                 new GenericFile('phantomjs')
                ,new GenericFile("{$JSONConfig}phantomjs.json")
                ,new Config(array(
                     'Timeout'     => 10
                    ,'CWD'         => NULL
                    ,'Environment' => NULL
                    ,'Extras'      => array()
                ))
            )
        );
    }

    protected function setUp()
    {

        list(list($Executable, $ConfigFile, $Config)) = $this->generateValidArgs();
        $this->Engine           = new Engine($Executable, $ConfigFile, $Config);
        $this->Browser          = new Nexus(new CURL, new Config(array(
            'MaxHistory' => 10
        )));

        $this->Browser->Mediator->remSubscriber($this->Browser);

        $this->Browser->Engines['PhantomJS'] = $this->Engine;
    }

    protected function tearDown()
    {
        $this->Browser = NULL;
        $this->Engine  = NULL;
    }

    /**
     * @covers ::getSubscribedEvents
     */
    public function test_getSubscribedEvents()
    {
        foreach($this->Engine->getSubscribedEvents() as $Event)
            $this->assertTrue(is_callable(array($this->Engine, $Event[0])), 'PhantomJS::getSubscribedEvents() Returned an invalid event');
    }

    /**
     * @covers ::phantomStart
     */
    public function test_phantomStart()
    {
        $Process = function ($Engine) {
            $Property = new ReflectionProperty($Engine, '_Process');
            $Property->setAccessible(true);

            return $Property->getValue($Engine);
        };

        $fp = function ($Engine) {
            $Property = new ReflectionProperty($Engine, '_Process');
            $Property->setAccessible(true);
            $Process  = $Property->getValue($Engine);
            $Property = new ReflectionProperty($Process, '_fp');
            $Property->setAccessible(true);

            return $Property->getValue($Process);
        };

        # Valid arguments
        $this->Engine->phantomStart();

        $this->assertInstanceOf('\\BLW\\Type\\Command\\ICommand', $Process($this->Engine), 'PhantomJS::phantomStart() Failed to set $_Process');
        $this->assertInternalType('resource', $fp($this->Engine), 'PhantomJS::phantomStart() Failed to create process');
        $this->assertSame('process', get_resource_type($fp($this->Engine)), 'PhantomJS::phantomStart() Failed to start process');

        # Invalid arguments
    }

    /**
     * @depends test_phantomStart
     * @covers ::phantomRestart
     * @covers ::phantomStart
     */
    public function test_phantomRestart()
    {
        $Process = function ($Engine) {
            $Property = new ReflectionProperty($Engine, '_Process');
            $Property->setAccessible(true);

            return $Property->getValue($Engine);
        };

        $fp = function ($Engine) {
            $Property = new ReflectionProperty($Engine, '_Process');
            $Property->setAccessible(true);
            $Process  = $Property->getValue($Engine);
            $Property = new ReflectionProperty($Process, '_fp');
            $Property->setAccessible(true);

            return $Property->getValue($Process);
        };

        # Valid arguments
        $original_fp = $fp($this->Engine);

        $this->assertInternalType('resource', $original_fp, 'PhantomJS::phantomStart() Failed to create process');

        $this->Engine->phantomRestart();

        $this->assertInternalType('resource', $fp($this->Engine), 'PhantomJS::phantomRestart() Failed to create process');
        $this->assertNotEquals($original_fp, $fp($this->Engine), 'PhantomJS::phantomRestart() Failed to create a new process');

        # Invalid arguments
    }

    /**
     * @depends test_phantomStart
     * @covers ::phantomStop
     * @covers ::phantomStart
     */
    public function test_phantomStop()
    {
        $fp = function ($Engine) {
            $Property = new ReflectionProperty($Engine, '_Process');
            $Property->setAccessible(true);
            $Process  = $Property->getValue($Engine);
            $Property = new ReflectionProperty($Process, '_fp');
            $Property->setAccessible(true);

            return $Property->getValue($Process);
        };

        $this->assertInternalType('resource', $fp($this->Engine), 'PhantomJS should have a process');
        $this->Engine->phantomStop();
        $this->assertNotInternalType('resource', $fp($this->Engine), 'PhantomJS::phantomStop() Failed to close process');
    }

    /**
     * @depends test_phantomStart
     * @covers ::phantom
     * @covers ::phantomStart
     * @covers ::_sendJSON
     */
    public function test_phantom()
    {
        # Valid args
        $Result = $this->Engine->phantom("phantom.version;");

        $this->assertInstanceOf('stdClass', $Result, 'PhantomJS::phantom() Should return an instance of stdClass');
        $this->assertObjectHasAttribute('status', $Result, 'PhantomJS::phantom() Returned an invalid value');

        # Invalid args
        try {
            $this->Engine->phantom(NULL);
            $this->fail('Failed to generate exception with invalid arguments');
        } catch (InvalidArgumentException $e) {}
    }

    /**
     * @depeds test_phantom
     * @covers ::parseResults
     */
    public function test_parseResults()
    {
        static $JSON = <<<EOT
{
    "status": "ok",
    "log": {
        "version": "1.2",
        "creator": {
            "name": "BLW",
            "version": "1.0.0",
            "comment": ""
        },
        "browser": {
            "name": "PhantomJS",
            "version": "1.9.2",
            "comment": ""
        },
        "pages": [
            {
                "startedDateTime": "2014-05-19T14:53:25.120Z",
                "id": "about:blank",
                "title": "",
                "pageTimings": {
                    "ononContentLoad": -1,
                    "onLoad": 4003,
                    "comment": ""
                },
                "comment": "",
                "url": "about:blank",
                "html": "<html><head></head><body></body></html>"
            }
        ],
        "entries": [
            {
                "request": {
                    "url": "about:blank"
                },
                "response": {
                    "redirectURL": "about:none"
                }
            },
            {
                "request": {
                    "url": "about:none"
                },
                "response": {
                    "redirectURL": null,
                    "httpVersion": "HTTP/1.1",
                    "status": 200,
                    "headers": [
                        {"name": "User-Agent", "value": "foo"},
                        {"name": "Setcookie", "value": "bar1=1"},
                        {"name": "Setcookie", "value": "bar2=1"}
                    ],
                    "content": {
                        "mimeType": "text/html"
                    }
                }
            }
        ]
    }
}
EOT;
        $results = json_decode($JSON);

        $this->assertInstanceOf('\\BLW\\Type\\HTTP\\IResponse', $this->Engine->parseResults($results), 'PhantomJS::parseResults() should return an instance of IResponse');

        # Incomplete har
        $results->log->pages[0]->url = 'about:invalid';

        # Invalid results
        $this->assertFalse($this->Engine->parseResults($results), 'PhantomJS::parseResults() should return false');

        # Invalid results
        $this->assertFalse($this->Engine->parseResults(null), 'PhantomJS::parseResults() should return false');

        # Error status
        $this->assertFalse($this->Engine->parseResults((object) array('status' => 'foo')), 'PhantomJS::parseResults() should  return false');
    }

    public function generateInvalidArgs()
    {
        $Executable  = NULL;
        $ConfigFile  = NULL;
        $self        = $this;
        $ConfigMinus = function ($k = NULL) use ($self, &$Executable, &$ConfigFile) {
            list(list($Executable, $ConfigFile, $Config)) = $self->generateValidArgs();

            if(isset($k)) unset($Config[$k]);

            return $Config;
        };
        $ConfigMinus();

        return array(
             array($Executable, $ConfigFile, $ConfigMinus('Timeout'))
            ,array($Executable, $ConfigFile, $ConfigMinus('CWD'))
            ,array($Executable, $ConfigFile, $ConfigMinus('Environment'))
            ,array($Executable, $ConfigFile, $ConfigMinus('Extras'))
            ,array($Executable, new GenericFile('1:undefined.c'), $ConfigMinus())
        );
    }

    /**
     * @depends test_phantomStart
     * @depends test_phantom
     * @covers ::__construct
     * @covers ::_sendJSON
     */
    public function test_construct()
    {
        # Valid Arguments
        list(list($Executable, $ConfigFile, $Config)) = $this->generateValidArgs();

        $this->Engine = new Engine($Executable, $ConfigFile, $Config);

        # Invalid arguments
        for ($args=$this->generateInvalidArgs(); list($k,list($Executable, $ConfigFile, $Config)) = each($args);) {

            try {
                new Engine($Executable, $ConfigFile, $Config);
                $this->fail('Failed generating exception with invalid arguments');
            } catch (InvalidArgumentException $e) {}
        }
    }

    /**
     * @depends test_construct
     * @covers ::translate
     */
    public function test_translate()
    {
        $URI     = new GenericURI('about:blank');
        $Referer = new GenericURI('about:blank');
        $Data    = array(
             'foo'    => 1
            ,'bar'    => 'trololololo'
            ,'upload' => new GenericFile(__FILE__)
        );

        $Data    = $this->Engine->translate($this->Browser->RequestFactory->createPost($URI, $Referer, $Data, $this->Browser->createHeaders()));

        $this->assertArrayHasKey('type', $Data, 'PhantomJS::translate() Produced an invalid PhantomJS request');
        $this->assertArrayHasKey('address', $Data, 'PhantomJS::translate() Produced an invalid PhantomJS request');
        $this->assertArrayHasKey('timeout', $Data, 'PhantomJS::translate() Produced an invalid PhantomJS request');
        $this->assertArrayHasKey('headers', $Data, 'PhantomJS::translate() Produced an invalid PhantomJS request');
        $this->assertArrayHasKey('data', $Data, 'PhantomJS::translate() Produced an invalid PhantomJS request');

        $this->assertRegExp('!get|post|head|put|delete!i', $Data['type'], 'PhantomJS::translate() Produced an invalid type');
        $this->assertContains(strval($URI), $Data['address'], 'PhantomJS::translate() Pruduced an invalid address');
        $this->assertNotEmpty($Data['data'], 'PhantomJS::translate() Produced an invalid data');
        $this->assertInternalType('string', $Data['data'], 'PhantomJS::translate() Produced an invalid data');
        $this->assertNotEmpty($Data['headers'], 'PhantomJS::translate() Produced an invalid headers');
        $this->assertInternalType('array', $Data['headers'], 'PhantomJS::translate() Produced an invalid headers');

        # Invalid type
        try {
            $this->Engine->translate(new Request(IRequest::CONNECT));
            $this->fail('Failed to generate exception with invalid type');

        } catch (InvalidArgumentException $e) {

        }
    }

    /**
     * @depends test_translate
     * @covers ::send
     * @covers ::_sendJSON
     * @covers ::parseResults
     */
    public function test_send()
    {
        # Internal page
        $URI     = new GenericURI('about:blank');
        $Referer = new GenericURI('about:blank');
        $Data    = array(
             'foo'    => 1
            ,'bar'    => 'trololololo'
            ,'upload' => new GenericFile(__FILE__)
        );

        $Headers  = $this->Browser->createHeaders();
        $Request  = $this->Browser->RequestFactory->createPost($URI, $Referer, $Data, $Headers);
        $Response = $this->Engine->send($Request);

        $this->assertInstanceOf('\\BLW\\Type\\HTTP\\IResponse', $Response, 'PhantomJS::send() Returned an invalid value');
        $this->assertSame(200, $Response->Status, 'PhantomJS::send() returned an invalid IResponse');
        $this->assertContains('<html', strval($Response->Body), 'PhantomJS::send() returned an invalid IResponse');

        # www page
        $Request->URI = new GenericURI('http://example.com');
        $Response     = $this->Engine->send($Request);

        $this->assertSame(200, $Response->Status, 'PhantomJS::send() returned an invalid IResponse');
        $this->assertContains('<h1>Example Domain</h1>', strval($Response->Body), 'PhantomJS::send() returned an invalid IResponse');

        # invalid page
        $Request->URI = new GenericURI('http://a.com');
        $Response     = $this->Engine->send($Request);

        $this->assertSame(200, $Response->Status, 'PhantomJS::send() returned an invalid IResponse');
        $this->assertContains('<html', strval($Response->Body), 'PhantomJS::send() returned an invalid IResponse');
    }

    /**
     * @depends test_phantom
     * @covers ::doPhantomCommand
     * @covers ::_sendJSON
     */
    public function test_doPhantomCommand()
    {
        # Valid args
        $Event = new Event($this->Browser, array('Command' => 'phantom.version;'));

        $this->Browser->_do('Phantom.Command', $Event);

        $this->assertInstanceOf('stdClass', $Event->Result, 'PhantomJS::doPhantomCommand() Did not modify $Event');
        $this->assertObjectHasAttribute('status', $Event->Result, 'PhantomJS::phantom() Did not modify $Event');

        # Invalid args
        try {
            $Event->Command = NULL;

            $this->Browser->_do('Phantom.Command', $Event);
            $this->fail('Failed to generate exception with invalid arguments');
        } catch (\RuntimeException $e) {}
    }

    /**
     * @depends test_send
     * @covers ::doPageDownload
     * @covers ::_sendJSON
     */
    public function test_doPageDownload()
    {
        $this->Browser->_on('go', array($this->Browser, 'doGo'));

        $this->Browser->go('about:blank');

        $this->assertInstanceOf('DOMNode', $this->Browser->filter('body')->offsetGet(0), 'PhantomJS.doPageDownload() Failed to update IBrowser');

        # Invalid request
        $called = 0;

        $this->Browser->_on('exception', function () use (&$called) {$called++;});

        $this->Engine->doPageDownload(new Event($this->Browser, array(
            'Request' => null
        )));

        $this->assertSame(1, $called, 'Failed to generate exception with invalid request');
    }

    /**
     * @covers ::getLastResult
     */
    public function test_getLastResult()
    {
        $this->assertNull($this->Engine->getLastResult(), 'PhantomJS.getLastResult() should be initially be NULL');
    }

    public function generateInvalidNodeArgs()
    {
        $Node = $this->Browser->filter('body')->offsetGet(0);

        return array(
             array()
            ,array($Node)
            ,array($Node, NULL)
            ,array(NULL, 'console.log(this);')
        );
    }
    /**
     * @depends test_phantom
     * @covers ::doEvaluateNode
     * @covers ::_sendJSON
     */
    public function test_doEvaluateNode()
    {
        $Node = $this->Browser->filter('body')->offsetGet(0);

        # Valid args
        $this->Browser->evaluateNode($Node, 'console.log(this);');
        $this->assertInstanceOf('stdClass', $this->Engine->getLastResult(), 'PhantomJS::doEvaluateNode() Returned an invalid result');
        $this->assertObjectHasAttribute('result', $this->Engine->getLastResult(), 'PhantomJS::doEvaluateNode() Returned an invalid result');
        $this->assertGreaterThanOrEqual(1, $this->Engine->getLastResult()->result, 'PhantomJS::doEvaluateNode() Returned an invalid result');

        # illegal JavaScript
        try {
            $this->Browser->evaluateNode($Node, '____undefined____');
            $this->fail('Failed to generate exception with invalid JavaScript');
        } catch (\RuntimeException $e) {}

        # Invalid arguments
        foreach ($this->generateInvalidNodeArgs() as $Arguments) {

            try {
                call_user_func_array(array($this->Browser, 'evaluateNode'), $Arguments);
                $this->fail('Failed to generate exception with invalid arguments');
            } catch (\RuntimeException $e) {}
        }
    }

    public function generateInvalidXPathArgs()
    {
        return array(
             array()
            ,array('/html/body')
            ,array('/html/body', NULL)
            ,array(NULL, 'console.log(this);')
        );
    }

    /**
     * @depends test_phantom
     * @covers ::doEvaluateXPath
     * @covers ::_sendJSON
     */
    public function test_doEvaluateXPath()
    {
        # Valid args
        $this->Browser->evaluateXPath('/html/body', 'console.log(this);');
        $this->assertInstanceOf('stdClass', $this->Engine->getLastResult(), 'PhantomJS::doEvaluateNode() Returned an invalid result');
        $this->assertObjectHasAttribute('result', $this->Engine->getLastResult(), 'PhantomJS::doEvaluateNode() Returned an invalid result');
        $this->assertGreaterThanOrEqual(1, $this->Engine->getLastResult()->result, 'PhantomJS::doEvaluateXPath() Returned an invalid result');

        # Illegal JavaScript
        try {
            $this->Browser->evaluateXPath('/html/body', '____undefined____');
            $this->fail('Failed to generate exception with invalid JavaScript');
        } catch (\RuntimeException $e) {}

        # Invalid xpath
        try {
            $this->Browser->evaluateXPath('\\html\\body', 'console.log(this);');
            $this->fail('Failed to generate exception with invalid XPath');
        } catch (\RuntimeException $e) {}

        # Invalid arguments
        foreach ($this->generateInvalidXPathArgs() as $Arguments) {

            try {
                call_user_func_array(array($this->Browser, 'evaluateXPath'), $Arguments);
                $this->fail('Failed to generate exception with invalid arguments');
            } catch (\RuntimeException $e) {}
        }
    }

    public function generateInvalidSelectorArgs()
    {
        return array(
             array()
            ,array('html > body')
            ,array('html > body', NULL)
            ,array(NULL, 'console.log(this);')
        );
    }

    /**
     * @depends test_phantom
     * @covers ::doEvaluateCSS
     * @covers ::_sendJSON
     */
    public function test_doEvaluateCSS()
    {
        # Valid args
        $this->Browser->evaluateCSS('html > body', 'console.log(this);');
        $this->assertInstanceOf('stdClass', $this->Engine->getLastResult(), 'PhantomJS::doEvaluateNode() Returned an invalid result');
        $this->assertObjectHasAttribute('result', $this->Engine->getLastResult(), 'PhantomJS::doEvaluateNode() Returned an invalid result');
        $this->assertGreaterThanOrEqual(1, $this->Engine->getLastResult()->result, 'PhantomJS::doEvaluateCSS() Returned an invalid result');

        # Illegal JavaScript
        try {
            $this->Browser->evaluateCSS('html > body', '____undefined____');
            $this->fail('Failed to generate exception with invalid JavaScript');
        } catch (\RuntimeException $e) {}

        # Invalid CSS Selector
        try {
            $this->Browser->evaluateCSS('html <> body', 'console.log(this);');
            $this->fail('Failed to generate exception with invalid Selector');
        } catch (\RuntimeException $e) {}

        # Invalid arguments
        foreach ($this->generateInvalidSelectorArgs() as $Arguments) {

            try {
                call_user_func_array(array($this->Browser, 'evaluateCSS'), $Arguments);
                $this->fail('Failed to generate exception with invalid arguments');
            } catch (\RuntimeException $e) {}
        }
    }

    /**
     * @depends test_doEvaluateNode
     * @depends test_doEvaluateXPath
     * @depends test_doEvaluateCSS
     * @covers ::wait
     * @covers ::_sendJSON
     */
    public function test_wait()
    {
        $this->Browser->evaluateCSS('html', "window.location.href = 'http://example.com';");
        $Response = $this->Engine->wait();

        $this->assertSame(200, $Response->Status, 'PhantomJS::send() returned an invalid IResponse');
        $this->assertContains('<h1>Example Domain</h1>', strval($Response->Body), 'PhantomJS::wait() returned an invalid IResponse');
    }

    /**
     * @depends test_wait
     * @covers ::doWait
     * @covers ::_sendJSON
     */
    public function test_doWait()
    {
        $this->Browser->evaluateCSS('html', "window.location.href = 'http://example.com';");
        $this->Browser->wait();

        $this->assertEquals('Example Domain', $this->Browser->filter('h1')->offsetGet(0)->textContent, 'PhantomJS::wait() returned an invalid IResponse');
    }
}
