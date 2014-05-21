<?php
/**
 * QuotedPrintable.php | Mar 20, 2014
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


/**
 * Tests BLW Library MIME Part header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\MIME\Part\QuotedPrintable
 */
class QuotedPrintableTest extends \PHPUnit_Framework_TestCase
{
    const TEXT = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum erat nibh, mattis eget pretium sit amet, cursus vel nibh. Mauris semper hendrerit aliquam. Nullam aliquam consequat arcu quis scelerisque. Curabitur eu odio enim. Morbi porta neque eget rhoncus cursus. Aliquam adipiscing, massa nec lobortis dignissim, felis magna faucibus odio, eget interdum tellus eros lacinia tortor. Pellentesque molestie, eros vel fringilla dapibus, dolor nulla tempus quam, a malesuada nisl augue imperdiet nibh. Quisque nibh ipsum, molestie et felis ut, vulputate viverra tellus. Nam vel malesuada neque, in vulputate lacus. Quisque a tortor tellus. Cras et eros magna. Nullam blandit quam sit amet est dignissim vehicula. Donec nec augue congue, commodo urna vel, cursus purus. Phasellus condimentum tincidunt sodales. Donec placerat dapibus nisl, a bibendum mi consequat vehicula.\nDuis dignissim purus leo, quis pellentesque dolor placerat vel. Donec non nisi volutpat, varius quam at, aliquam magna. Sed a felis ac metus placerat ornare in vitae nulla. Aliquam tincidunt nisl eget turpis cursus rutrum. Curabitur a nisl id turpis hendrerit ultricies. Curabitur sit amet volutpat mi, eget eleifend nunc. Sed nec orci gravida, ornare est sed, rutrum est. Praesent quis porttitor dui. Quisque mattis nisi a pellentesque adipiscing. Morbi et lorem erat. Phasellus a lobortis neque.\nMaecenas vel pharetra magna. Pellentesque sit amet velit tempor, lobortis risus sit amet, ultricies lectus. Donec convallis sodales arcu, vestibulum consequat lectus vehicula eget. Morbi congue tortor sed molestie sagittis. Aliquam rutrum, turpis nec sodales semper, eros metus bibendum risus, ac porta metus tellus et mi. Nunc euismod dapibus dui sit amet ornare. In blandit magna eu eros ornare, in convallis enim posuere. In hac habitasse platea dictumst. Nulla facilisi. Aenean lectus velit, commodo quis felis in, varius sollicitudin nulla. Duis mollis erat leo, in accumsan massa auctor ac.";
    const HTML = "<div id=\"lipsum\">\n<p>\nLorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum erat nibh, mattis eget pretium sit amet, cursus vel nibh. Mauris semper hendrerit aliquam. Nullam aliquam consequat arcu quis scelerisque. Curabitur eu odio enim. Morbi porta neque eget rhoncus cursus. Aliquam adipiscing, massa nec lobortis dignissim, felis magna faucibus odio, eget interdum tellus eros lacinia tortor. Pellentesque molestie, eros vel fringilla dapibus, dolor nulla tempus quam, a malesuada nisl augue imperdiet nibh. Quisque nibh ipsum, molestie et felis ut, vulputate viverra tellus. Nam vel malesuada neque, in vulputate lacus. Quisque a tortor tellus. Cras et eros magna. Nullam blandit quam sit amet est dignissim vehicula. Donec nec augue congue, commodo urna vel, cursus purus. Phasellus condimentum tincidunt sodales. Donec placerat dapibus nisl, a bibendum mi consequat vehicula.\n</p>\n<p>\nDuis dignissim purus leo, quis pellentesque dolor placerat vel. Donec non nisi volutpat, varius quam at, aliquam magna. Sed a felis ac metus placerat ornare in vitae nulla. Aliquam tincidunt nisl eget turpis cursus rutrum. Curabitur a nisl id turpis hendrerit ultricies. Curabitur sit amet volutpat mi, eget eleifend nunc. Sed nec orci gravida, ornare est sed, rutrum est. Praesent quis porttitor dui. Quisque mattis nisi a pellentesque adipiscing. Morbi et lorem erat. Phasellus a lobortis neque.\n</p>\n<p>\nMaecenas vel pharetra magna. Pellentesque sit amet velit tempor, lobortis risus sit amet, ultricies lectus. Donec convallis sodales arcu, vestibulum consequat lectus vehicula eget. Morbi congue tortor sed molestie sagittis. Aliquam rutrum, turpis nec sodales semper, eros metus bibendum risus, ac porta metus tellus et mi. Nunc euismod dapibus dui sit amet ornare. In blandit magna eu eros ornare, in convallis enim posuere. In hac habitasse platea dictumst. Nulla facilisi. Aenean lectus velit, commodo quis felis in, varius sollicitudin nulla. Duis mollis erat leo, in accumsan massa auctor ac.\n</p></div>";

