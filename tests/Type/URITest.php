<?php
/**
 * URITest.php | Jan 26, 2014
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

use ReflectionMethod;
use BLW\Model\InvalidArgumentException;


/**
 * Tests BLW Library Iterator type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\IURI
 */
class URITest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\IURI $URI
     */
    protected $URI = NULL;

    public function setUp()
    {
        $this->URI = $this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('http://a/b/c/d;p?q#f'));

    }

    public function tearDown()
    {
        $this->URI = NULL;
    }

    /**
     * @covers ::getFactoryMethods
     */
    public function test_getFactoryMethods()
    {
        $Expected = array(
        	new ReflectionMethod($this->URI, 'createURIString')
        );

        $this->assertEquals($Expected, $this->URI->getFactoryMethods(), 'IURI::getFactoryMethods() returned invalid method list');
    }

    public function generateParts()
    {
        return array(
             array(array(), '')
        	,array(array('scheme' => 'http', 'host' => 'www.example.com', 'userinfo' => 'user:pass', 'query' => array('foo' => 'bar'), 'fragment' => 'fragment'), 'http://user:pass@www.example.com?foo=bar#fragment')
            ,array(parse_url('http://user:pass@www.example.com?foo=bar#fragment'), 'http://user:pass@www.example.com?foo=bar#fragment')
            ,array(parse_url('http://user@www.example.com?foo=bar#fragment'), 'http://user@www.example.com?foo=bar#fragment')
            ,array(parse_url('http://www.example.com?foo=bar#fragment') + array('pass' => 'pass'), 'http://anonymous:pass@www.example.com?foo=bar#fragment')
        );
    }

    /**
     * @dataProvider generateParts
     * @covers ::createURIString
     */
    public function test_createURIString($Input, $Expected)
    {
        $this->assertSame($Expected, $this->URI->createURIString($Input), 'IURI::createURIString() returned and invalid value');
    }

    public function generateValidURIs()
    {
        return array(
             array('http://www.example.com', 'http://www.example.com')
        	,array('ftp://ftp.is.co.za/rfc/rfc1808.txt', 'ftp://ftp.is.co.za/rfc/rfc1808.txt')
            ,array('http://www.ietf.org/rfc/rfc2396.txt', 'http://www.ietf.org/rfc/rfc2396.txt')
            ,array('ldap://[2001:db8::7]/c=GB?objectClass?one', 'ldap://[2001:db8::7]/c=GB?objectClass%3Fone=')
            ,array('mailto:John.Doe@example.com', 'mailto:John.Doe@example.com')
            ,array('news:comp.infosystems.www.servers.unix', 'news:comp.infosystems.www.servers.unix')
            ,array('tel:+1-816-555-1212', 'tel:+1-816-555-1212')
            ,array('telnet://192.0.2.16:80/', 'telnet://192.0.2.16:80/')
            ,array('urn:oasis:names:specification:docbook:dtd:xml:4.1.2', 'urn:oasis:names:specification:docbook:dtd:xml:4.1.2')
            ,array('http://www.w%33.org', 'http://www.w%33.org')
            ,array('http://r%C3%A4ksm%C3%B6rg%C3%A5s.josefsson.org', 'http://r%C3%A4ksm%C3%B6rg%C3%A5s.josefsson.org')
            ,array('http://www.xn--n8jaaaaai5bhf7as8fsfk3jnknefdde3fg11amb5gzdb4wi9bya3kc6lra.w3.mag.keio.ac.jp/', 'http://www.xn--n8jaaaaai5bhf7as8fsfk3jnknefdde3fg11amb5gzdb4wi9bya3kc6lra.w3.mag.keio.ac.jp/')
            ,array('üסמחרני@üסמחרני.com', 'üסמחרני@üסמחרני.com')
            ,array(new \SplFileInfo('http://www.example.com'), 'http://www.example.com')
        );
    }

    public function generateInvalidURIs()
    {
        return array(
            array('')
        );
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Vald URI's
        foreach($this->generateValidURIs() as $Params) {

            list($Input) = $Params;

            try { $URI = $this->getMockForAbstractClass('\\BLW\\Type\\AURI', array($Input)); }

            catch (\Exception $e) {
                $this->fail(sprintf('Failded constructing URI with input (%s): %s', $Input, $e->getMessage()));
            }
        }

        $Expected = $this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('http://a/b/c/path/file?query#fragment'));
        $Actual   = $this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('path/file?query#fragment', $this->URI));

        $this->assertEquals($Expected, $Actual, 'IURI::__construct() Produced invalid URI');

        $Expected = $this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('http://a/path/file?query#fragment'));
        $Actual   = $this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('/path/file?query#fragment', $this->URI));

        $this->assertEquals($Expected, $Actual, 'IURI::__construct() Produced invalid URI');

        # Test URI input
        $URI2 = $this->getMockForAbstractClass('\\BLW\\Type\\AURI', array($this->URI));

        $this->assertEquals($this->URI, $URI2, 'IURI::__construct(IURI) should create an equivalient object');

        # Test invalid URI
        try {
            $this->URI->__construct(NULL);
            $this->fail('Failed to generate exception on invalid parameter');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::getRegex
     */
    public function test_getRegex()
    {
        $this->assertNotEmpty($this->URI->getRegex(), 'IURI::getRegex() should not be empty');
    }

    public function generatePaths()
    {
        return array(
             array('', '')
        	,array('a/b/c/././g', 'a/b/c/g')
            ,array('/a/b/c/././g', '/a/b/c/g')
            ,array('a/b/c/././g/', 'a/b/c/g/')
            ,array('/a/b/c/././g/', '/a/b/c/g/')
            ,array('a/b/c/./../../g', 'a/g')
            ,array('a/b/c/./../../../g', 'g')
            ,array('a/b/c/./../../g/', 'a/g/')
            ,array('a/b/c/./../../../g/', 'g/')
            ,array('/a/b/c/./../../g', '/a/g')
            ,array('/a/b/c/./../../../g', '/g')
            ,array('/a/b/c/./../../../g', '/g')
        );
    }

    /**
     * @covers ::removeDotSegments
     */
    public function test_removeDotSegments()
    {
        # Valid input
        foreach($this->generatePaths() as $Arguments) {

            list($Input, $Expected) = $Arguments;

            $this->assertSame($Expected, $this->URI->removeDotSegments($Input), sprintf('IURI::removeDotSegments(%s) returned an invalid value', $Input));
        }

        # Invalid input
        try {
            $this->URI->removeDotSegments(NULL);
            $this->fail('Failed to generate error on invalid arguments');
        }

        catch(InvalidArgumentException $e) {}
    }

    public function generateTLDs()
    {
        return array(
        	 array('www.example.co.uk', 'co.uk')
        	,array('www.example.tk', 'tk')
        	,array('www.example.com.info', 'com.info')
        	,array('www.example.biz.mobi', 'biz.mobi')
        	,array('www.example.com.sa', 'com.sa')
        );
    }

    /**
     * @covers ::parseTLD
     */
    public function test_parseTLD()
    {
        # Valid input
        foreach($this->generateTLDs() as $Arguments) {

            list($Input, $Expected) = $Arguments;

            $this->assertSame($Expected, $this->URI->parseTLD($Input), sprintf('IURI::parseTLD(%s) returned an invalid value', $Input));
        }

        # Invalid input
        $this->assertSame(false, $this->URI->parseTLD(NULL), 'IURI::parseTLD(NULL) returned an invalid value');

        try {
            $this->assertSame(false, $this->URI->parseTLD(array()), 'IURI::parseTLD(array()) returned an invalid value');
            $this->fail('Failed to generate warning with invalid parameter');
        }

        catch(\PHPUnit_Framework_Error_Warning $e) {}
    }

    /**
     * @covers ::parse
     */
    public function test_parse()
    {
        # Valid arguments
        $Test  = array(
             'scheme'       => 'http'
            ,'userinfo'     => ''
            ,'host'         => 'www.example.co.uk'
            ,'port'         => ''
            ,'path'         => '/path/file'
            ,'query'        => Array ('query' => 1)
            ,'fragment'     => 'fragment'
            ,'IPv4Address'  => ''
            ,'IPv6Address'  => ''
            ,'TLD'          => 'co.uk'
        );

        $this->assertEquals($Test, $this->URI->parse('http://www.example.co.uk/path/file?query=1#fragment'), 'IURI::parse() returned an invalid value');

        # Invalid arguments
        try {
            $this->URI->parse('http://www.example.co.uk/path/file?query=1#fragment', array());
            $this->fail('Failed to generate exception with invalid argument');
        }

        catch (InvalidArgumentException $e) {}

        try {
            $this->URI->parse(NULL);
            $this->fail('Failed to generate exception with invalid argument');
        }

        catch (InvalidArgumentException $e) {}
    }

    public function generateRelativePaths()
    {
        return array(
        	 array('g:h', 'g:h')
            ,array('g', 'http://a/b/c/g')
            ,array('./g', 'http://a/b/c/g')
            ,array('g/', 'http://a/b/c/g/')
            ,array('/g', 'http://a/g')
            ,array('//g', 'http://g')
            ,array('?y', 'http://a/b/c/d;p?y=')
            ,array('g?y', 'http://a/b/c/g?y=')
            ,array('#s', 'http://a/b/c/d;p?q=#s')
            ,array('g#s', 'http://a/b/c/g#s')
            ,array('g?y#s', 'http://a/b/c/g?y=#s')
            ,array(';x', 'http://a/b/c/;x')
            ,array('g;x', 'http://a/b/c/g;x')
            ,array('g;x?y#s', 'http://a/b/c/g;x?y=#s')
            ,array('""', 'http://a/b/c/d;p?q=')
            ,array('.', 'http://a/b/c/')
            ,array('./', 'http://a/b/c/')
            ,array('..', 'http://a/b/')
            ,array('../', 'http://a/b/')
            ,array('../g', 'http://a/b/g')
            ,array('../..', 'http://a/')
            ,array('../../g', 'http://a/g')
            ,array('../../../g', 'http://a/g')
            ,array('../../../../g', 'http://a/g')
        );
    }

    /**
     * @depends test_construct
     * @depends test_parse
     * @dataProvider generateRelativePaths
     * @covers ::resolve
     */
    public function test_resolve($Input, $Expected)
    {
        $this->assertSame($Expected, $this->URI->resolve($Input), 'IURL::resolve() returned an invalid value');
    }

    /**
     * @depends test_construct
     * @covers ::isValid
     */
    public function test_isValid()
    {
        # Vald URI's
        foreach($this->generateValidURIs() as $Params) {

            list($Input) = $Params;

            $URI = $this->getMockForAbstractClass('\\BLW\\Type\\AURI', array($Input));

            $this->assertTrue($URI->isValid(), sprintf('URI (%s) is should be valid.', $Input));
        }

        # Invalld URI's
        foreach($this->generateInvalidURIs() as $Params) {

            list($Input) = $Params;

            $URI = $this->getMockForAbstractClass('\\BLW\\Type\\AURI', array($Input));

            $this->assertFalse($URI->isValid(), sprintf('URI (%s) is should be invalid.', $Input));
        }
    }

    /**
     * @depends test_construct
     * @covers ::isAbsolute
     */
    public function test_isAbsolute()
    {
        $this->URI->__construct('http://www.google.com');
        $this->assertTrue($this->URI->isAbsolute(), 'IURI::isAbsolute() should be true');

        $this->URI->__construct('isbn:131cds9sdf0asf80');
        $this->assertTrue($this->URI->isAbsolute(), 'IURI::isAbsolute() should be true');

        $this->URI->__construct('php://');
        $this->assertFalse($this->URI->isAbsolute(), 'IURI::isAbsolute() should be false');
    }

    /**
     * @depends test_construct
     * @covers ::toString
     */
    public function test_toString()
    {
        # Valid URI's
        foreach($this->generateValidURIs() as $Params) {

            list($Input, $Expected) = $Params;

            $URI = $this->getMockForAbstractClass('\\BLW\\Type\\AURI', array($Input));

            $this->assertEquals($Expected, strval($URI), sprintf('(string) IURI should equal `%s`.', $Input));
        }

        # Invalid URI's
        foreach($this->generateInvalidURIs() as $Params) {

            list($Input) = $Params;

            $URI = $this->getMockForAbstractClass('\\BLW\\Type\\AURI', array($Input));

            $this->assertEquals('', strval($URI), '(string) IURI should be empty.');
        }
    }

    /**
     * @depends test_construct
     * @depends test_parse
     * @dataProvider generateValidURIs
     * @covers ::offsetGet
     */
    public function test_offsetGet($Input)
    {
        $URI = $this->getMockForAbstractClass('\\BLW\\Type\\AURI', array($Input));

        foreach($this->URI->parse($Input) as $k => $v) {
            $this->assertSame($v, $URI->offsetGet($k), sprintf('IURI[%s] should equal `%s`', $k, @strval($v)));
        }
    }

    /**
     * @depends test_construct
     * @depends test_parse
     * @dataProvider generateValidURIs
     * @covers ::offsetExists
     */
    public function test_offsetExists($Input)
    {
        $URI = $this->getMockForAbstractClass('\\BLW\\Type\\AURI', array($Input));

        foreach($this->URI->parse($Input) as $k => $v) {
            $this->assertTrue($URI->offsetExists($k), sprintf('IURI[%s] should exist', $k));
        }
    }

    /**
     * @depends test_construct
     * @covers ::offsetSet
     */
    public function test_offsetSet()
    {
        try {
            $this->URI->offsetSet('foo', 'bar');
            $this->fail('Failed to generate notice on readonly offset');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify', $e->getMessage(), 'Invalid Notice: '. $e->getMessage());
        }
    }


    /**
     * @depends test_construct
     * @covers ::offsetUnset
     */
    public function test_offsetUnset()
    {
        try {
            $this->URI->offsetSet('foo', 'bar');
            $this->fail('Failed to generate notice on readonly offset');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify', $e->getMessage(), 'Invalid Notice: '. $e->getMessage());
        }
    }
}