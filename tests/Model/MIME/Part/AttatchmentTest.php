<?php
/**
 * Attachment.php | Mar 10, 2014
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
namespace BLW\Tests\Model\MIME\Part;

use BLW\Model\InvalidArgumentException;
use BLW\Model\MIME\Part\Attachment;
use BLW\Model\GenericFile;


/**
 * Tests BLW Library MIME Attachment header.
 * @package BLW\MIME
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\MIME\Part\Attachment
 */
class AttachmentTest extends \PHPUnit_Framework_TestCase
{
    const FILE   = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVQYV2NgYAAAAAMAAWgmWQ0AAAAASUVORK5CYII=';
    const BASE64 = "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVQYV2NgYAAAAAMAAWgmWQ0A\r\nAAAASUVORK5CYII=";

    /**
     * @var \Attachment
     */
    protected $Attachment = NULL;

    protected function setUp()
    {
        $this->Attachment = new Attachment(new GenericFile(self::FILE), 'Test.png', 'image/png-test');
    }

    protected function tearDown()
    {
        $this->Attachment = NULL;
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Check params
        $this->assertTrue(isset($this->Attachment['Content-Type']), 'Attachment::__construct() failed to set Content-Type');
        $this->assertTrue(isset($this->Attachment['Content-Transfer-Encoding']), 'Attachment::__construct() failed to set Content-Transfer-Encoding');
        $this->assertTrue(isset($this->Attachment['Content-Disposition']), 'Attachment::__construct() failed to set Content-Disposition');
        $this->assertTrue(isset($this->Attachment['Content']), 'Attachment::__construct() failed to set Content');

        $this->assertInstanceOf('\\BLW\\Model\\MIME\\ContentType', $this->Attachment['Content-Type'], 'Attachment::__construct() set invalid Content-Type');
        $this->assertInstanceOf('\\BLW\\Model\MIME\\ContentTransferEncoding', $this->Attachment['Content-Transfer-Encoding'], 'Attachment::__construct() set invalid Content-Transfer-Encoding');
        $this->assertInstanceOf('\\BLW\\Model\\MIME\\ContentDisposition', $this->Attachment['Content-Disposition'], 'Attachment::__construct() set invalid Content-Disposition');
        $this->assertEquals(self::BASE64 . "\r\n", $this->Attachment['Content'], 'Attachment::__construct() set invalid Content');

        # Invalid arguments
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $Expected = <<<EOT
Content-Type: image/png-test; name=Test.png\r\nContent-Transfer-Encoding: base64\r\nContent-Disposition: attachment; filename=Test.png\r\n\r\niVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVQYV2NgYAAAAAMAAWgmWQ0A\r\nAAAASUVORK5CYII=\r\n\r\n
EOT;

        $this->assertEquals($Expected, @strval($this->Attachment), 'Attachment::__toSting() returned an invalid format');
    }

    /**
     * @covers ::offsetSet
     */
    public function test_offsetSet()
    {
        # Content-Type
        try {
            $this->Attachment['Content-Type'] = 'foo';
            $this->fail('Failed generating warning on readonly offset');
        }

        catch (\PHPUnit_Framework_Error_Warning $e) {
            $this->assertContains('Cannot modify readonly offset', $e->getMessage(), 'Invalid warning: '.$e->getMessage());
        }

        # Content-Transfer-Encoding
        try {
            $this->Attachment['Content-Transfer-Encoding'] = 'foo';
            $this->fail('Failed generating warning on readonly offset');
        }

        catch (\PHPUnit_Framework_Error_Warning $e) {
            $this->assertContains('Cannot modify readonly offset', $e->getMessage(), 'Invalid warning: '.$e->getMessage());
        }

        # Content-Disposition
        try {
            $this->Attachment['Content-Disposition'] = 'foo';
            $this->fail('Failed generating warning on readonly offset');
        }

        catch (\PHPUnit_Framework_Error_Warning $e) {
            $this->assertContains('Cannot modify readonly offset', $e->getMessage(), 'Invalid warning: '.$e->getMessage());
        }

        # Content
        try {
            $this->Attachment['Content'] = 'foo';
            $this->fail('Failed generating warning on readonly offset');
        }

        catch (\PHPUnit_Framework_Error_Warning $e) {
            $this->assertContains('Cannot modify readonly offset', $e->getMessage(), 'Invalid warning: '.$e->getMessage());
        }
    }
}
