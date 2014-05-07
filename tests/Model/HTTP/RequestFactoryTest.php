<?php
/**
 * RequestFactoryTest.php | Apr 14, 2014
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
namespace BLW\Tests\Model\HTTP;

use ReflectionProperty;
use ReflectionMethod;

use BLW\Model\InvalidArgumentException;

use BLW\Model\HTTP\RequestFactory;
use BLW\Model\HTTP\Request\Generic as Request;
use BLW\Model\GenericContainer;
use BLW\Model\MIME\Generic;
use BLW\Model\MIME\AcceptCharset;
use BLW\Model\MIME\AcceptEncoding;
use BLW\Model\MIME\AcceptLanguage;
use BLW\Model\MIME\Accept;
use BLW\Model\GenericURI;
use BLW\Type\HTTP\IRequest;
use BLW\Model\GenericFile;
use BLW\Model\MIME\Part\FormField;


/**
 * Test for Request Factory object
 * @package BLW\HTTP
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\HTTP\RequestFactory
 */
class RequestFactoryTest  extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\HTTP\RequestFactory
     */
    protected $Factory = NULL;

    /**
     * @var \BLW\Type\IContainer
     */
    protected $Headers = array();

    protected function setUp()
    {
        $this->Factory = new RequestFactory;

        $this->Headers   = new GenericContainer('\\BLW\\Type\\MIME\\IHeader');
        $this->Headers[] = new Generic('User-Agent', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:28.0) Gecko/20100101 Firefox/28.0');
        $this->Headers[] = new Accept('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');
        $this->Headers[] = new AcceptLanguage('en-US,en;q=0.5');
        $this->Headers[] = new AcceptEncoding('gzip, deflate');
        $this->Headers[] = new Generic('Test', 'foo');
    }

    protected function tearDown()
    {
        $this->Factory = NULL;
    }

    public function generateInvalidArgs()
    {
        return array(
        	 array('UndefinedClass')
            ,array('stdClass')
            ,array(array(1))
            ,array(new \stdClass)
            ,array(new Request)
        );
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        $Expected = '\\BLW\\Model\\HTTP\\Request\\Generic';
        $Property = new ReflectionProperty($this->Factory, '_RequestClass');

        $Property->setAccessible(true);

        # Valid arguments
        $this->Factory = new RequestFactory($Expected);

        $this->assertEquals($Expected, $Property->getValue($this->Factory), 'IFactory::__construct() Failed to set $_RequestClass');

        for($args=$this->generateInvalidArgs();list($k,list($Input)) = each($args);) {

            try {
                new RequestFactory($Input);
                $this->fail('Failed to generate exception with invalid arguments');
            }

            catch (InvalidArgumentException $e) {}
        }
    }

    /**
     * @covers ::getFactoryMethods
     */
    public function test_getFactoryMethods()
    {
        $this->assertGreaterThan(2, $this->Factory->getFactoryMethods(), 'IFactory::getFactoryMethods() Returned an invalid value');
    }

    public function generateInvalidGET()
    {
        return array(
        	 array(new GenericURI('http://foo.com'), new GenericURI('about:nothing'), NULL)    // Invalid Headers
        	,array(new GenericURI(''), new GenericURI('about:nothing'), array())               // Invalid URI
//        	,array(new GenericURI('about:foo'), new GenericURI('about:nothing'), NULL)         // Relative URI
        );
    }

    /**
     * @covers ::createGET
     */
    public function test_createGet()
    {
        $Expected = <<<EOT
User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:28.0) Gecko/20100101 Firefox/28.0\r\nAccept: text/html, application/xhtml+xml, application/xml;q=0.9, */*;q=0.8\r\nAccept-Language: en-us, en;q=0.5\r\nAccept-Encoding: gzip, deflate\r\nTest: foo\r\n\r\n
EOT;

        # Valid Arguments
        $URI     = new GenericURI('http://example.com');
        $Request = $this->Factory->createGET($URI, $URI, $this->Headers);

        $this->assertEquals($URI, $Request->URI, 'IFactory::createGET() Failed to set request URI');
        $this->assertEquals(IRequest::GET, $Request->Type, 'IFactory::createGET() Failed to set request Type');
        $this->assertEquals($Expected, strval($Request), 'IFactory::createGET() Produced an invalid request');

        # Invalid arguments
        for($args=$this->generateInvalidGET(); list($k,list($URI,$BaseURI,$Headers)) = each($args);) {

            try {
                $this->Factory->createGET($URI, $BaseURI, $Headers);
                $this->fail('Failed to generate exception with invalid arguments');
            }

            catch (InvalidArgumentException $e) {}
        }
    }

    /**
     * @covers ::createHEAD
     */
    public function test_createHEAD()
    {
        $Expected = <<<EOT
User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:28.0) Gecko/20100101 Firefox/28.0\r\nAccept: text/html, application/xhtml+xml, application/xml;q=0.9, */*;q=0.8\r\nAccept-Language: en-us, en;q=0.5\r\nAccept-Encoding: gzip, deflate\r\nTest: foo\r\n\r\n
EOT;

        # Valid Arguments
        $URI     = new GenericURI('http://example.com');
        $Request = $this->Factory->createHEAD($URI, $URI, $this->Headers);

        $this->assertEquals($URI, $Request->URI, 'IFactory::createHEAD() Failed to set request URI');
        $this->assertEquals(IRequest::HEAD, $Request->Type, 'IFactory::createHEAD() Failed to set request Type');
        $this->assertEquals($Expected, strval($Request), 'IFactory::createHEAD() Produced an invalid request');

        # Invalid Arguments
        for($args=$this->generateInvalidGET(); list($k,list($URI,$BaseURI,$Headers)) = each($args);) {

            try {
                $this->Factory->createHEAD($URI, $BaseURI, $Headers);
                $this->fail('Failed to generate exception with invalid arguments');
            }

            catch (InvalidArgumentException $e) {}
        }
    }

    public function generateInvalidPOST()
    {
        return array(
        	 array(new GenericURI('http://foo.com'), new GenericURI('about:nothing'), array(), NULL)   // Invalid Headers
        	,array(new GenericURI(''), new GenericURI('about:nothing'), array(), $this->Headers)       // Invalid URI
//        	,array(new GenericURI('about:foo'), new GenericURI('about:nothing'), $this->Headers)       // Relative URI
        	,array(new GenericURI(''), new GenericURI('about:nothing'), NULL,  $this->Headers)         // Invalid Data
        );
    }

    /**
     * @covers ::createPOST
     */
    public function test_createPOST()
    {
        # Valid arguments
        $URI   = new GenericURI('http://example.com');
        $Image = new GenericFile(dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . '1x1.png');
        $Data  = array(
             'int'      => 1
            ,'float'    => 1.1
            ,'string'   => 'foo'
            ,'array'    => array(1,2)
            ,'object1'  => new \stdClass
            ,'object2'  => new \SplFileInfo(sys_get_temp_dir())
            ,'file'     => $Image
            ,'Field'    => new FormField('field', 'text/plain', 'foo')
        );

        # File multipart/form-data
        $Request     = $this->Factory->createPOST($URI, $URI, $Data, $this->Headers);
        $File = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'request-multipart.form-data.txt';
        $Boundary = preg_match('!boundary\s*=\s*"(.*)"!', $Request->Header['Content-Type'], $m)
            ? $m[1]
            : '';
        $Expected    = sprintf(preg_replace('!\r*\n!', "\r\n", file_get_contents($File)), $Boundary, sys_get_temp_dir(), file_get_contents($Image));

        // file_put_contents($File, str_replace(array(sys_get_temp_dir(), file_get_contents($Image), $Boundary), array('%2$s', '%3$s', '%1$s'), strval($Request)));

        $this->assertEquals($URI, $Request->URI, 'IFactory::createPOST() Failed to set request URI');
        $this->assertEquals(IRequest::POST, $Request->Type, 'IFactory::createPOST() Failed to set request Type');

        $this->assertEquals($Expected, strval($Request), 'IFactory::createPOST() Produced an invalid request');

        # No file application/x-www-form-urlencoded
        unset($Data['file']);

        $Request     = $this->Factory->createPOST($URI, $URI, $Data, $this->Headers);
        $File        = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'request-application.x-www-form-urlencoded.txt';
        $Boundary    = preg_match('!boundary\s*=\s*"(.*)"!', $Request->Header['Content-Type'], $m)
            ? $m[1]
            : '';
        $Expected    = file_get_contents($File);

        // file_put_contents($File, str_replace($Boundary, '%1$s', strval($Request)));

        # Invalid Arguments
        for($args=$this->generateInvalidPOST(); list($k,list($URI,$BaseURI,$Data, $Headers)) = each($args);) {

            try {
                $this->Factory->createPOST($URI, $BaseURI, $Data, $Headers);
                $this->fail('Failed to generate exception with invalid arguments');
            }

            catch (InvalidArgumentException $e) {}
        }
    }
}