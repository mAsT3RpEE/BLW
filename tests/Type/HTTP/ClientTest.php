<?php
/**
 * ClientTest.php | Apr 11, 2014
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

use ReflectionMethod;

use BLW\Model\InvalidArgumentException;

use BLW\Model\Mediator\Symfony as Mediator;
use BLW\Model\HTTP\Request\Generic as Request;
use BLW\Model\HTTP\Response\Generic as Response;
use BLW\Model\GenericURI;
use BLW\Model\GenericFile;


/**
 * Test for base HTTP client class
 * @package BLW\HTTP
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\HTTP\AClient
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\HTTP\IRequest
     */
    protected $Request = NULL;

    /**
     * @var \BLW\Type\HTTP\IResponse
     */
    protected $Response = NULL;

    /**
     * @var \BLW\Type\HTTP\AClient
     */
    protected $Client = NULL;

    protected function setUp()
    {
        # Valid values
        $this->Request  = new Request;
        $this->Response = new Response;
        $this->Client   = $this->getMockForAbstractClass('\\BLW\\Type\\HTTP\\AClient');

        $this->Client->setMediator(new Mediator);

        $this->Request->setURI(new GenericURI('http://example.com'));

        $this->Client->attach($this->Request, $this->Response);
    }

    protected function tearDown()
    {
        $this->Client   = NULL;
        $this->Request  = NULL;
        $this->Response = NULL;
    }

    public function generateInvalidRequests()
    {
        // No timeout
        $NoTimeout        = new Request;
        $NoTimeout->URI   = new GenericURI('http://example.com');

        unset($NoTimeout->Config['Timeout']);

        // No redirects
        $NoRedirects      = new Request;
        $NoRedirects->URI = new GenericURI('http://example.com');

        unset($NoRedirects->Config['MaxRedirects']);

        // No cookies
        $NoCookies        = new Request;
        $NoCookies->URI   = new GenericURI('http://example.com');

        unset($NoCookies->Config['EnableCookies']);

        return array(
        	 array(new Request)
            ,array($NoTimeout)
            ,array($NoRedirects)
            ,array($NoCookies)
            ,array('foo')
            ,array(false)
            ,array(NULL)
            ,array(array())
            ,array(new \stdClass)
        );
    }

    /**
     * @covers ::validateRequest
     */
    public function test_validateRequest()
    {
        # Valid arguments
        $this->assertTrue($this->Client->validateRequest($this->Request), 'IClient::validateRequest() Should return true');

        # Invalid arguments
        foreach ($this->generateInvalidRequests() as $Arguments) {

            list($Request) = $Arguments;

            $this->assertFalse($this->Client->validateRequest($Request), 'IClient::validateRequest() Should return false');
        }
    }

    public function generateInvalid()
    {
        $Request      = new Request;
        $Request->URI = new GenericURI('http://example.com');

        return array(
             array(new Request, new Response)
            ,array('foo', new Response)
            ,array(false, new Response)
            ,array(array(), new Response)
            ,array(new \stdClass, new Response)
            ,array($Request, 'foo')
            ,array($Request, false)
            ,array($Request, array())
            ,array($Request, new \stdClass)
        );
    }

    /**
     * @depends test_validateRequest
     * @covers ::attach
     */
    public function test_attach()
    {
        $Called   = 0;

        $this->Client->_on('Request.New', function() use(&$Called)
        {
            $Called++;
        });

        # Valid arguments
        $Request      = new Request;
        $Response     = new Response;
        $Request->URI = new GenericURI('http://example.com');

        $this->assertCount(1, $this->Client, 'IClient should have 1 request');

        $this->Client->attach($Request, $Response);

        $this->assertCount(2, $this->Client, 'IClient::attach() Failed to que request');
        $this->assertSame(1, $Called, 'IClient::attach() Failed to generate `Request.New` event');

        # Invalid arguments
        foreach ($this->generateInvalid() as $Arguments) {

            list($Request, $Response) = $Arguments;

            try {
                $this->Client->attach($Request, $Response);
                $this->fail('Failed to generate exception with invalid arguments');
            }

            catch (InvalidArgumentException $e) {}
        }
    }

    /**
     * @depends test_attach
     * @covers ::detach
     */
    public function test_detach()
    {
        $Called = 0;

        $this->Client->_on('Request.Remove', function() use(&$Called)
        {
            $Called++;
        });

        # Valid arguments
        $this->Client->detach($this->Request);
        $this->assertCount(0, $this->Client, 'IClient should be empty');
        $this->assertSame(1, $Called, 'IClient::detach() Failed to generate `Request.Remove` Event');

        $this->Client->detach($this->Request);
        $this->assertCount(0, $this->Client, 'IClient should be empty');

        # Invalid arguments
        $this->Client->attach($this->Request, $this->Response);
        $this->Client->detach(new \stdClass);
        $this->assertCount(1, $this->Client, 'IClient should have 1 request');
    }

    /**
     * @covers ::getFactoryMethods
     */
    public function test_getFactoryMethods()
    {
        $this->assertNotEmpty($this->Client->getFactoryMethods(), 'IClient::getFactoryMethods() Returned an invalid value');
        $this->assertContainsOnlyInstancesOf('ReflectionMethod', $this->Client->getFactoryMethods(), 'IClient::getFactoryMethods() Returned an invalid value');
    }

    public function generateValidTempDirs()
    {
        $Tempfile = NULL;

        foreach(scandir(sys_get_temp_dir()) as $File) {
            $File = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $File;

            if (is_file($File) && !is_dir($File)) $Tempfile = $File;
        }

        $Tempfile =  $Tempfile ?: tempnam(sys_get_temp_dir(), 'foo');

        return array(
        	 array(NULL)
            ,array(new GenericFile(sys_get_temp_dir()))
            ,array(new GenericFile($Tempfile))
        );
    }

    public function generateInvalidTempDirs()
    {
        return array(
        	 array(dirname(__FILE__))
            ,array(false)
            ,array(array())
            ,array(new \stdClass)
            ,array(new GenericFile(DIRECTORY_SEPARATOR == '\\'? 'C:\\Windows' : '/etc'))
        );
    }

    /**
     * @covers ::createCookieFile
     */
    public function test_createCookieFile()
    {
        # Valid Arguments
        foreach ($this->generateValidTempDirs() as $Arguments) {

            list($TempDir) = $Arguments;

            $this->assertInstanceOf('\\BLW\\Type\\IFile', $this->Client->createCookieFile($TempDir), 'IClient::createCookieFile() Failed to create a cookie file');
        }

        # Invalid arguments
        foreach ($this->generateInvalidTempDirs() as $Arguments) {

            list($TempDir) = $Arguments;

            try {
                $this->Client->createCookieFile($TempDir);
                $this->fail('Failed to genereate notice with invalid arguments');
            }

            catch (InvalidArgumentException $e) {}
            catch (\PHPUnit_Framework_Error $e) {}
        }
    }

    /**
     * @covers ::send
     */
    public function test_send()
    {
        # Valid arguments
        $Request  = new Request;

        $Request->setURI(new GenericURI('http://example.com'));

        $this->assertTrue($this->Client->send($Request), 'IClient::send() should return true.');
        $this->assertFalse($this->Client->send($Request), 'IClient::send() should return false.');
        $this->assertFalse($this->Client->send($this->Request), 'IClient::send() should return false.');
        $this->assertCount(2, $this->Client, 'IClient should have 2 requests');

        # Invalid arguments
        foreach ($this->generateInvalidRequests() as $Arguments) {

            list($Request) = $Arguments;

            try {
                $this->Client->send($Request);
                $this->fail('Failed to genereate notice with invalid arguments');
            }

            catch (InvalidArgumentException $e) {}
            catch (\PHPUnit_Framework_Error $e) {}
        }
    }

    /**
     * @depends test_send
     * @covers ::sendAll
     */
    public function test_sendAll()
    {
        # Valid arguments
        $Request  = new Request;

        $Request->setURI(new GenericURI('http://example.com'));

        $Array       = array(clone $Request, clone $Request, clone $Request);
        $Traversable = new \ArrayObject(array(clone $Request, clone $Request, clone $Request));

        $this->assertEquals(3, $this->Client->sendAll($Array), 'IClient::sendAll() Should return 5');
        $this->assertCount(4, $this->Client, 'IClient Should contain 4 items');
        $this->assertEquals(0, $this->Client->sendAll($Array), 'IClient::sendAll() Should return 0');
        $this->assertCount(4, $this->Client, 'IClient Should contain 4 items');

        $this->assertEquals(3, $this->Client->sendAll($Traversable), 'IClient::sendAll() Should return 4');
        $this->assertCount(7, $this->Client, 'IClient Should contain 7 items');
        $this->assertEquals(0, $this->Client->sendAll($Traversable), 'IClient::sendAll() Should return 0');
        $this->assertCount(7, $this->Client, 'IClient Should contain 7 items');

        # Invalid arguments
        $Array = array(clone $Request, clone $Request, new Request);

        try {
            $this->Client->sendAll($Array);
            $this->fail('Failed to generate exception with invalid arguments');
        }

        catch (InvalidArgumentException $e) {}

        $this->assertCount(7, $this->Client, 'IClient Should contain 7 items');

        try {
            $this->Client->sendAll(NULL);
            $this->fail('Failed to generate exception with invalid arguments');
        }

        catch (InvalidArgumentException $e) {}

        $this->assertCount(7, $this->Client, 'IClient Should contain 7 items');
    }

    /**
     * @covers ::countScheduled
     */
    public function test_countScheduled()
    {
        $this->assertEquals(1, $this->Client->countScheduled(), 'IClient::countScheduled() should return 1');
        $this->Client[$this->Request]['Finished'] = true;
        $this->assertEquals(0, $this->Client->countScheduled(), 'IClient::countScheduled() should return 0');
    }

    /**
     * @covers ::countFinished
     */
    public function test_countFinished()
    {
        $this->assertEquals(0, $this->Client->countFinished(), 'IClient::countFinished() should return 0');
        $this->Client[$this->Request]['Finished'] = true;
        $this->assertEquals(1, $this->Client->countFinished(), 'IClient::countFinished() should return 1');
    }

    /**
     * @covers ::countRunning
     */
    public function test_countRunning()
    {
        $this->assertEquals(0, $this->Client->countRunning(), 'IClient::countRunning() should return 0');
        $this->Client[$this->Request]['Running'] = true;
        $this->assertEquals(1, $this->Client->countRunning(), 'IClient::countRunning() should return 1');
    }
}