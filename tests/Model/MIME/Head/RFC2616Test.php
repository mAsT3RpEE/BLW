<?php
/**
 * RFC2616Test.php | Mar 20, 2014
 *
 * @filesource
 * @license MIT
 * @copyright Copyright (c) 2013-2018, mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * @package BLW\MIME
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Tests\Model\MIME\Head;

use ReflectionMethod;
use ReflectionProperty;

use BLW\Model\InvalidArgumentException;

use BLW\Model\MIME\MIMEVersion;
use BLW\Model\MIME\Section;

use BLW\Model\MIME\Head\RFC2616 as Head;
use BLW\Model\MIME\Generic as GenericHeader;


/**
 * Tests BLW Library MIME Head header.
 * @package BLW\MIME
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\Head
 */
class RFC2616Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\MIME\IHeader
     */
    protected $Header = NULL;

    /**
     * @var \BLW\Type\MIME\IHead
     */
    protected $Head = NULL;

    protected function setUp()
    {
        $this->Header = $this->getMockForAbstractClass('\\BLW\\Type\\MIME\\IHeader');
        $this->Header
            ->expects($this->any())
            ->method('__toString')
            ->will($this->returnValue("Mock Header: foo\r\n"));

        $this->Head    = new Head;
        $this->Head[]  = $this->Header;
        $this->Head[]  = "Direct String\r\n";
        $this->Head[]  = $this->Header;
        $this->Head[]  = $this->Header;
    }

    protected function tearDown()
    {
        $this->Head   = NULL;
        $this->Header = NULL;
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Check properties
        $this->assertInstanceOf('\\BLW\\Type\\MIME\\IHead', new Head, 'IHead::__construct() Did not return an instance of IHead');
    }

    /**
     * @depends test_construct
     * @covers ::getHeader
     */
    public function test_getHeader()
    {
        $this->Head[]      = new GenericHeader('Foo', 'bar');
        $this->Head['Bar'] = 'bar';

        $this->assertInstanceOf('\\BLW\\Type\\MIME\\IHeader', $this->Head->getHeader('Foo'), 'IHead::getHeader() Should return an instance of IHeader');
        $this->assertSame('bar', $this->Head->getHeader('Bar'), 'IHead::getHeader() Should return `bar`');
    }

    /**
     * @depends test_construct
     * @covers ::__toString
     */
    public function test_toString()
    {
        $Expected = <<<EOT
Mock Header: foo
Mock Header: foo
Mock Header: foo


EOT;

        $this->assertEquals($Expected, @strval($this->Head), '(string) Head returned an invalid format');
    }
}