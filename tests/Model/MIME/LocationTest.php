<?php
/**
 * LocationTest.php | Mar 10, 2014
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

use BLW\Model\InvalidArgumentException;


/**
 * Tests BLW Library MIME Contetn-Base header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\Location
 */
class LocationTest extends \PHPUnit_Framework_TestCase
{
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
        $this->Header      = new Location($this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('ftp://example.com')));
        $this->Properties  = array(
             'Type'  => new \ReflectionProperty($this->Header, '_Type')
            ,'Value' => new \ReflectionProperty($this->Header, '_Value')
        );

        $this->Properties['Type']->setAccessible(true);
        $this->Properties['Value']->setAccessible(true);
    }

    protected function tearDown()
    {
        $this->Properties = NULL;
        $this->Header     = NULL;
    }

    public function generateValidBases()
    {
        return array(
             array($this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('http://foo.com')), 'http://foo.com')
            ,array($this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('http://www.example.com/test/')), 'http://www.example.com/test/')
        );
    }

    public function generateInvalidBases()
    {
        return array(
             array($this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('')), '')
            ,array($this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('"""')), '')
            ,array($this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('folder/test.png')), '')
        );
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Check params
        $this->assertEquals('Location', $this->Properties['Type']->getValue($this->Header), 'Location::__construct() failed to set $_Type');
        $this->assertEquals('ftp://example.com', $this->Properties['Value']->getValue($this->Header), 'Location::__construct() failed to set $_Value');

        # Valid Base
        foreach ($this->generateValidBases() as $Parameters) {
            list($Input, $Expected) = $Parameters;

            $this->Header = new Location($Input);

            $this->assertEquals($Expected, $this->Properties['Value']->getValue($this->Header), sprintf('Location::__contruct(%s) failed to set $_Value', $Input));
        }

        # Invalid Base
        foreach ($this->generateInvalidBases() as $Parameters) {
            list($Input, $Expected) = $Parameters;

            try {
                new Location($Input);
                $this->fail('Failed to generate exception with invalid parameters');
            } catch (InvalidArgumentException $e) {}
        }
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("Location: ftp://example.com\r\n", @strval($this->Header), 'Location::__toSting() returned an invalid format');
    }
}