    /**
     * @var \BLW\Model\MIME\Part\QuotedPrintable
     */
    protected $QuotedPrintable = NULL;

    protected function setUp()
    {
        $this->QuotedPrintable = new QuotedPrintable('text/html', self::HTML, 'utf-8');
    }

    protected function tearDown()
    {
        $this->QuotedPrintable = NULL;
    }

    /**
     * @covers ::format
     */
    public function test_format()
    {
        $this->assertSame(self::TEXT, quoted_printable_decode($this->QuotedPrintable->format(self::TEXT, 76)), 'QuotedPrintable::fomrat() did not create valid quoted-printable string');

        # Invalid value
        try {
            $this->QuotedPrintable->format(null, 50);
            $this->fail('Failed to generate Exception with invalid arguments');
        } catch (InvalidArgumentException $e) {}
    }

    /**
     * @depends test_format
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Check params
        $this->assertTrue(isset($this->QuotedPrintable['Content-Type']), 'QuotedPrintable::__construct() failed to set Content-Type');
        $this->assertTrue(isset($this->QuotedPrintable['Content-Transfer-Encoding']), 'QuotedPrintable::__construct() failed to set Content-Transfer-Encoding');
        $this->assertTrue(isset($this->QuotedPrintable['Content']), 'QuotedPrintable::__construct() failed to set Content');

        $this->assertInstanceOf('\\BLW\\Model\\MIME\\ContentType', $this->QuotedPrintable['Content-Type'], 'QuotedPrintable::__construct() set invalid Content-Type');
        $this->assertInstanceOf('\\BLW\\Model\\MIME\\ContentTransferEncoding', $this->QuotedPrintable['Content-Transfer-Encoding'], 'QuotedPrintable::__construct() set invalid Content-Transfer-Encoding');
        $this->assertEquals(self::HTML . "\r\n", quoted_printable_decode($this->QuotedPrintable['Content']), 'QuotedPrintable::__construct() set invalid Content');

        # Invalid arguments
        try {
            new QuotedPrintable('text/plain', null);
            $this->fail('Failed to generate exception with invalid arguments');
        } catch (InvalidArgumentException $e) {}
    }

    /**
     * @depends test_construct
     * @covers ::__toString
     */
    public function test_toString()
    {
        $Start = <<<EOT
Content-Type: text/html; charset=utf-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\n<div id=3D"lipsum">=0A<p>
EOT;
        $End = <<<EOT
>\r\n\r\n
EOT;

        $this->assertStringStartsWith($Start, @strval($this->QuotedPrintable), 'QuotedPrintable::__toSting() returned an invalid format');
        $this->assertStringEndsWith($End, @strval($this->QuotedPrintable), 'QuotedPrintable::__toSting() returned an invalid format');
    }

    /**
     * @depends test_construct
     * @covers ::offsetSet
     */
    public function test_offsetSet()
    {
        # Content-Type
        try {
            $this->QuotedPrintable['Content-Type'] = 'foo';
            $this->fail('Failed generating warning on readonly offset');
        } catch (\PHPUnit_Framework_Error_Warning $e) {
            $this->assertContains('Cannot modify readonly offset', $e->getMessage(), 'Invalid warning: '.$e->getMessage());
        }

        @$this->QuotedPrintable['Content-Type'] = 'foo';

        # Content-Transfer-Encoding
        try {
            $this->QuotedPrintable['Content-Transfer-Encoding'] = 'foo';
            $this->fail('Failed generating warning on readonly offset');
        } catch (\PHPUnit_Framework_Error_Warning $e) {
            $this->assertContains('Cannot modify readonly offset', $e->getMessage(), 'Invalid warning: '.$e->getMessage());
        }

        @$this->QuotedPrintable['Content-Transfer-Encoding'] = 'foo';

        # Content
        try {
            $this->QuotedPrintable['Content'] = 'foo';
            $this->fail('Failed generating warning on readonly offset');
        } catch (\PHPUnit_Framework_Error_Warning $e) {
            $this->assertContains('Cannot modify readonly offset', $e->getMessage(), 'Invalid warning: '.$e->getMessage());
        }

        @$this->QuotedPrintable['Content'] = 'foo';

        # Undefined
        $this->QuotedPrintable['undefined'] = 'foo';
    }
}
