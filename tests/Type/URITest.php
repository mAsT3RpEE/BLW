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
namespace BLW\Type;

use ReflectionMethod;
use BLW\Model\InvalidArgumentException;
use BLW\Type\IURI;


/**
 * Tests BLW Library Iterator type.
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 *  @coversDefaultClass \BLW\Type\AURI
 */
class URITest extends \BLW\Type\IterableTest
{
    /**
     * @var \BLW\Type\AURI $URI
     */
    protected $URI = NULL;

    public function setUp()
    {
        $this->URI      = $this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('http://a/b/c/d;p?q#f'));
        $this->Iterable = $this->URI;
    }

    public function tearDown()
    {
        $this->URI      = NULL;
        $this->Iterable = NULL;
    }

    /**
     * @covers ::getFactoryMethods
     */
    public function test_getFactoryMethods()
    {
        $this->assertNotEmpty($this->URI->getFactoryMethods(), 'IURI::getFactoryMethods() Returned an invalid value');
        $this->assertContainsOnlyInstancesOf('ReflectionMethod', $this->URI->getFactoryMethods(), 'IURI::getFactoryMethods() Returned an invalid value');
    }

    public function generateParts()
    {
        return array(
             array(array(), '')
        	,array(array('scheme' => 'http', 'host' => 'www.example.com', 'userinfo' => 'user:pass', 'query' => array('foo' => 'bar'), 'fragment' => 'fragment'), 'http://user:pass@www.example.com?foo=bar#fragment')
        	,array(array('scheme' => 'http', 'host' => 'www.example.com', 'query' => false, 'fragment' => 'fragment'), 'http://www.example.com#fragment')
            ,array(parse_url('http://user:pass@www.example.com/path/to/file?foo=bar#fragment'), 'http://user:pass@www.example.com/path/to/file?foo=bar#fragment')
            ,array(parse_url('http://user@www.example.com?foo=bar#fragment'), 'http://user@www.example.com?foo=bar#fragment')
            ,array(parse_url('http://www.example.com?foo=bar#fragment') + array('pass' => 'pass'), 'http://anonymous:pass@www.example.com?foo=bar#fragment')
        );
    }

    /**
     * @covers ::createURIString
     * @covers ::_userInfo
     */
    public function test_createURIString()
    {
        for($i = $this->generateParts(); list($k,list($Input, $Expected)) = each($i);) {
            $this->assertSame($Expected, $this->URI->createURIString($Input), 'IURI::createURIString() returned and invalid value');
        }
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
     * @covers ::_parse
     */
    public function test_parse()
    {
        # Valid arguments
        $Test  = array(
             'scheme'       => 'https'
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

        $Base  = array(
            'scheme'        => 'http'
            ,'host'         => 'foo.com'
            ,'path'         => '/path/file'
            ,'query'        => Array ('query' => 1)
            ,'fragment'     => 'fragment'
            ,'TLD'          => 'com'
        );

        $this->assertEquals($Test, $this->URI->parse('https://www.example.co.uk/path/file?query=1#fragment', $Base), 'IURI::parse() returned an invalid value');

        # no scheme
        $Test['scheme'] = 'http';

        $this->assertEquals($Test, $this->URI->parse('//www.example.co.uk/path/file?query=1#fragment', $Base), 'IURI::parse() returned an invalid value');

        # no authority
        $Test['host'] = 'foo.com';
        $Test['TLD']  = 'com';

        $this->assertEquals($Test, $this->URI->parse('/path/file?query=1#fragment', $Base), 'IURI::parse() returned an invalid value');

        # relative path
        $Test['path'] = '/path/file2';

        $this->assertEquals($Test, $this->URI->parse('file2?query=1#fragment', $Base), 'IURI::parse() returned an invalid value');

        # no path
        $Test['path'] = '/path/file';

        $this->assertEquals($Test, $this->URI->parse('?query=1#fragment', $Base), 'IURI::parse() returned an invalid value');
        $this->assertEquals($Test, $this->URI->parse('#fragment', $Base), 'IURI::parse() returned an invalid value');

        # Invalid URI
        $Test['fragment'] = '';

        $this->assertEquals($Test, $this->URI->parse('', $Base), 'IURI::parse() returned an invalid value');

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
        $default = array(
            'scheme' => 'http',
            'userinfo' => '',
            'host' => 'a',
            'port' => '',
            'IPv4Address' => '',
            'IPv6Address' => '',
            'path' => '/b/c/d;p',
            'query' => array(),
            'fragment' => '',
            'TLD' => ''
        );

        return array(
        	 array('g:h', 'g:h', array('scheme' => 'g', 'host' => '', 'path' => 'h') + $default)
            ,array('g', 'http://a/b/c/g', array('path' => '/b/c/g') + $default)
            ,array('./g', 'http://a/b/c/g', array('path' => '/b/c/g') + $default)
            ,array('g/', 'http://a/b/c/g/', array('path' => '/b/c/g/') + $default)
            ,array('/g', 'http://a/g', array('path' => '/g') + $default)
            ,array('//g', 'http://g', array('host' => 'g', 'path' => '') + $default)
            ,array('?y', 'http://a/b/c/d;p?y=', array('query' => array('y' => null)) + $default)
            ,array('g?y', 'http://a/b/c/g?y=', array('path' => '/b/c/g', 'query' => array('y' => null)) + $default)
            ,array('#s', 'http://a/b/c/d;p?q=#s', array('query' => array('q' => null), 'fragment' => 's') + $default)
            ,array('g#s', 'http://a/b/c/g#s', array('path' => '/b/c/g', 'fragment' => 's') + $default)
            ,array('g?y#s', 'http://a/b/c/g?y=#s', array('path' => '/b/c/g', 'query' => array('y' => null), 'fragment' => 's') + $default)
            ,array(';x', 'http://a/b/c/;x', array('path' => '/b/c/;x') + $default)
            ,array('g;x', 'http://a/b/c/g;x', array('path' => '/b/c/g;x') + $default)
            ,array('g;x?y#s', 'http://a/b/c/g;x?y=#s', array('path' => '/b/c/g;x', 'query' => array('y' => null), 'fragment' => 's') + $default)
            ,array('""', 'http://a/b/c/d;p?q=', array('query' => array('q' => null)) + $default)
            ,array('.', 'http://a/b/c/', array('path' => '/b/c/') + $default)
            ,array('./', 'http://a/b/c/', array('path' => '/b/c/') + $default)
            ,array('..', 'http://a/b/', array('path' => '/b/') + $default)
            ,array('../', 'http://a/b/', array('path' => '/b/') + $default)
            ,array('../g', 'http://a/b/g', array('path' => '/b/g') + $default)
            ,array('../..', 'http://a/', array('path' => '/') + $default)
            ,array('../../g', 'http://a/g', array('path' => '/g') + $default)
            ,array('../../../g', 'http://a/g', array('path' => '/g') + $default)
            ,array('../../../../g', 'http://a/g', array('path' => '/g') + $default)
        );
    }

    /**
     * @depends test_construct
     * @depends test_parse
     * @covers ::resolve
     */
    public function test_resolve()
    {
        # Valid Input
        foreach ($this->generateRelativePaths() as $Arguments) {

            list($Input, $Expected1, $Expected2) = $Arguments;

            $this->assertEquals($Expected1, $this->URI->resolve($Input, IURI::AS_STRING), 'IURL::resolve() returned an invalid value');
            $this->assertEquals($Expected2, $this->URI->resolve($Input, IURI::AS_ARRAY), 'IURL::resolve() returned an invalid value');
        }

        # Invalid Input
        try {
            $this->URI->resolve('', 0);
            $this->fail('Failed to generate exception with invalid arguments');
        }

        catch (InvalidArgumentException $e) {}
    }

    public function generateInvalidURIs()
    {
        return array(
             array('')
            ,array(str_repeat('a', 32). '://a/b/c/d;p?q#f')
            ,array('http://' . str_repeat('a', 256). '/b/c/d;p?q#f')
            ,array('http://a/' . str_repeat('b', 2048). '/c/d;p?q#f')
            ,array('http://a:65536/b/c/d;p?q#f')
            ,array(str_repeat('a', 31). '://' . str_repeat('a', 255). '/' . str_repeat('b', 2000). '/c/d;p?q#f')
        );
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
     * @covers ::__toString
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
        $URI = $this->getMockForAbstractClass('\\BLW\\Type\\AURI', array(''));

        $this->assertEquals('', strval($URI), '(string) IURI should be empty.');
    }

    /**
     * @depends test_construct
     * @depends test_parse
     * @covers ::offsetGet
     */
    public function test_offsetGet()
    {
        # Valid arguments
        foreach ($this->generateValidURIs() as $Arguments) {

            list($Input) = $Arguments;

            $URI = $this->getMockForAbstractClass('\\BLW\\Type\\AURI', array($Input));

            foreach($this->URI->parse($Input) as $k => $v)
                $this->assertSame($v, $URI->offsetGet($k), sprintf('IURI[%s] should equal `%s`', $k, @strval($v)));
        }

        # Invalid arguments
        try {
            $URI['undefined'];
            $this->fail('Failed to generate notice with undefined value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {}

        @$URI['undefined'];
    }

    /**
     * @depends test_construct
     * @depends test_parse
     * @dataProvider generateValidURIs
     * @covers ::offsetExists
     */
    public function test_offsetExists($Input)
    {
        # Valid arguments
        foreach ($this->generateValidURIs() as $Arguments) {

            list($Input) = $Arguments;

            $URI = $this->getMockForAbstractClass('\\BLW\\Type\\AURI', array($Input));

            foreach($this->URI->parse($Input) as $k => $v)
                $this->assertTrue($URI->offsetExists($k), sprintf('IURI[%s] should exist', $k));
        }

        # Invalid arguments
        $this->assertFalse(isset($URI['undefined']), 'IURI[undefined] should not exist');
    }

    /**
     * @depends test_construct
     * @covers ::offsetSet
     */
    public function test_offsetSet()
    {
        try {
            $this->URI->offsetSet('undfined', 'bar');
            $this->fail('Failed to generate notice on readonly offset');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify', $e->getMessage(), 'Invalid Notice: '. $e->getMessage());
        }

        @$this->URI->offsetSet('undfined', 'bar');
    }


    /**
     * @depends test_construct
     * @covers ::offsetUnset
     */
    public function test_offsetUnset()
    {
        try {
            unset($this->URI['host']);
            $this->fail('Failed to generate notice on readonly offset');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify', $e->getMessage(), 'Invalid Notice: '. $e->getMessage());
        }

        @$this->URI->offsetUnset('Personal');
    }

    /**
     * @depends test_construct
     * @covers ::count
     */
    public function test_count()
    {
        $this->assertInternalType('integer', $this->URI->count(), 'IURI::count() Returned an invalid value');
        $this->assertGreaterThan(1, $this->URI->count(), 'IURI::count() Returned an invalid value');
    }

    /**
     * @depends test_construct
     * @covers ::getIterator
     */
    public function test_getIterator()
    {
        $count = 0;

        $this->assertInstanceOf('Traversable', $this->URI->getIterator(), 'IURI::getIterator() Returned an invalid result');

        foreach($this->URI as $v)
            $count++;

        $this->assertSame(count($this->URI), $count, 'IEmailAddress::getIterator() Returned an invalid result');
    }
}
