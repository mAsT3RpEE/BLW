<?php
/**
 * cURL.php | Apr 12, 2014
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
namespace BLW\Model\HTTP\Client;

use ReflectionProperty;
use ReflectionMethod;

use BLW\Type\HTTP\IClient;

use BLW\Model\GenericURI;
use BLW\Model\Stream\Handle as ResourceStream;
use BLW\Model\Mediator\Symfony as Mediator;
use BLW\Model\Config\Generic as Config;

use BLW\Model\HTTP\Client\CURL as Client;
use BLW\Model\HTTP\Request\Generic as Request;
use BLW\Model\HTTP\RequestFactory;
use BLW\Model\HTTP\Browser\Nexus;
use BLW\Model\MIME\Referer;
use BLW\Model\InvalidArgumentException;


/**
 * Test for cURL HTTP client class
 * @package BLW\HTTP
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\HTTP\Client\CURL
 */
class cURLTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\HTTP\IRequest
     */
    protected $Request = NULL;

    /**
     * @var \BLW\Model\HTTP\Client\CURL
     */
    protected $Client = NULL;

    protected function setUp()
    {
        $this->Client       = new Client(IClient::COOKIE_DEFAULT, NULL, '', 4, 16, 8);
        $this->Request      = new Request;
        $this->Request->URI = new GenericURI('http://example.com:80/');

        $this->Client->setMediator(new Mediator);
        $this->Client->send($this->Request);
    }

    protected function tearDown()
    {
        $this->Client  = NULL;
        $this->Request = NULL;
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        // Check properties
        $this->assertAttributeSame(IClient::COOKIE_DEFAULT, '_CookieFormat', $this->Client, 'cURL::__construct() Failed to set $_CookieFormat');
        $this->assertAttributeInstanceOf('\\BLW\\Type\\IFile', '_TempDir', $this->Client, 'cURL::__construct() Failed to set $_TempDir');
        $this->assertAttributeInternalType('string', '_Proxy', $this->Client, 'cURL::__construct() Failed to set $_Proxy');
        $this->assertAttributeInternalType('int', '_MaxConnections', $this->Client, 'cURL::__construct() Failed to set $_MaxConnections');
        $this->assertAttributeInternalType('int', '_MaxRequests', $this->Client, 'cURL::__construct() Failed to set $_MaxRequests');
        $this->assertAttributeInternalType('int', '_MaxRate', $this->Client, 'cURL::__construct() Failed to set $_MaxRate');
    }

    /**
     * @covers ::translate
     * @covers ::_translateHeaders
     * @covers ::_translateURI
     * @covers ::_translateReferer
     */
    public function test_translate()
    {
        $Expected = array(
            CURLOPT_CUSTOMREQUEST => 'POST',                            // Request type (GET | POST | ETC)
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,              // Request protoccol

            CURLOPT_CONNECTTIMEOUT => 30,                               // Connection timeout
            CURLOPT_MAXREDIRS => 10,                                    // Maximum number of redirects
            CURLOPT_RETURNTRANSFER => true,                             // Return transfer
            CURLOPT_HEADER => true,                                     // Return header
            CURLOPT_HTTPHEADER => array(
                'Accept: text/html, application/xhtml+xml, application/xml;q=0.9, image/webp, */*;q=0.8',
                'Accept-Language: en-us, en;q=0.5',
                'Cache-Control: max-age=0',
                'Content-Type: application/x-www-form-urlencoded',
            ),
            CURLOPT_ENCODING => '',                                     // Accept-Encoding
            CURLOPT_AUTOREFERER => true,                                // Automatically update 'Referer:'
            CURLOPT_FOLLOWLOCATION => true,                             // Follow 'Location:' headers
            CURLOPT_SSL_VERIFYPEER => 1,                                // Verify certs
            CURLOPT_SSL_VERIFYHOST => 2,                                // Verify hosts
            CURLOPT_URL => 'http://example.com',                        // Target URI
            CURLOPT_PORT => 1000,
            CURLOPT_USERPWD => 'user:pass',
            CURLOPT_REFERER => 'about:nothing',
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Linux; Android 4.3; Nexus 7 Build/JSS15Q) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.72 Safari/537.36',
            CURLOPT_POSTFIELDS => 'foo=bar',
            CURLOPT_COOKIEFILE => $this->Client->createCookieFile()->getPathname(),
            CURLOPT_COOKIEJAR => $this->Client->createCookieFile()->getPathname(),
        );
        $Factory  = new RequestFactory;
        $Browser  = new Nexus($this->Client, new Config(array('MaxHistory' => 10)));
        $Request  = $Factory->createPOST(new GenericURI('http://user:pass@example.com:1000'), new GenericURI('about:nothing'), array('foo' => 'bar'), $Browser->createHeaders());

        $this->Client->send($Request);

        $this->assertEquals($Expected, $this->Client->translate($Request), 'cURL::translate() Returned an invalid value');

        unset($Request->Referer);

        $Request->getHeader()->offsetSet('Referer', new Referer(new GenericURI('about:nothing')));

        $this->assertEquals($Expected, $this->Client->translate($Request), 'cURL::translate() Returned an invalid value');

        $Request->getHeader()->offsetUnset('Referer');
        unset($Expected[CURLOPT_REFERER]);

        $this->assertEquals($Expected, $this->Client->translate($Request), 'cURL::translate() Returned an invalid value');

        # Inalid request
        $this->assertSame(array(), $this->Client->translate($Factory->createGET(new GenericURI('about:nothing'), new GenericURI('about:nothing'))), 'cURL::translate() Returned an invalid value');
    }

    /**
     * @covers ::run
     * @covers ::schedule
     * @covers ::processMessages
     * @covers ::update
     * @covers ::_start
     * @covers ::_lock
     * @covers ::_finish
     * @covers ::_update
     */
    public function test_run()
    {
        $New      = 0;
        $Finished = 0;
        $Updated  = 0;

        $this->Client->_on('Request.New', function() use (&$New) {$New++;});
        $this->Client->_on('Request.Finished', function() use (&$Finished) {$Finished++;});
        $this->Client->_on('Request.Update', function() use (&$Updated) {$Updated++;});

        for($i=0; $i<9; $i++) $this->Client->send(clone $this->Request);

        $this->assertEquals(10, $this->Client->run(), 'cURL::run() Should return 1');

        $this->assertStringStartsWith('<!doctype html>', strval($this->Client[$this->Request]->getBody()), 'cURL::download() Failed to download message body');
        $this->assertContains('Example Domain', strval($this->Client[$this->Request]->getBody()), 'cURL::download() Failed to download message body');

        $this->assertSame(9, $New, 'cURL Failed to trigger Request.New event');
        $this->assertSame(10, $Finished, 'cURL Failed to trigger Request.Finished event');
        $this->assertGreaterThan(10, $Updated, 'cURL Failed to trigger Request.Updated event');

        # Invalid arguments
        try {
            $this->Client->run(null);
            $this->fail('Failed to generate exception with invalid arguments');

        } catch (InvalidArgumentException $e) {

        }
    }

    /**
     * @covers ::download
     */
    public function test_download()
    {
        $Stream = new ResourceStream(fopen('php://memory', 'w+'));

        $this->assertTrue($this->Client->download($this->Request, $Stream), 'cURL::download() Should return true');

        $this->assertStringStartsWith('<!doctype html>', strval($Stream), 'cURL::download() Failed to download message body');
        $this->assertContains('Example Domain', strval($Stream), 'cURL::download() Failed to download message body');

        unset($Stream);
    }

    /**
     * @covers ::update
     * @covers ::_reschedule
     */
    public function test_reschedule()
    {
        $Response            = $this->Client[$this->Request];
        $Response['Running'] = true;

        $this->Client->update();

        $this->assertFalse($this->Client[$this->Request]['Running'], 'cURL::update() Failed to reschedule request');
    }

    /**
     * @covers ::_findByHandle
     */
    public function test_findByHandle()
    {
        $Method             = new ReflectionMethod($this->Client, '_findByHandle');
        $Response           = $this->Client[$this->Request];
        $Response['handle'] = 1;

        $Method->setAccessible(true);

        $this->assertSame($this->Request, $Method->invoke($this->Client, 1), 'cURL::_findByHandle() should return 1st Request');
        $this->assertNotSame($this->Request, $Method->invoke($this->Client, 100), 'cURL::_findByHandle() should return new Request');
    }
}