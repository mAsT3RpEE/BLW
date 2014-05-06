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
namespace BLW\Tests\Model\MIME;

use DateTime;
use BLW\Model\InvalidArgumentException;
use BLW\Model\MIME\IfModifiedSince;


/**
 * Tests BLW Library MIME IfModifiedSince header.
 * @package BLW\MIME
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
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

    /**
     * @var \ReflectionProperty[]
     */
    protected $Properties = array();

    protected function setUp()
    {
        $this->IfModifiedSince = new DateTime;
        $this->Header          = new IfModifiedSince($this->IfModifiedSince);
        $this->Properties      = array(
             'Type'  => new \ReflectionProperty($this->Header, '_Type')
        	,'Value' => new \ReflectionProperty($this->Header, '_Value')
        );

        $this->Properties['Type']->setAccessible(true);
        $this->Properties['Value']->setAccessible(true);
    }

    protected function tearDown()
    {
        $this->Properties      = NULL;
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
        $this->assertEquals('If-Modified-Since', $this->Properties['Type']->getValue($this->Header), 'IfModifiedSince::__construct() failed to set $_Type');
        $this->assertSame($this->IfModifiedSince->format(IfModifiedSince::FORMAT), $this->Properties['Value']->getValue($this->Header), 'IfModifiedSince::__construct() failed to set $_Value');

        # Valid arguments
        foreach($this->generateValidDates() as $Arguments) {
            list($IfModifiedSince) = $Arguments;

            $this->Header = new IfModifiedSince($IfModifiedSince);

            $this->assertEquals($IfModifiedSince->format(IfModifiedSince::FORMAT), $this->Properties['Value']->getValue($this->Header), 'IfModifiedSince::__construct() failed to set $_Value');
        }

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