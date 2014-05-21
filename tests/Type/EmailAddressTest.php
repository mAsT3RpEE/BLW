<?php
/**
 * EmailAddressTest.php | Jan 26, 2014
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


/**
 * Tests BLW Library Iterator type.
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 *  @coversDefaultClass \BLW\Type\AEmailAddress
 */
class EmailAddressTest extends \BLW\Type\IterableTest
{
    /**
     * @var \BLW\Type\IEmailAddress $Email
     */
    protected $Email = NULL;

    public function setUp()
    {
        $this->Email    = $this->getMockForAbstractClass('\\BLW\\Type\\AEmailAddress', array('test@example.com', 'Test User'));
        $this->Iterable = $this->Email;

    }

    public function tearDown()
    {
        $this->Email    = NULL;
        $this->Iterable = NULL;
    }

    /**
     * @covers ::getFactoryMethods
     */
    public function test_getFactoryMethods()
    {
        $this->assertNotEmpty($this->Email->getFactoryMethods(), 'IEmailAddress::getFactoryMethods() Returned an invalid value');
        $this->assertContainsOnlyInstancesOf('ReflectionMethod', $this->Email->getFactoryMethods(), 'IEmailAddress::getFactoryMethods() Returned and invalid value');
    }

    public function generateParts()
    {
        return array(
                array(array(), '')
                ,array(array('Personal' => 'Test User', 'Local' => 'test', 'Domain' => 'example.com'), 'Test User <test@example.com>')
                ,array(array('Personal' => 'Test User', 'Local' => 'te"st', 'Domain' => 'example.com'), 'Test User <"te\\"st"@example.com>')
        );
    }

    /**
     * @dataProvider generateParts
     * @covers ::createEmailString
     */
    public function test_createEmailString($Input, $Expected)
    {
        $this->assertSame($Expected, $this->Email->createEmailString($Input), 'IEmailAddress::createEmailString() returned and invalid value');
    }

    public function generateValidAddresses()
    {
        return array(
             array('niceandsimple@example.com', 'niceandsimple@example.com')

            # Valid addressses
            ,array('very.common@example.com', 'very.common@example.com')
            ,array('a.little.lengthy.but.fine@dept.example.co.uk', 'a.little.lengthy.but.fine@dept.example.co.uk')
            ,array('disposable.style.email.with+symbol@example.com', 'disposable.style.email.with+symbol@example.com')
            ,array('other.email-with-dash@example.com', 'other.email-with-dash@example.com')
            ,array('customer/department=shipping@example.com', 'customer/department=shipping@example.com')
            ,array('user@[IPv6:2001:db8:1ff::a0b:dbd0]', 'user@[IPv6:2001:db8:1ff::a0b:dbd0]')
            ,array('"much.more unusual"@example.com', '"much.more unusual"@example.com')
            ,array('"very.unusual.@.unusual.com"@example.com', '"very.unusual.@.unusual.com"@example.com')
            ,array('"very.(),:;<>[]\".VERY.\"very@\\ \"very\".unusual"@strange.example.com', '"very.(),:;<>[]\".VERY.\"very@\\ \"very\".unusual"@strange.example.com')
            ,array('!#$%&\'*+-/=?^_`{}|~@example.org', '!#$%&\'*+-/=?^_`{}|~@example.org')
            ,array('üסמחרני@example.com', 'üסמחרני@example.com')
            ,array('üסמחרני@üסמחרני.com', 'üסמחרני@üסמחרני.com')
            ,array('test@[192.168.20.1]', 'test@[192.168.20.1]')
            ,array('test@[IPv6:2001:db8:85a3:8d3:1319:8a2e:370:7348]', 'test@[IPv6:2001:db8:85a3:8d3:1319:8a2e:370:7348]')
            ,array('test@[IPv6:0:0:0:0:0:1:127.0.0.1]', 'test@[IPv6:0:0:0:0:0:1:127.0.0.1]')
            ,array('test@[IPv6:::1:127.0.0.1]', 'test@[IPv6:::1:127.0.0.1]')

            # Unimplemented
//          ,array('postbox@com', 'postbox@com')
//          ,array('admin@mailserver1', 'admin@mailserver1')
        );
    }

