<?php
/**
 * RFC2616.php | Mar 20, 2014
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
namespace BLW\Model\MIME\Body;

use ReflectionMethod;
use ReflectionProperty;

use BLW\Type\MIME\IHeader;

use BLW\Model\InvalidArgumentException;
use BLW\Model\GenericFile;

use BLW\Model\MIME\Body\RFC2616 as Body;
use BLW\Model\MIME\Section;
use BLW\Model\MIME\Part\Attachment;
use BLW\Model\MIME\Part\QuotedPrintable;
use BLW\Model\MIME\Part\FormField;
use BLW\Model\MIME\Part\FormData;


/**
 * Tests BLW Library MIME Body header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\Body\RFC2616
 */
class RFC2616Test extends \PHPUnit_Framework_TestCase
{
    const FILE   = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVQYV2NgYAAAAAMAAWgmWQ0AAAAASUVORK5CYII=';

    /**
     * @var \BLW\Model\MIME\Part\FormField
     */
    protected $Field = NULL;

    /**
     * @var \BLW\Model\MIME\Part\FormData
     */
    protected $Part = NULL;

    /**
     * @var \BLW\Model\MIME\Section
     */
    protected $Section = NULL;

    /**
     * @var \BLW\Model\MIME\IHeader
     */
    protected $Header = NULL;

    /**
     * @var \BLW\Model\MIME\Body\RFC2616
     */
    protected $Body = NULL;

    protected function setUp()
    {
        $this->Header = $this->getMockForAbstractClass('\\BLW\\Type\\MIME\\IHeader');
        $this->Header
            ->expects($this->any())
            ->method('__toString')
            ->will($this->returnValue("Mock Header: foo\r\n"));

        $this->Field            = new FormField('Name', 'text/plain', 'Mr.X');
        $this->Part             = new FormData(array($this->Field));
        $this->Body             = new Body;
        $this->Section          = new Section('multipart/mixed', 'mixed-boundary');
    }

    protected function tearDown()
    {
        $this->Header           = NULL;
        $this->Field            = NULL;
        $this->Part             = NULL;
        $this->Body             = NULL;
        $this->Section          = NULL;
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        $Body = new Body;

        $this->assertAttributeCount(0, '_Sections', $Body, 'RFC2616::__construct() Failed to reset sections');

        # Invalid arguments
    }

    /**
     * @depends test_construct
     * @covers ::addSection
     */
    public function test_addSection()
    {
        $Expected = new Section('multipart/mixed', 'xxx-xxx');

        $this->assertTrue($this->Body->addSection($Expected), 'RFC2616::addSection() should return true');

        $Sections = $this->readAttribute($this->Body, '_Sections');

        $this->assertSame($Expected, $Sections[0], 'RFC2616::addSection() failed to modify $_Sections');
        $this->assertEquals($Expected->createStart(), $this->Body[count($this->Body)-2], 'RFC2616::addSection() failed to add a mime boundary');

        $this->assertTrue($this->Body->addSection($this->Section), 'RFC2616::addSection() should return true');

        $Sections = $this->readAttribute($this->Body, '_Sections');

        $this->assertSame($this->Section, $Sections[0], 'RFC2616::addSection() failed to modify $_Sections');
    }

    /**
     * @depends test_addSection
     * @covers ::getSection
     */
    public function test_getSection()
    {
        $this->assertNull($this->Body->getSection(), 'RFC2616::getSection() should be NULL');
        $this->assertTrue($this->Body->addSection($this->Section), 'RFC2616::addSection() should return true');
        $this->assertSame($this->Section, $this->Body->getSection(), 'RFC2616::getSection() returned an invalid result');
    }

    /**
     * @depends test_getSection
     * @covers ::endSection
     */
    public function test_endSection()
    {
        $Expected = new Section('multipart/mixed', 'xxx-xxx');

        $this->assertTrue($this->Body->addSection($Expected), 'RFC2616::addSection() should return true');
        $this->assertTrue($this->Body->endSection(), 'RFC2616::endSection() should return true');
        $this->assertNull($this->Body->getSection(), 'RFC2616::endSection failed to modify $_Sections');
        $this->assertFalse($this->Body->endSection(), 'RFC2616::endSection() should return true');
        $this->assertNULL($this->Body->getSection(), 'RFC2616::endSection failed to modify $_Sections');
    }

    /**
     * @depends test_getSection
     * @covers ::addPart
     */
    public function test_addPart()
    {
        # Valid arguments
        $this->assertTrue($this->Body->addSection($this->Section), 'RFC2616::addSection() should return true');
        $this->assertTrue($this->Body->addPart($this->Field), 'RFC2616::addPart() should return true');
        $this->assertSame($this->Field, $this->Body[count($this->Body)-1], 'RFC2616::addPart() failed to modify $_Storage');
        $this->assertEquals($this->Body->getSection()->createBoundary(), $this->Body[count($this->Body)-2], 'RFC2616::addPart() failed to add a mime boundary');

        $this->assertTrue($this->Body->addPart($this->Part), 'RFC2616::addPart() should return true');
        $this->assertSame($this->Part, $this->Body[count($this->Body)-1], 'RFC2616::addPart() failed to modify $_Storage');
        $this->assertEquals($this->Body->getSection()->createBoundary(), $this->Body[count($this->Body)-2], 'RFC2616::addSection() failed to add a mime boundary');

        # Invalid arguments
        try {
            $this->Body->addPart(NULL);
            $this->fail('Failed to generate exception with invalid arguments');
        }

        catch (\PHPUnit_Framework_Error $e) {}

        # Empty sections
        $this->assertTrue($this->Body->endSection(), 'RFC2616::endSection() should return true');
        $this->assertFalse($this->Body->addPart($this->Part), 'RFC2616::addPart() should return false');
    }

    /**
     * @depends test_construct
     * @depends test_addSection
     * @depends test_endSection
     * @depends test_addPart
     * @depends test_addPart
     * @covers ::__toString
     */
    public function test_toString()
    {
        $Expected = <<<EOT
Mock Header: foo\r\nDirect String\r\nMock Header: foo\r\nContent-Type: multipart/mixed; boundary="mixed-boundary"\r\n\r\n--mixed-boundary\r\nContent-Type: multipart/alternative; boundary="alternative-boundary"\r\n\r\n--alternative-boundary\r\nContent-Type: multipart/foo; boundary="foo-boundary"\r\n\r\n--foo-boundary\r\nName=Mr.X\r\n\r\n--foo-boundary--\r\n\r\n--alternative-boundary\r\nContent-Disposition: form-data; name=Name\r\nContent-Type: text/plain; charset=utf-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\nMr.X\r\n\r\n--alternative-boundary\r\nContent-Disposition: form-data; name=Name\r\nContent-Type: text/plain; charset=utf-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\nMr.X\r\n\r\n--alternative-boundary--\r\n\r\n--mixed-boundary--\r\n
EOT;
        $this->Body[] = $this->Header;
        $this->Body[] = "Direct String\r\n";
        $this->Body[] = $this->Header;

        $this->Body->addSection(new Section('multipart/mixed', 'mixed-boundary'));
        $this->Body->addSection(new Section('multipart/alternative', 'alternative-boundary'));

        $this->Body->addSection(new Section('multipart/foo', 'foo-boundary'));
        $this->Body->addPart($this->Part);
        $this->Body->endSection();
        $this->Body->addPart($this->Field);
        $this->Body->addPart($this->Field);

        $this->assertEquals(sprintf($Expected, $this->Body->getSection()->getBoundary()), @strval($this->Body), '(string) Body returned an invalid format');
    }
}
