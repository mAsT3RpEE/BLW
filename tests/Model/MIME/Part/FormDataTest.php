<?php
/**
 * FormData.php | Apr 10, 2014
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
use BLW\Model\MIME\Part\FormData;
use BLW\Model\MIME\Part\FormField;


/**
 * Tests BLW Library MIME Part object.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\MIME\Part\FormData
 */
class FormDataTest extends \PHPUnit_Framework_TestCase
{
    const TEXT = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum erat nibh, mattis eget pretium sit amet, cursus vel nibh. Mauris semper hendrerit aliquam. Nullam aliquam consequat arcu quis scelerisque. Curabitur eu odio enim. Morbi porta neque eget rhoncus cursus. Aliquam adipiscing, massa nec lobortis dignissim, felis magna faucibus odio, eget interdum tellus eros lacinia tortor. Pellentesque molestie, eros vel fringilla dapibus, dolor nulla tempus quam, a malesuada nisl augue imperdiet nibh. Quisque nibh ipsum, molestie et felis ut, vulputate viverra tellus. Nam vel malesuada neque, in vulputate lacus. Quisque a tortor tellus. Cras et eros magna. Nullam blandit quam sit amet est dignissim vehicula. Donec nec augue congue, commodo urna vel, cursus purus. Phasellus condimentum tincidunt sodales. Donec placerat dapibus nisl, a bibendum mi consequat vehicula.\nDuis dignissim purus leo, quis pellentesque dolor placerat vel. Donec non nisi volutpat, varius quam at, aliquam magna. Sed a felis ac metus placerat ornare in vitae nulla. Aliquam tincidunt nisl eget turpis cursus rutrum. Curabitur a nisl id turpis hendrerit ultricies. Curabitur sit amet volutpat mi, eget eleifend nunc. Sed nec orci gravida, ornare est sed, rutrum est. Praesent quis porttitor dui. Quisque mattis nisi a pellentesque adipiscing. Morbi et lorem erat. Phasellus a lobortis neque.\nMaecenas vel pharetra magna. Pellentesque sit amet velit tempor, lobortis risus sit amet, ultricies lectus. Donec convallis sodales arcu, vestibulum consequat lectus vehicula eget. Morbi congue tortor sed molestie sagittis. Aliquam rutrum, turpis nec sodales semper, eros metus bibendum risus, ac porta metus tellus et mi. Nunc euismod dapibus dui sit amet ornare. In blandit magna eu eros ornare, in convallis enim posuere. In hac habitasse platea dictumst. Nulla facilisi. Aenean lectus velit, commodo quis felis in, varius sollicitudin nulla. Duis mollis erat leo, in accumsan massa auctor ac.";
    const HTML = "<div id=\"lipsum\">\n<p>\nLorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum erat nibh, mattis eget pretium sit amet, cursus vel nibh. Mauris semper hendrerit aliquam. Nullam aliquam consequat arcu quis scelerisque. Curabitur eu odio enim. Morbi porta neque eget rhoncus cursus. Aliquam adipiscing, massa nec lobortis dignissim, felis magna faucibus odio, eget interdum tellus eros lacinia tortor. Pellentesque molestie, eros vel fringilla dapibus, dolor nulla tempus quam, a malesuada nisl augue imperdiet nibh. Quisque nibh ipsum, molestie et felis ut, vulputate viverra tellus. Nam vel malesuada neque, in vulputate lacus. Quisque a tortor tellus. Cras et eros magna. Nullam blandit quam sit amet est dignissim vehicula. Donec nec augue congue, commodo urna vel, cursus purus. Phasellus condimentum tincidunt sodales. Donec placerat dapibus nisl, a bibendum mi consequat vehicula.\n</p>\n<p>\nDuis dignissim purus leo, quis pellentesque dolor placerat vel. Donec non nisi volutpat, varius quam at, aliquam magna. Sed a felis ac metus placerat ornare in vitae nulla. Aliquam tincidunt nisl eget turpis cursus rutrum. Curabitur a nisl id turpis hendrerit ultricies. Curabitur sit amet volutpat mi, eget eleifend nunc. Sed nec orci gravida, ornare est sed, rutrum est. Praesent quis porttitor dui. Quisque mattis nisi a pellentesque adipiscing. Morbi et lorem erat. Phasellus a lobortis neque.\n</p>\n<p>\nMaecenas vel pharetra magna. Pellentesque sit amet velit tempor, lobortis risus sit amet, ultricies lectus. Donec convallis sodales arcu, vestibulum consequat lectus vehicula eget. Morbi congue tortor sed molestie sagittis. Aliquam rutrum, turpis nec sodales semper, eros metus bibendum risus, ac porta metus tellus et mi. Nunc euismod dapibus dui sit amet ornare. In blandit magna eu eros ornare, in convallis enim posuere. In hac habitasse platea dictumst. Nulla facilisi. Aenean lectus velit, commodo quis felis in, varius sollicitudin nulla. Duis mollis erat leo, in accumsan massa auctor ac.\n</p></div>";