    public function generateInvalidAddresses()
    {
        return array(
             array('just"not"right@example.com', '')
            ,array('this\ still\"not\\allowed@example.com', '')
            ,array('Abc.example.com', '')
            ,array('A@b@c@example.com', '')
            ,array('a"b(c)d,e:f;g<h>i[j\k]l@example.com', '')
            ,array('this is"not\allowed@example.com', '')
            ,array('" "@example.org', '')
            ,array('"()<>[]:,;@\\\"!#$%&\'*+-/=?^_`{}| ~.a"@example.org', '')
            ,array('email@brazil.b', 'email@brazil.b')
            ,array('test@a.c', 'test@a.c')
            ,array('test@'.str_repeat('a', 64).'.com', 'test@'.str_repeat('a', 64).'.com')
            ,array('test@foo.'.str_repeat('c', 64) . '.uk', 'test@foo.'.str_repeat('c', 64) . '.uk')
            ,array(str_repeat('a', 64) . '@example.com', str_repeat('a', 64) . '@example.com')
            ,array('test@'. str_repeat('a', 255) . '.com', 'test@'. str_repeat('a', 255) . '.com')
            ,array('test@foo-.com', 'test@foo-.com')
            ,array('test@[127.0.0.256]', 'test@[127.0.0.256]')
            ,array('test@[IPv6:0:0:0:0:0:100:127.0.0.256]', 'test@[IPv6:0:0:0:0:0:100:127.0.0.256]')
            ,array('test@[IPv6:0:0:0::0:0:100:127.0.0.256]', 'test@[IPv6:0:0:0::0:0:100:127.0.0.256]')
            ,array('test@[2001:db8:85a3:8d3:1319:8a2e:370:7348]', 'test@[2001:db8:85a3:8d3:1319:8a2e:370:7348]')
            ,array('test@[IPv6:2001:db8:85a3:8d3::1319:8a2e:370:7348:8334]', 'test@[IPv6:2001:db8:85a3:8d3::1319:8a2e:370:7348:8334]')
        );
    }

    public function generateRegexs()
    {
        return array(
             array('addr-spec')
            ,array('local-part')
            ,array('domain')
            ,array('dot-atom')
            ,array('quoted-string')
            ,array('obs-local-part')
            ,array('domain-literal')
            ,array('obs-domain')
            ,array('atom')
            ,array('word')
            ,array('comment')
        );
    }

    /**
     * @covers ::getRegex
     */
    public function test_getRegex()
    {
        foreach ($this->generateRegexs() as $Arguments) {

            list($Regex) = $Arguments;

            $this->assertNotEmpty($this->Email->getRegex($Regex), 'IEmailAddress::getRegex('.$Regex.') should not be empty');
        }
    }

    /**
     * @depends test_getRegex
     * @covers ::parse
     */
    public function test_parse()
    {
        $Test  = array(
             'Personal'      => 'Test User'
            ,'Local'         => 'test'
            ,'Domain'        => 'example.co.uk'
            ,'TLD'           => 'co.uk'
            ,'LocalAtom'     => 'test'
            ,'LocalQuoted'   => ''
            ,'LocalObs'      => ''
            ,'DomainAtom'    => 'example.co.uk'
            ,'DomainLiteral' => ''
            ,'DomainObs'     => ''
            ,'Address'       => 'test@example.co.uk'
        );

        $this->assertEquals($Test, $this->Email->parse('test@example.co.uk', 'Test User'), 'IEmailAddress::buildParts() returned an invalid value');
    }

    /**
     * @depends test_parse
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Vald Adresses
        foreach ($this->generateValidAddresses() as $Params) {

            list($Input) = $Params;

            try { $Email = $this->getMockForAbstractClass('\\BLW\\Type\\AEmailAddress', array($Input)); } catch (\Exception $e) {
                $this->fail(sprintf('Failded constructing EmailAddress with input (%s): %s', $Input, $e->getMessage()));
            }
        }

        # Test EmailAddress input
        $Email2 = $this->getMockForAbstractClass('\\BLW\\Type\\AEmailAddress', array($this->Email));

        $this->assertEquals($this->Email, $Email2, 'IEmailAddress::__construct(IEmailAddress) should create an equivalient object');

        # Test object personal
        $this->Email->__construct('foo@example.com', new \SplFileInfo(__FILE__));

        # Test invalid Email
        try {
            $this->Email->__construct(NULL);
            $this->fail('Failed to generate exception on invalid parameter');
        } catch (InvalidArgumentException $e) {}

        # Test invalid Personal
        try {
            $this->Email->__construct('test@foo.com', array());
            $this->fail('Failed to generate exception on invalid parameter');
        } catch (InvalidArgumentException $e) {}
    }

    /**
     * @depends test_construct
     * @covers ::isValid
     */
    public function test_isValid()
    {
        # Vald Addresses
        foreach ($this->generateValidAddresses() as $Params) {

            list($Input) = $Params;

            $Email = $this->getMockForAbstractClass('\\BLW\\Type\\AEmailAddress', array($Input));

            $this->assertTrue($Email->isValid(), sprintf('Email address (%s) is should be valid.', $Input));
        }

        # Invalld URI's
        foreach ($this->generateInvalidAddresses() as $Params) {

            list($Input, $Expected) = $Params;

            $Email = $this->getMockForAbstractClass('\\BLW\\Type\\AEmailAddress', array($Input));

            $this->assertFalse($Email->isValid(), sprintf('Email address (%s) is should be invalid.', $Input));
        }

    }

