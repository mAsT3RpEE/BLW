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
namespace BLW\Tests\Model\MIME\Part;

use BLW\Model\InvalidArgumentException;
use BLW\Model\MIME\Part\QuotedPrintable;


/**
 * Tests BLW Library MIME Part header.
 * @package BLW\MIME
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\Part
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
    }

    /**
     * @depends test_construct
     * @covers ::__toString
     */
    public function test_toString()
    {
        $Expected = <<<EOT
Content-Type: text/html; charset=utf-8
Content-Transfer-Encoding: quoted-printable

<div id=3D"lipsum">=0A<p>=0ALorem=20ipsum=20dolor=20sit=20amet,=20consectet=
ur=20adipiscing=20elit.=20Vestibulum=20erat=20nibh,=20mattis=20eget=20preti=
um=20sit=20amet,=20cursus=20vel=20nibh.=20Mauris=20semper=20hendrerit=20ali=
quam.=20Nullam=20aliquam=20consequat=20arcu=20quis=20scelerisque.=20Curabit=
ur=20eu=20odio=20enim.=20Morbi=20porta=20neque=20eget=20rhoncus=20cursus.=
=20Aliquam=20adipiscing,=20massa=20nec=20lobortis=20dignissim,=20felis=20ma=
gna=20faucibus=20odio,=20eget=20interdum=20tellus=20eros=20lacinia=20tortor=
.=20Pellentesque=20molestie,=20eros=20vel=20fringilla=20dapibus,=20dolor=20=
nulla=20tempus=20quam,=20a=20malesuada=20nisl=20augue=20imperdiet=20nibh.=
=20Quisque=20nibh=20ipsum,=20molestie=20et=20felis=20ut,=20vulputate=20vive=
rra=20tellus.=20Nam=20vel=20malesuada=20neque,=20in=20vulputate=20lacus.=20=
Quisque=20a=20tortor=20tellus.=20Cras=20et=20eros=20magna.=20Nullam=20bland=
it=20quam=20sit=20amet=20est=20dignissim=20vehicula.=20Donec=20nec=20augue=
=20congue,=20commodo=20urna=20vel,=20cursus=20purus.=20Phasellus=20condimen=
tum=20tincidunt=20sodales.=20Donec=20placerat=20dapibus=20nisl,=20a=20biben=
dum=20mi=20consequat=20vehicula.=0A</p>=0A<p>=0ADuis=20dignissim=20purus=20=
leo,=20quis=20pellentesque=20dolor=20placerat=20vel.=20Donec=20non=20nisi=
=20volutpat,=20varius=20quam=20at,=20aliquam=20magna.=20Sed=20a=20felis=20a=
c=20metus=20placerat=20ornare=20in=20vitae=20nulla.=20Aliquam=20tincidunt=
=20nisl=20eget=20turpis=20cursus=20rutrum.=20Curabitur=20a=20nisl=20id=20tu=
rpis=20hendrerit=20ultricies.=20Curabitur=20sit=20amet=20volutpat=20mi,=20e=
get=20eleifend=20nunc.=20Sed=20nec=20orci=20gravida,=20ornare=20est=20sed,=
=20rutrum=20est.=20Praesent=20quis=20porttitor=20dui.=20Quisque=20mattis=20=
nisi=20a=20pellentesque=20adipiscing.=20Morbi=20et=20lorem=20erat.=20Phasel=
lus=20a=20lobortis=20neque.=0A</p>=0A<p>=0AMaecenas=20vel=20pharetra=20magn=
a.=20Pellentesque=20sit=20amet=20velit=20tempor,=20lobortis=20risus=20sit=
=20amet, ultricies lectus. Donec convallis sodales arcu, vestibulum consequ=
at lectus vehicula eget. Morbi congue tortor sed molestie sagittis. Aliquam=
 rutrum, turpis nec sodales semper, eros metus bibendum risus, ac porta met=
us tellus et mi. Nunc euismod dapibus dui sit amet ornare. In blandit magna=
 eu eros ornare, in convallis enim posuere. In hac habitasse platea dictums=
t. Nulla facilisi. Aenean lectus velit, commodo quis felis in, varius solli=
citudin nulla. Duis mollis erat leo, in accumsan massa auctor ac.=0A</p></d=
iv>


EOT;

        $this->assertEquals($Expected, @strval($this->QuotedPrintable), 'QuotedPrintable::__toSting() returned an invalid format');
    }

    /**
     * @covers ::offsetSet
     */
    public function test_offsetSet()
    {
        # Content-Type
        try {
            $this->QuotedPrintable['Content-Type'] = 'foo';
            $this->fail('Failed generating warning on readonly offset');
        }

        catch (\PHPUnit_Framework_Error_Warning $e) {
            $this->assertContains('Cannot modify readonly offset', $e->getMessage(), 'Invalid warning: '.$e->getMessage());
        }

        # Content-Transfer-Encoding
        try {
            $this->QuotedPrintable['Content-Transfer-Encoding'] = 'foo';
            $this->fail('Failed generating warning on readonly offset');
        }

        catch (\PHPUnit_Framework_Error_Warning $e) {
            $this->assertContains('Cannot modify readonly offset', $e->getMessage(), 'Invalid warning: '.$e->getMessage());
        }

        # Content
        try {
            $this->QuotedPrintable['Content'] = 'foo';
            $this->fail('Failed generating warning on readonly offset');
        }

        catch (\PHPUnit_Framework_Error_Warning $e) {
            $this->assertContains('Cannot modify readonly offset', $e->getMessage(), 'Invalid warning: '.$e->getMessage());
        }
    }
}
