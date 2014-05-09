<?php
/**
 * IfModifiedSince.php | Apr 8, 2014
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
namespace BLW\Model\MIME;

use DateTime;
use BLW\Model\InvalidArgumentException;
use BLW\Model\MIME\IfModifiedSince;


/**
 * Tests BLW Library MIME IfModifiedSince header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\IfModifiedSince
 */
class IfModifiedSinceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \DateTime
     */
    protected $IfModifiedSince = NULL;
    /**
     * @var \BLW\Model\MIME\ContentType
     */
    protected $Header = NULL;

    protected function setUp()
    {
        $this->IfModifiedSince = new DateTime;
        $this->Header          = new IfModifiedSince($this->IfModifiedSince);
    }

    protected function tearDown()
    {
        $this->Header          = NULL;
        $this->IfModifiedSince = NULL;
    }

    public function generateValidDates()
    {
        return array(
        	 array(new DateTime('tomorrow'))
        	,array(new DateTime('+1week'))
        	,array(new DateTime('last year'))
        );
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Check params
        $this->assertAttributeEquals('If-Modified-Since', '_Type', $this->Header, 'IfModifiedSince::__construct() failed to set $_Type');
        $this->assertAttributeSame($this->IfModifiedSince->format(IfModifiedSince::FORMAT), '_Value', $this->Header, 'IfModifiedSince::__construct() failed to set $_Value');

        # Valid arguments
        foreach($this->generateValidDates() as $Arguments) {
            list($IfModifiedSince) = $Arguments;

            $this->Header = new IfModifiedSince($IfModifiedSince);

            $this->assertAttributeEquals($IfModifiedSince->format(IfModifiedSince::FORMAT), '_Value', $this->Header, 'IfModifiedSince::__construct() failed to set $_Value');
        }

        # NULL Date
        $this->Header = new IfModifiedSince;
        $Expected     = new DateTime;

        $this->assertAttributeContains($Expected->format(substr(LastModified::FORMAT, 0, -5)), '_Value', $this->Header, 'IfModifiedSince::__construct() failed to set $_Value');

        # Invalid arguments
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $Expected = sprintf("If-Modified-Since: %s\r\n", $this->IfModifiedSince->format(IfModifiedSince::FORMAT));
        $this->assertEquals($Expected, @strval($this->Header), 'IfModifiedSince::__toSting() returned an invalid format');
    }
}