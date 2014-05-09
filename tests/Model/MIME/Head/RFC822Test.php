<?php
/**
 * RFC822Test.php | Mar 20, 2014
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
namespace BLW\Model\MIME\Head;

use ReflectionMethod;
use ReflectionProperty;

use BLW\Model\InvalidArgumentException;

use BLW\Model\MIME\MIMEVersion;
use BLW\Model\MIME\Section;

use BLW\Model\MIME\Head\RFC822 as Head;
use BLW\Model\MIME\Generic as GenericHeader;


/**
 * Tests BLW Library MIME Head header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\Head\RFC822
 */
class RFC822Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\MIME\Verion
     */
    protected $Version = NULL;

    /**
     * @var \BLW\Model\MIME\Section
     */
    protected $Section = NULL;

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

        $this->Version = new MIMEVersion('1.0');
        $this->Section = new Section('multipart/alternative');
        $this->Head    = new Head($this->Version, $this->Section);
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
        $Version = new ReflectionProperty($this->Head, '_Version');
        $Section = new ReflectionProperty($this->Head, '_Section');

        $Version->setAccessible(true);
        $Section->setAccessible(true);

        $this->assertSame($this->Version, $Version->getValue($this->Head), 'Head::__construct() failed to set $_Version');
        $this->assertSame($this->Section, $Section->getValue($this->Head), 'Head::__construct() failed to set $_Section');
    }

    /**
     * @depends test_construct
     * @covers ::getVersion
     */
    public function test_getVersion()
    {
        $this->assertSame($this->Version, $this->Head->getVersion(), 'Head::getVersion() returned an invalid result');
    }

    /**
     * @depends test_construct
     * @covers ::getSection
     */
    public function test_getSection()
    {
        $this->assertSame($this->Section, $this->Head->getSection(), 'Head::getSection() returned an invalid result');
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
        $this->assertFalse($this->Head->getHeader(new \SplFileInfo(__FILE__)), 'IHead::getHeader() should return FALSE');

        # Invalid argument
        try {
            $this->Head->getHeader(null);
            $this->fail('Failed to generate exception with invalid arguments');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @depends test_construct
     * @covers ::__toString
     */
    public function test_toString()
    {
        $Expected = <<<EOT
Mock Header: foo\r\nMock Header: foo\r\nMock Header: foo\r\nMIME-Version: 1.0\r\nContent-Type: multipart/alternative; boundary="%s"\r\n\r\n
EOT;

        $this->assertEquals(sprintf($Expected, $this->Head->getSection()->getBoundary()), @strval($this->Head), '(string) Head returned an invalid format');
    }
}
