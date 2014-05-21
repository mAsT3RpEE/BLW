<?php
/**
 * Date.php | Mar 10, 2014
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
use BLW\Model\MIME\Date;


/**
 * Tests BLW Library MIME Date header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\Date
 */
class DateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \DateTime
     */
    protected $Date = NULL;
    /**
     * @var \BLW\Model\MIME\ContentType
     */
    protected $Header = NULL;

    protected function setUp()
    {
        $this->Date       = new DateTime;
        $this->Header     = new Date($this->Date);
    }

    protected function tearDown()
    {
        $this->Header     = NULL;
        $this->Date       = NULL;
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
        $this->assertAttributeEquals('Date', '_Type', $this->Header, 'Date::__construct() failed to set $_Type');
        $this->assertAttributeSame($this->Date->format(Date::FORMAT), '_Value', $this->Header, 'Date::__construct() failed to set $_Value');

        # Valid arguments
        foreach($this->generateValidDates() as $Arguments) {
            list($Date) = $Arguments;

            $this->Header = new Date($Date);

            $this->assertAttributeEquals($Date->format(Date::FORMAT), '_Value', $this->Header, 'Date::__construct() failed to set $_Value');
        }

        # NULL Date
        $this->Header = new Date;
        $Expected     = new DateTime;

        $this->assertAttributeContains($Expected->format(substr(LastModified::FORMAT, 0, -5)), '_Value', $this->Header, 'IfModifiedSince::__construct() failed to set $_Value');

        # Invalid arguments
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $Expected = sprintf("Date: %s\r\n", $this->Date->format(Date::FORMAT));
        $this->assertEquals($Expected, @strval($this->Header), 'Date::__toSting() returned an invalid format');
    }
}
