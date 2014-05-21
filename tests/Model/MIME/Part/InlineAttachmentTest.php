<?php
/**
 * InlineAttachment.php | Mar 10, 2014
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
namespace BLW\Model\MIME\Part;

use BLW\Model\InvalidArgumentException;
use BLW\Model\GenericFile;
use BLW\Model\FileException;


/**
 * Tests BLW Library MIME InlineAttachment header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\MIME\Part\InlineAttachment
 */
class InlineAttachmentTest extends \PHPUnit_Framework_TestCase
{
    const FILE   = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVQYV2NgYAAAAAMAAWgmWQ0AAAAASUVORK5CYII=';
    const BASE64 = "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVQYV2NgYAAAAAMAAWgmWQ0A\r\nAAAASUVORK5CYII=";

    /**
     * @var \BLW\Model\MIME\InlineAttachment
     */
    protected $InlineAttachment = NULL;

    protected function setUp()
    {
        $this->InlineAttachment = new InlineAttachment(new GenericFile(self::FILE), 'Test.png', 'image/png-test');
    }

    protected function tearDown()
    {
        $this->InlineAttachment = NULL;
    }

    /**
     * @covers ::format
     */
    public function test_format()
    {
        $Excpected = substr(base64_encode(__FILE__), 0, 50);

        $this->assertStringStartsWith($Excpected, $this->InlineAttachment->format(__FILE__, 50), 'InlineAttachment::format() Returned an invalid value');
        $this->assertStringStartsWith($Excpected, $this->InlineAttachment->format(new \SplFileInfo(__FILE__), 50), 'InlineAttachment::format() Returned an invalid value');

        # Invalid arguments
        try {
            $this->InlineAttachment->format(0, 50);
            $this->fail('Failed to generate exception with invalid arguments');
        } catch (InvalidArgumentException $e) {}
    }

    /**
     * @depends test_format
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Check params
        $this->assertTrue(isset($this->InlineAttachment['Content-Type']), 'InlineAttachment::__construct() failed to set Content-Type');
        $this->assertTrue(isset($this->InlineAttachment['Content-Transfer-Encoding']), 'InlineAttachment::__construct() failed to set Content-Transfer-Encoding');
        $this->assertTrue(isset($this->InlineAttachment['Content-Disposition']), 'InlineAttachment::__construct() failed to set Content-Disposition');
        $this->assertTrue(isset($this->InlineAttachment['Content-ID']), 'InlineAttachment::__contruct failed to set Content-ID');
        $this->assertTrue(isset($this->InlineAttachment['Content-Location']), 'InlineAttachment::__contruct failed to set Content-Location');
        $this->assertTrue(isset($this->InlineAttachment['Content-Base']), 'InlineAttachment::__contruct failed to set Content-Base');
        $this->assertTrue(isset($this->InlineAttachment['Content']), 'InlineAttachment::__construct() failed to set Content');

        $this->assertInstanceOf('\\BLW\\Model\\MIME\\ContentType', $this->InlineAttachment['Content-Type'], 'InlineAttachment::__construct() set invalid Content-Type');
        $this->assertInstanceOf('\\BLW\\Model\\MIME\\ContentTransferEncoding', $this->InlineAttachment['Content-Transfer-Encoding'], 'InlineAttachment::__construct() set invalid Content-Transfer-Encoding');
        $this->assertInstanceOf('\\BLW\\Model\\MIME\\ContentDisposition', $this->InlineAttachment['Content-Disposition'], 'InlineAttachment::__construct() set invalid Content-Disposition');
        $this->assertInstanceOf('\\BLW\\Model\\MIME\\ContentID', $this->InlineAttachment['Content-ID'], 'InlineAttachment::__construct() set invalid Content-ID');
        $this->assertInstanceOf('\\BLW\\Model\\MIME\\ContentLocation', $this->InlineAttachment['Content-Location'], 'InlineAttachment::__construct() set invalid Content-Location');
        $this->assertInstanceOf('\\BLW\\Model\\MIME\\ContentBase', $this->InlineAttachment['Content-Base'], 'InlineAttachment::__construct() set invalid Content-Base');
        $this->assertEquals(self::BASE64 . "\r\n", $this->InlineAttachment['Content'], 'InlineAttachment::__construct() set invalid Content');

        # Invalid arguments
        try {
            new InlineAttachment(new GenericFile('z:\\undefined\\!!!'), 'Test.png', 'image/png-test');
            $this->fail('Failed to generate exception with invalid arguments');
        } catch (FileException $e) {}
    }

    /**
     * @depends test_construct
     * @covers ::__toString
     */
    public function test_toString()
    {
        $Expected = <<<EOT
Content-Type: image/png-test; name=Test.png\r\nContent-Transfer-Encoding: base64\r\nContent-Disposition: inline; filename=Test.png\r\nContent-ID: .*\r\nContent-Location: .*\r\nContent-Base: .*\r\n\r\niVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVQYV2NgYAAAAAMAAWgmWQ0A\r\nAAAASUVORK5CYII=\r\n\r\n
EOT;

        $this->assertRegExp("!^$Expected$!", @strval($this->InlineAttachment), 'InlineAttachment::__toSting() returned an invalid format');
    }

