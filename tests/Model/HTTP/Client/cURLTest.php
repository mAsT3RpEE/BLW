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
namespace BLW\Tests\Model\HTTP\Client;

use ReflectionProperty;
use ReflectionMethod;

use BLW\Type\HTTP\IClient;

use BLW\Model\GenericURI;
use BLW\Model\Stream\Handle as ResourceStream;
use BLW\Model\Mediator\Symfony as Mediator;

use BLW\Model\HTTP\Client\cURL as Client;
use BLW\Model\HTTP\Request\Generic as Request;
use BLW\Model\Mediator\Symfony;


/**
 * Test for cURL HTTP client class
 * @package BLW\HTTP
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\HTTP\Client\cURL
 */
class cURLTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\HTTP\IRequest
     */
    protected $Request = NULL;

    /**
     * @var \BLW\Model\HTTP\Client\cURL
     */
    protected $Client = NULL;

    protected function setUp()
    {
        $this->Client       = new Client(IClient::COOKIE_DEFAULT, NULL, '', 4, 16, 60);
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
     * @covers ::run
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

        $this->assertEquals(10, $this->Client->run(), 'IClient::run() Should return 1');

        $this->assertStringStartsWith('<!doctype html>', strval($this->Client[$this->Request]->getBody()), 'IClient::download() Failed to download message body');
        $this->assertContains('Example Domain', strval($this->Client[$this->Request]->getBody()), 'IClient::download() Failed to download message body');

        $this->assertSame(9, $New, 'IClient Failed to trigger Request.New event');
        $this->assertSame(10, $Finished, 'IClient Failed to trigger Request.Finished event');
        $this->assertGreaterThan(10, $Updated, 'IClient Failed to trigger Request.Updated event');
    }

    /**
     * @covers ::download
     */
    public function test_download()
    {
        $Stream = new ResourceStream(fopen('php://memory', 'w+'));

        $this->assertTrue($this->Client->download($this->Request, $Stream), 'IClient::download() Should return true');

        $this->assertStringStartsWith('<!doctype html>', strval($Stream), 'IClient::download() Failed to download message body');
        $this->assertContains('Example Domain', strval($Stream), 'IClient::download() Failed to download message body');

        unset($Stream);
    }
}