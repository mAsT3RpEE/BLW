<?php
/**
 * RFC822.php | Mar 20, 2014
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

use ReflectionProperty;

use BLW\Type\MIME\IHeader;

use BLW\Model\GenericFile;

use BLW\Model\MIME\Body\RFC822 as Body;
use BLW\Model\MIME\Section;
use BLW\Model\MIME\Part\Attachment;
use BLW\Model\MIME\Part\QuotedPrintable;


/**
 * Tests BLW Library MIME Body header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\Body\RFC822
 */
class RFC822Test extends \PHPUnit_Framework_TestCase
{
    const FILE   = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVQYV2NgYAAAAAMAAWgmWQ0AAAAASUVORK5CYII=';

    /**
     * @var \BLW\Model\MIME\Attachment
     */
    protected $Attachment = NULL;

    /**
     * @var \BLW\Model\MIME\Part
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
     * @var \BLW\Model\MIME\Body
     */
    protected $Body = NULL;

    protected function setUp()
    {
        $this->Header = $this->getMockForAbstractClass('\\BLW\\Type\\MIME\\IHeader');
        $this->Header
            ->expects($this->any())
            ->method('__toString')
            ->will($this->returnValue("Mock Header: foo\r\n"));

        $this->Attachment       = new Attachment(new GenericFile(self::FILE), 'test.png', 'image/png');
        $this->Part             = new QuotedPrintable('text/plain', 'Test content', 'utf-8');
        $this->Section          = new Section('multipart/mixed', 'mixed-boundary');
        $this->Body             = new Body($this->Section);
    }

    protected function tearDown()
    {
        $this->Header           = NULL;
        $this->Attachment       = NULL;
        $this->InlineAttachment = NULL;
        $this->Section          = NULL;
        $this->Body             = NULL;
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Check properties
        $Sections = new ReflectionProperty($this->Body, '_Sections');

        $Sections->setAccessible(true);

        $this->assertSame(array($this->Section), $Sections->getValue($this->Body), 'Body::__construct() failed to set $_Sections');
    }

    /**
     * @depends test_construct
     * @covers ::getSection
     */
    public function test_getSection()
    {
        $this->assertSame($this->Section, $this->Body->getSection(), 'Body::getSection() returned an invalid result');
    }

    /**
     * @depends test_getSection
     * @covers ::addSection
     */
    public function test_addSection()
    {
        $Expected = new Section('multipart/mixed', 'xxx-xxx');

        $this->assertTrue($this->Body->addSection($Expected), 'Body::addSection() should return true');
        $this->assertSame($Expected, $this->Body->getSection(), 'Body::addSection() failed to modify $_Sections');
        $this->assertEquals($this->Section->createBoundary(), $this->Body[count($this->Body)-3], 'Body::addSection() failed to add a mime boundary');
    }

    /**
     * @depends test_addSection
     * @covers ::endSection
     */
    public function test_endSection()
    {
        $Expected = new Section('multipart/mixed', 'xxx-xxx');

        $this->assertTrue($this->Body->addSection($Expected), 'Body::addSection() should return true');
        $this->assertTrue($this->Body->endSection(), 'Body::endSection() should return true');
        $this->assertSame($this->Section, $this->Body->getSection(), 'Body::endSection failed to modify $_Sections');
        $this->assertTrue($this->Body->endSection(), 'Body::endSection() should return true');
        $this->assertNULL($this->Body->getSection(), 'Body::endSection failed to modify $_Sections');
        $this->assertFalse($this->Body->endSection(), 'Body::endSection() should return true');
    }

    /**
     * @depends test_getSection
     * @covers ::addPart
     */
    public function test_addPart()
    {
        # Valid arguments
        $this->assertTrue($this->Body->addPart($this->Attachment), 'Body::addPart() should return true');
        $this->assertSame($this->Attachment, $this->Body[count($this->Body)-1], 'Body::addPart() failed to modify $_Storage');
        $this->assertEquals($this->Body->getSection()->createBoundary(), $this->Body[count($this->Body)-2], 'Body::addPart() failed to add a mime boundary');

        $this->assertTrue($this->Body->addPart($this->Part), 'Body::addPart() should return true');
        $this->assertSame($this->Part, $this->Body[count($this->Body)-1], 'Body::addPart() failed to modify $_Storage');
        $this->assertEquals($this->Body->getSection()->createBoundary(), $this->Body[count($this->Body)-2], 'Body::addSection() failed to add a mime boundary');

        # Invalid arguments
        try {
            $this->Body->addPart(NULL);
            $this->fail('Failed to generate exception with invalid arguments');
        } catch (\PHPUnit_Framework_Error $e) {}

        # Empty sections
        $this->assertTrue($this->Body->endSection(), 'Body::endSection() should return true');
        $this->assertFalse($this->Body->addPart($this->Attachment), 'Body::addPart() should return false');
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
Mock Header: foo\r\nDirect String\r\nMock Header: foo\r\n--mixed-boundary\r\nContent-Type: text/plain; boundary="foo-boundary"\r\n\r\n--foo-boundary\r\nContent-Type: multipart/alternative; boundary="alternative-boundary"\r\n\r\n--alternative-boundary\r\nContent-Type: text/plain; charset=utf-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\nTest content\r\n\r\n--alternative-boundary--\r\n\r\n--foo-boundary\r\nContent-Type: image/png; name=test.png\r\nContent-Transfer-Encoding: base64\r\nContent-Disposition: attachment; filename=test.png\r\n\r\niVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVQYV2NgYAAAAAMAAWgmWQ0A\r\nAAAASUVORK5CYII=\r\n\r\n--foo-boundary\r\nContent-Type: image/png; name=test.png\r\nContent-Transfer-Encoding: base64\r\nContent-Disposition: attachment; filename=test.png\r\n\r\niVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVQYV2NgYAAAAAMAAWgmWQ0A\r\nAAAASUVORK5CYII=\r\n\r\n--foo-boundary--\r\n\r\n--mixed-boundary--\r\n
EOT;

        $this->Body[]           = $this->Header;
        $this->Body[]           = "Direct String\r\n";
        $this->Body[]           = $this->Header;

        $this->Body->addSection(new Section('foo', 'foo-boundary'));
        $this->Body->addSection(new Section('multipart/alternative', 'alternative-boundary'));
        $this->Body->addPart($this->Part);
        $this->Body->endSection();
        $this->Body->addPart($this->Attachment);
        $this->Body->addPart($this->Attachment);

        $this->assertEquals(sprintf($Expected, $this->Body->getSection()->getBoundary()), @strval($this->Body), '(string) Body returned an invalid format');
    }
}
