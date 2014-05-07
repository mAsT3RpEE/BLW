<?php
/**
 * FormFile.php | Apr 10, 2014
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
use BLW\Model\MIME\Part\FormFile;
use BLW\Model\GenericFile;


/**
 * Tests BLW Library MIME FormFile header.
 * @package BLW\MIME
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\MIME\Part\FormFile
 */
class FormFileTest extends \PHPUnit_Framework_TestCase
{
    const FILE   = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVQYV2NgYAAAAAMAAWgmWQ0AAAAASUVORK5CYII=';

    /**
     * @var \FormFile
     */
    protected $FormFile = NULL;

    protected function setUp()
    {
        $this->FormFile = new FormFile('fieldname', new GenericFile(self::FILE), 'Test.png', 'image/png-test');
    }

    protected function tearDown()
    {
        $this->FormFile = NULL;
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        $this->FormFile = new FormFile('fieldname', new GenericFile(self::FILE), 'Test.png', 'image/png-test');

        # Check properties
        $this->assertTrue(isset($this->FormFile['Content-Disposition']), 'FormFile::__construct() failed to set Content-Disposition');
        $this->assertTrue(isset($this->FormFile['Content-Type']), 'FormFile::__construct() failed to set Content-Type');
        $this->assertTrue(isset($this->FormFile['Content-Transfer-Encoding']), 'FormFile::__construct() failed to set Content-Transfer-Encoding');
        $this->assertTrue(isset($this->FormFile['Content']), 'FormFile::__construct() failed to set Content');

        $this->assertInstanceOf('\\BLW\\Model\\MIME\\ContentDisposition', $this->FormFile['Content-Disposition'], 'FormFile::__construct() set invalid Content-Disposition');
        $this->assertInstanceOf('\\BLW\\Model\\MIME\\ContentType', $this->FormFile['Content-Type'], 'FormFile::__construct() set invalid Content-Type');
        $this->assertInstanceOf('\\BLW\\Model\MIME\\ContentTransferEncoding', $this->FormFile['Content-Transfer-Encoding'], 'FormFile::__construct() set invalid Content-Transfer-Encoding');
        $this->assertEquals(file_get_contents(self::FILE) . "\r\n", $this->FormFile['Content'], 'FormFile::__construct() set invalid Content');

        # Invalid arguments
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $Expected = <<<EOT
Content-Disposition: form-data; name=fieldname; filename=Test.png\r\nContent-Type: image/png-test\r\nContent-Transfer-Encoding: binary\r\n\r\n%s\r\n\r\n
EOT;

        $this->assertEquals(sprintf($Expected, file_get_contents(self::FILE)), @strval($this->FormFile), 'FormFile::__toSting() returned an invalid format');
    }

    /**
     * @covers ::offsetSet
     */
    public function test_offsetSet()
    {
        # Content-Disposition
        try {
            $this->FormFile['Content-Disposition'] = 'foo';
            $this->fail('Failed generating warning on readonly offset');
        }

        catch (\PHPUnit_Framework_Error_Warning $e) {
            $this->assertContains('Cannot modify readonly offset', $e->getMessage(), 'Invalid warning: '.$e->getMessage());
        }

        # Content-Type
        try {
            $this->FormFile['Content-Type'] = 'foo';
            $this->fail('Failed generating warning on readonly offset');
        }

        catch (\PHPUnit_Framework_Error_Warning $e) {
            $this->assertContains('Cannot modify readonly offset', $e->getMessage(), 'Invalid warning: '.$e->getMessage());
        }

        # Content-Transfer-Encoding
        try {
            $this->FormFile['Content-Transfer-Encoding'] = 'foo';
            $this->fail('Failed generating warning on readonly offset');
        }

        catch (\PHPUnit_Framework_Error_Warning $e) {
            $this->assertContains('Cannot modify readonly offset', $e->getMessage(), 'Invalid warning: '.$e->getMessage());
        }

        # Content
        try {
            $this->FormFile['Content'] = 'foo';
            $this->fail('Failed generating warning on readonly offset');
        }

        catch (\PHPUnit_Framework_Error_Warning $e) {
            $this->assertContains('Cannot modify readonly offset', $e->getMessage(), 'Invalid warning: '.$e->getMessage());
        }
    }
}
