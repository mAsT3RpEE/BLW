<?php
/**
 * LastModified.php | Apr 8, 2014
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
use BLW\Model\MIME\LastModified;


/**
 * Tests BLW Library MIME LastModified header.
 * @package BLW\MIME
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\LastModified
 */
class LastModifiedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \DateTime
     */
    protected $LastModified = NULL;
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
        $this->LastModified    = new DateTime;
        $this->Header          = new LastModified($this->LastModified);
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
        $this->LastModified = NULL;
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
        $this->assertEquals('Last-Modified', $this->Properties['Type']->getValue($this->Header), 'LastModified::__construct() failed to set $_Type');
        $this->assertSame($this->LastModified->format(LastModified::FORMAT), $this->Properties['Value']->getValue($this->Header), 'LastModified::__construct() failed to set $_Value');

        # Valid arguments
        foreach($this->generateValidDates() as $Arguments) {
            list($LastModified) = $Arguments;

            $this->Header = new LastModified($LastModified);

            $this->assertEquals($LastModified->format(LastModified::FORMAT), $this->Properties['Value']->getValue($this->Header), 'LastModified::__construct() failed to set $_Value');
        }

        # Invalid arguments
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $Expected = sprintf("Last-Modified: %s\r\n", $this->LastModified->format(LastModified::FORMAT));
        $this->assertEquals($Expected, @strval($this->Header), 'LastModified::__toSting() returned an invalid format');
    }
}