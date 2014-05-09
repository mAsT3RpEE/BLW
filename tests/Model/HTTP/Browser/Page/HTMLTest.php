<?php
/**
 * HTMLTest.php | May 15, 2014
 *
 * @filesource
 * @license MIT
 * @copyright Copyright (c) 2013-2018, mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * @package BLW\HTTP
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model\HTTP\Browser\Page;


use BLW\Model\HTTP\Browser\Page\HTML as Page;
use BLW\Model\DOM\Document;
use BLW\Model\MIME\Head\RFC2616 as Head;
use BLW\Model\MIME\Body\RFC2616 as Body;
use BLW\Model\GenericURI;
use BLW\Model\Mediator\Symfony as Mediator;


/**
 * Tests BLW Library page object
 * @package BLW\HTTP
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\HTTP\Browser\Page\HTML
 */
class HTMLTest  extends \PHPUnit_Framework_TestCase
{
    /**
     * @construct ::__construct
     */
    public function test_construct()
    {
        $Component = new Document;
        $Head      = new Head;
        $URI       = new GenericURI('http://a/b/c/d;p?q#f');
        $Mediator  = new Mediator;
        $Page      = new Page($Component, $URI, $Head, $Head, $Mediator);

        $this->assertAttributeSame($Component, '_Component', $Page, 'HTML::__construct() Failed to set $_Component');
        $this->assertAttributeSame($URI, '_Base', $Page, 'HTML::__construct() Failed to set $_Base');
        $this->assertAttributeSame($Head, '_RequestHead', $Page, 'HTML::__construct() Failed to set $_RequestHead');
        $this->assertAttributeSame($Head, '_ResponseHead', $Page, 'HTML::__construct() Failed to set $_ResponseHead');

        $this->assertSame('Browser', $Page->getMediatorID(), 'HTML::__construct() Failed to set MediatorID');
    }
}