    /**
     * @depends test_construct
     * @covers ::__toString
     */
    public function test_toString()
    {
        $Email = $this->getMockForAbstractClass('\\BLW\\Type\\AEmailAddress', array('test@example.com', 'Test User'));
        $this->AssertEquals('Test User <test@example.com>', strval($Email), '(string) IEmailAddress should equal `Test User <test@example.com>`');

        # Valid Addresses
        foreach ($this->generateValidAddresses() as $Params) {

            list($Input, $Expected) = $Params;

            $Email = $this->getMockForAbstractClass('\\BLW\\Type\\AEmailAddress', array($Input));

            $this->assertEquals($Expected, strval($Email), sprintf('(string) IEmailAddress should equal `%s`', $Input));
        }

        # Invalid Addresses
        foreach ($this->generateInvalidAddresses() as $Params) {

            list($Input, $Expected) = $Params;

            $Email = $this->getMockForAbstractClass('\\BLW\\Type\\AEmailAddress', array($Input));

            $this->assertEquals($Expected, strval($Email), '(string) IEmailAddress should be empty');
        }
    }

    /**
     * @depends test_construct
     * @depends test_parse
     * @dataProvider generateValidAddresses
     * @covers ::offsetGet
     */
    public function test_offsetGet($Input)
    {
        $Email = $this->getMockForAbstractClass('\\BLW\\Type\\AEmailAddress', array($Input));

        foreach ($this->Email->parse($Input) as $k => $v) {
            $this->assertSame($v, $Email[$k], sprintf('IEmailAddress[%s] should equal `%s`', $k, @strval($v)));
        }

        # Undefined offset
        try {
            $this->Email['undefined'];
            $this->fail('Failed to generate notice with undefined offset');
        } catch (\PHPUnit_Framework_Error_Notice $e) {}

        @$this->Email['undefined'];
    }

    /**
     * @depends test_construct
     * @depends test_parse
     * @dataProvider generateValidAddresses
     * @covers ::offsetExists
     */
    public function test_offsetExists($Input)
    {
        $Email = $this->getMockForAbstractClass('\\BLW\\Type\\AEmailAddress', array($Input));

        foreach ($this->Email->parse($Input) as $k => $v) {
            $this->assertArrayHasKey($k, $Email, sprintf('IEmailAddress[%s] should exist', $k));
        }
    }

    /**
     * @depends test_construct
     * @covers ::offsetSet
     */
    public function test_offsetSet()
    {
        try {
            $this->Email['Personal'] = 'Test User';
            $this->fail('Failed to generate notice on readonly offset');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify', $e->getMessage(), 'Invalid Notice: '. $e->getMessage());
        }

        @$this->Email->offsetSet('Personal', 'Test User');
    }


    /**
     * @depends test_construct
     * @covers ::offsetUnset
     */
    public function test_offsetUnset()
    {
        try {
            unset($this->Email['Personal']);
            $this->fail('Failed to generate notice on readonly offset');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify', $e->getMessage(), 'Invalid Notice: '. $e->getMessage());
        }

        @$this->Email->offsetUnset('Personal');
    }

    /**
     * @depends test_construct
     * @covers ::count
     */
    public function test_count()
    {
        $this->assertInternalType('integer', $this->Email->count(), 'IEmailAddress::count() Returned an invalid value');
        $this->assertGreaterThan(1, $this->Email->count(), 'IEmailAddress::count() Returned an invalid value');
    }

    /**
     * @depends test_construct
     * @covers ::getIterator
     */
    public function test_getIterator()
    {
        $count = 0;

        $this->assertInstanceOf('Traversable', $this->Email->getIterator(), 'IEmailAddress::getIterator() Returned an invalid result');

        foreach($this->Email as $v)
            $count++;

        $this->assertSame(count($this->Email), $count, 'IEmailAddress::getIterator() Returned an invalid result');
    }
}
