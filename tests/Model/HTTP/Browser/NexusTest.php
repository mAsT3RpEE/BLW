<?php
/**
 * NexusTest.php | Apr 19, 2014
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
namespace BLW\Model\HTTP\Browser;

use ReflectionProperty;
use ReflectionMethod;

use BLW\Model\HTTP\Client\Mock as Client;
use BLW\Model\HTTP\Browser\Nexus as Browser;
use BLW\Model\Config\Generic as Config;

use Psr\Log\NullLogger;


/**
 * Test for Nexus HTTP Browser
 * @package BLW\HTTP
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\HTTP\Browser\Nexus
 */
class NexusTest  extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\HTTP\IClient
     */
    protected $Client = NULL;

    /**
     * @var \BLW\Model\HTTP\Browser\Nexus
     */
    protected $Browser = NULL;

    protected function setUp()
    {
        $Config        = new Config(array('MaxHistory' => 10));
        $this->Client  = new Client;
        $this->Browser = new Browser($this->Client, $Config, new NullLogger);
    }

    protected function tearDown()
    {
        $this->Browser = NULL;
        $this->Client  = NULL;
    }

    /**
     * @covers ::getSubscribedEvents
     */
    public function test_getSubscribedEvents()
    {
        $this->assertCount(8, $this->Browser->getSubscribedEvents(), 'IBrowser::getSubscribedEvents() Returned an invalid value');
    }

    /**
     * @coverst ::__construct
     */
    public function test_construct()
    {
        $Config        = new Config;
        $Logger        = new NullLogger;
        $this->Browser = new Browser($this->Client, $Config, $Logger);

        $this->assertAttributeSame($Logger, 'logger', $this->Browser, 'Nexus::__construct() Failed to set $logger');
        $this->assertAttributeInstanceOf('\\BLW\\Type\\IContainer', '_History', $this->Browser, 'Nexus::__construct() Failed to set $_History');
        $this->assertAttributeInstanceOf('\\BLW\\Type\\IMediator', '_Mediator', $this->Browser, 'Nexus::__construct() Failed to set $_Mediator');
        $this->assertAttributeInstanceOf('\\BLW\\Type\\IContainer', '_Engines', $this->Browser, 'Nexus::__construct() Failed to set $_Engines');
        $this->assertAttributeInstanceOf('\\BLW\\Type\\IContainer', '_Plugins', $this->Browser, 'Nexus::__construct() Failed to set $_Plugins');
        $this->assertAttributeInstanceOf('\\BLW\\Type\\HTTP\\Browser\\IPage', '_Component', $this->Browser, 'Nexus::__construct() Failed to set $_Component');
    }

    /**
     * @covers ::createHeaders
     */
    public function test_createHeaders()
    {
        $Called = 0;

        $this->Browser->_on('Headers', function() use(&$Called) {$Called++;});

        $Headers = $this->Browser->createHeaders();

        $this->assertArrayHasKey('User-Agent', $Headers, 'Nexus::getHeaders() Failed to set UserAgent');
        $this->assertContainsOnlyInstancesOf('\\BLW\\Type\\MIME\\IHeader', $Headers, 'Nexus::getHeaders() Returned an invalid value');
        $this->assertSame(1, $Called, 'Nexus::getHeaders() Failed to trigger Headers event');
    }

    /**
     * @coversNothing
     */
    public function test_basic()
    {
        $this->assertTrue($this->Browser->go('http://a'), 'IBrowser::go() Should return true');
        $this->assertTrue($this->Browser->go('http://a/b'), 'IBrowser::go() Should return true');
        $this->assertTrue($this->Browser->go('http://a/b/c'), 'IBrowser::go() Should return true');
        $this->assertTrue($this->Browser->go('http://a/b/c/d?q#f'), 'IBrowser::go() Should return true');

        $this->assertEquals('http://a/b/c/d?q=#f', strval($this->Browser->Base), 'IBrowser::$Base Should equal `http://a/b/c/d?q#f`');
        $this->assertEquals('Heading', $this->Browser->filter('h1')->offsetGet(0)->textContent, 'IBrowser::go() Failed to set page');
        $this->assertTrue($this->Browser->back(), 'IBrowser::back() Should return true');
        $this->assertEquals('http://a/b/c', strval($this->Browser->Base), 'IBrowser::$Base Should equal `http://a/b/c/d?q#f`');
        $this->assertEquals('Heading', $this->Browser->filter('h1')->offsetGet(0)->textContent, 'IBrowser::go() Failed to set page');
        $this->assertTrue($this->Browser->back(), 'IBrowser::back() Should return true');
        $this->assertEquals('http://a/b', strval($this->Browser->Base), 'IBrowser::$Base Should equal `http://a/b/c/d?q#f`');
        $this->assertEquals('Heading', $this->Browser->filter('h1')->offsetGet(0)->textContent, 'IBrowser::go() Failed to set page');
        $this->assertTrue($this->Browser->forward(), 'IBrowser::back() Should return true');
        $this->assertEquals('http://a/b/c', strval($this->Browser->Base), 'IBrowser::$Base Should equal `http://a/b/c/d?q#f`');
        $this->assertEquals('Heading', $this->Browser->filter('h1')->offsetGet(0)->textContent, 'IBrowser::go() Failed to set page');
    }
}