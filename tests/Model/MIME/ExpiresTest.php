<?php
/**
 * Expires.php | Apr 8, 2014
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


/**
 * Tests BLW Library MIME Expires header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\Expires
 */
class ExpiresTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \DateTime
     */
    protected $Expires = NULL;
    /**
     * @var \BLW\Model\MIME\ContentType
     */
    protected $Header = NULL;

    protected function setUp()
    {
        $this->Expires    = new DateTime;
        $this->Header     = new Expires($this->Expires);
    }

    protected function tearDown()
    {
        $this->Header     = NULL;
        $this->Expires    = NULL;
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
        $this->assertAttributeEquals('Expires', '_Type', $this->Header, 'Expires::__construct() failed to set $_Type');
        $this->assertAttributeSame($this->Expires->format(Expires::FORMAT), '_Value', $this->Header, 'Expires::__construct() failed to set $_Value');

        # Valid arguments
        foreach ($this->generateValidDates() as $Arguments) {
            list($Expires) = $Arguments;

            $this->Header = new Expires($Expires);

            $this->assertAttributeEquals($Expires->format(Expires::FORMAT), '_Value', $this->Header, 'Expires::__construct() failed to set $_Value');
        }

        # NULL Date
        $this->Header = new Expires;
        $Expected     = new DateTime;

        $this->assertAttributeContains($Expected->format(substr(LastModified::FORMAT, 0, -5)), '_Value', $this->Header, 'IfModifiedSince::__construct() failed to set $_Value');

        # Invalid arguments
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $Expected = sprintf("Expires: %s\r\n", $this->Expires->format(Expires::FORMAT));
        $this->assertEquals($Expected, @strval($this->Header), 'Expires::__toSting() returned an invalid format');
    }
}