    /**
     * @depends test_construct
     * @covers ::offsetSet
     */
    public function test_offsetSet()
    {
        # Content-Type
        try {
            $this->InlineAttachment['Content-Type'] = 'foo';
            $this->fail('Failed generating warning on readonly offset');
        } catch (\PHPUnit_Framework_Error_Warning $e) {
            $this->assertContains('Cannot modify readonly offset', $e->getMessage(), 'Invalid warning: '.$e->getMessage());
        }

        @$this->InlineAttachment['Content-Type'] = 'foo';

        # Content-Transfer-Encoding
        try {
            $this->InlineAttachment['Content-Transfer-Encoding'] = 'foo';
            $this->fail('Failed generating warning on readonly offset');
        } catch (\PHPUnit_Framework_Error_Warning $e) {
            $this->assertContains('Cannot modify readonly offset', $e->getMessage(), 'Invalid warning: '.$e->getMessage());
        }

        @$this->InlineAttachment['Content-Transfer-Encoding'] = 'foo';

        # Content-Disposition
        try {
            $this->InlineAttachment['Content-Disposition'] = 'foo';
            $this->fail('Failed generating warning on readonly offset');
        } catch (\PHPUnit_Framework_Error_Warning $e) {
            $this->assertContains('Cannot modify readonly offset', $e->getMessage(), 'Invalid warning: '.$e->getMessage());
        }

        @$this->InlineAttachment['Content-Disposition'] = 'foo';

        # Content-ID
        try {
            $this->InlineAttachment['Content-ID'] = 'foo';
            $this->fail('Failed generating warning on readonly offset');
        } catch (\PHPUnit_Framework_Error_Warning $e) {
            $this->assertContains('Cannot modify readonly offset', $e->getMessage(), 'Invalid warning: '.$e->getMessage());
        }

        @$this->InlineAttachment['Content-ID'] = 'foo';

        # Content-Location
        try {
            $this->InlineAttachment['Content-Location'] = 'foo';
            $this->fail('Failed generating warning on readonly offset');
        } catch (\PHPUnit_Framework_Error_Warning $e) {
            $this->assertContains('Cannot modify readonly offset', $e->getMessage(), 'Invalid warning: '.$e->getMessage());
        }

        @$this->InlineAttachment['Content-Location'] = 'foo';

        # Content-Base
        try {
            $this->InlineAttachment['Content-Base'] = 'foo';
            $this->fail('Failed generating warning on readonly offset');
        } catch (\PHPUnit_Framework_Error_Warning $e) {
            $this->assertContains('Cannot modify readonly offset', $e->getMessage(), 'Invalid warning: '.$e->getMessage());
        }

        @$this->InlineAttachment['Content-Base'] = 'foo';

        # Content
        try {
            $this->InlineAttachment['Content'] = 'foo';
            $this->fail('Failed generating warning on readonly offset');
        } catch (\PHPUnit_Framework_Error_Warning $e) {
            $this->assertContains('Cannot modify readonly offset', $e->getMessage(), 'Invalid warning: '.$e->getMessage());
        }

        @$this->InlineAttachment['Content'] = 'foo';

        # Undefined
        $this->InlineAttachment['undefined'] = 'foo';
    }
}