    /**
     * @var \BLW\Model\MIME\Part\FormField[]
     */
    protected $Fields = array();

    /**
     * @var \BLW\Model\MIME\Part\FormData
     */
    protected $FormData = NULL;

    protected function setUp()
    {
        $this->Fields   = array(
             new FormField('field1', 'text/plain', self::TEXT)
            ,new FormField('field2', 'text/plain', self::HTML)
        );

        $this->FormData = new FormData($this->Fields);
    }

    protected function tearDown()
    {
        $this->FormData = NULL;
    }

    /**
     * @covers ::format
     */
    public function test_format()
    {
        $Expected = http_build_query(array('field1' => self::TEXT, 'field2' => self::HTML));

        $this->assertSame($Expected, $this->FormData->format($this->Fields, 76), 'FormData::format() did not create valid quoted-printable string');

        # Invalid arguments
        try {
            $this->FormData->format(null, 50);
            $this->fail('Failed to generate exception with invalid arguments');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @depends test_format
     * @covers ::__construct
     */
    public function test_construct()
    {
        $this->FormData = new FormData($this->Fields);
        $Expected       = http_build_query(array('field1' => self::TEXT, 'field2' => self::HTML));

        # Check params
        $this->assertTrue(isset($this->FormData['Content']), 'FormData::__construct() failed to set Content');

        $this->assertEquals($Expected . "\r\n", quoted_printable_decode($this->FormData['Content']), 'FormData::__construct() set invalid Content');

        # Invalid arguments
    }

    /**
     * @depends test_construct
     * @covers ::__toString
     */
    public function test_toString()
    {
        $Expected = <<<EOT
field1=Lorem+ipsum+dolor+sit+amet%2C+consectetur+adipiscing+elit.+Vestibulum+erat+nibh%2C+mattis+eget+pretium+sit+amet%2C+cursus+vel+nibh.+Mauris+semper+hendrerit+aliquam.+Nullam+aliquam+consequat+arcu+quis+scelerisque.+Curabitur+eu+odio+enim.+Morbi+porta+neque+eget+rhoncus+cursus.+Aliquam+adipiscing%2C+massa+nec+lobortis+dignissim%2C+felis+magna+faucibus+odio%2C+eget+interdum+tellus+eros+lacinia+tortor.+Pellentesque+molestie%2C+eros+vel+fringilla+dapibus%2C+dolor+nulla+tempus+quam%2C+a+malesuada+nisl+augue+imperdiet+nibh.+Quisque+nibh+ipsum%2C+molestie+et+felis+ut%2C+vulputate+viverra+tellus.+Nam+vel+malesuada+neque%2C+in+vulputate+lacus.+Quisque+a+tortor+tellus.+Cras+et+eros+magna.+Nullam+blandit+quam+sit+amet+est+dignissim+vehicula.+Donec+nec+augue+congue%2C+commodo+urna+vel%2C+cursus+purus.+Phasellus+condimentum+tincidunt+sodales.+Donec+placerat+dapibus+nisl%2C+a+bibendum+mi+consequat+vehicula.%0ADuis+dignissim+purus+leo%2C+quis+pellentesque+dolor+placerat+vel.+Donec+non+nisi+volutpat%2C+varius+quam+at%2C+aliquam+magna.+Sed+a+felis+ac+metus+placerat+ornare+in+vitae+nulla.+Aliquam+tincidunt+nisl+eget+turpis+cursus+rutrum.+Curabitur+a+nisl+id+turpis+hendrerit+ultricies.+Curabitur+sit+amet+volutpat+mi%2C+eget+eleifend+nunc.+Sed+nec+orci+gravida%2C+ornare+est+sed%2C+rutrum+est.+Praesent+quis+porttitor+dui.+Quisque+mattis+nisi+a+pellentesque+adipiscing.+Morbi+et+lorem+erat.+Phasellus+a+lobortis+neque.%0AMaecenas+vel+pharetra+magna.+Pellentesque+sit+amet+velit+tempor%2C+lobortis+risus+sit+amet%2C+ultricies+lectus.+Donec+convallis+sodales+arcu%2C+vestibulum+consequat+lectus+vehicula+eget.+Morbi+congue+tortor+sed+molestie+sagittis.+Aliquam+rutrum%2C+turpis+nec+sodales+semper%2C+eros+metus+bibendum+risus%2C+ac+porta+metus+tellus+et+mi.+Nunc+euismod+dapibus+dui+sit+amet+ornare.+In+blandit+magna+eu+eros+ornare%2C+in+convallis+enim+posuere.+In+hac+habitasse+platea+dictumst.+Nulla+facilisi.+Aenean+lectus+velit%2C+commodo+quis+felis+in%2C+varius+sollicitudin+nulla.+Duis+mollis+erat+leo%2C+in+accumsan+massa+auctor+ac.&field2=%3Cdiv+id%3D%22lipsum%22%3E%0A%3Cp%3E%0ALorem+ipsum+dolor+sit+amet%2C+consectetur+adipiscing+elit.+Vestibulum+erat+nibh%2C+mattis+eget+pretium+sit+amet%2C+cursus+vel+nibh.+Mauris+semper+hendrerit+aliquam.+Nullam+aliquam+consequat+arcu+quis+scelerisque.+Curabitur+eu+odio+enim.+Morbi+porta+neque+eget+rhoncus+cursus.+Aliquam+adipiscing%2C+massa+nec+lobortis+dignissim%2C+felis+magna+faucibus+odio%2C+eget+interdum+tellus+eros+lacinia+tortor.+Pellentesque+molestie%2C+eros+vel+fringilla+dapibus%2C+dolor+nulla+tempus+quam%2C+a+malesuada+nisl+augue+imperdiet+nibh.+Quisque+nibh+ipsum%2C+molestie+et+felis+ut%2C+vulputate+viverra+tellus.+Nam+vel+malesuada+neque%2C+in+vulputate+lacus.+Quisque+a+tortor+tellus.+Cras+et+eros+magna.+Nullam+blandit+quam+sit+amet+est+dignissim+vehicula.+Donec+nec+augue+congue%2C+commodo+urna+vel%2C+cursus+purus.+Phasellus+condimentum+tincidunt+sodales.+Donec+placerat+dapibus+nisl%2C+a+bibendum+mi+consequat+vehicula.%0A%3C%2Fp%3E%0A%3Cp%3E%0ADuis+dignissim+purus+leo%2C+quis+pellentesque+dolor+placerat+vel.+Donec+non+nisi+volutpat%2C+varius+quam+at%2C+aliquam+magna.+Sed+a+felis+ac+metus+placerat+ornare+in+vitae+nulla.+Aliquam+tincidunt+nisl+eget+turpis+cursus+rutrum.+Curabitur+a+nisl+id+turpis+hendrerit+ultricies.+Curabitur+sit+amet+volutpat+mi%2C+eget+eleifend+nunc.+Sed+nec+orci+gravida%2C+ornare+est+sed%2C+rutrum+est.+Praesent+quis+porttitor+dui.+Quisque+mattis+nisi+a+pellentesque+adipiscing.+Morbi+et+lorem+erat.+Phasellus+a+lobortis+neque.%0A%3C%2Fp%3E%0A%3Cp%3E%0AMaecenas+vel+pharetra+magna.+Pellentesque+sit+amet+velit+tempor%2C+lobortis+risus+sit+amet%2C+ultricies+lectus.+Donec+convallis+sodales+arcu%2C+vestibulum+consequat+lectus+vehicula+eget.+Morbi+congue+tortor+sed+molestie+sagittis.+Aliquam+rutrum%2C+turpis+nec+sodales+semper%2C+eros+metus+bibendum+risus%2C+ac+porta+metus+tellus+et+mi.+Nunc+euismod+dapibus+dui+sit+amet+ornare.+In+blandit+magna+eu+eros+ornare%2C+in+convallis+enim+posuere.+In+hac+habitasse+platea+dictumst.+Nulla+facilisi.+Aenean+lectus+velit%2C+commodo+quis+felis+in%2C+varius+sollicitudin+nulla.+Duis+mollis+erat+leo%2C+in+accumsan+massa+auctor+ac.%0A%3C%2Fp%3E%3C%2Fdiv%3E\r\n\r\n
EOT;

        $this->assertEquals($Expected, @strval($this->FormData), 'FormData::__toSting() returned an invalid format');
    }

    /**
     * @covers ::offsetSet
     */
    public function test_offsetSet()
    {
        # Content
        try {
            $this->FormData['Content'] = 'foo';
            $this->fail('Failed generating warning on readonly offset');
        }

        catch (\PHPUnit_Framework_Error_Warning $e) {
            $this->assertContains('Cannot modify readonly offset', $e->getMessage(), 'Invalid warning: '.$e->getMessage());
        }

        @$this->FormData['Content'] = 'foo';

        # Undefined
        $this->FormData['undefined'] = 'foo';
    }
}