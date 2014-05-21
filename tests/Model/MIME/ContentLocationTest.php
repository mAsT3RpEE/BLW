<?php
/**
 * ContentLocationTest.php | Mar 10, 2014
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
 * Tests BLW Library MIME Contetn-Location header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\ContentLocation
 */
class ContentLocationTest extends \PHPUnit_Framework_TestCase
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
        $this->Header      = new ContentLocation($this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('/test.png')));
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

    public function generateValidLocations()
    {
        return array(
             array($this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('test.png')), 'test.png')
            ,array($this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('http://www.example.com/test.png')), 'http://www.example.com/test.png')
            ,array($this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('folder/test.png')), 'folder/test.png')
        );
    }

    public function generateInvalidLocations()
    {
        return array(
             array($this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('')), '')
            ,array($this->getMockForAbstractClass('\\BLW\\Type\\AURI', array('"""')), '')
        );
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Check params
        $this->assertEquals('Content-Location', $this->Properties['Type']->getValue($this->Header), 'ContentLocation::__construct() failed to set $_Type');
        $this->assertEquals('/test.png', $this->Properties['Value']->getValue($this->Header), 'ContentLocation::__construct() failed to set $_Value');

        # Valid Location
        foreach ($this->generateValidLocations() as $Parameters) {
            list($Input, $Expected) = $Parameters;

            $this->Header = new ContentLocation($Input);

            $this->assertEquals($Expected, $this->Properties['Value']->getValue($this->Header), sprintf('ContentLocation::__contruct(%s) failed to set $_Value', $Input));
        }

        # Invalid Location
        foreach ($this->generateInvalidLocations() as $Parameters) {
            list($Input, $Expected) = $Parameters;

            try {
                new ContentLocation($Input);
                $this->fail('Failed to generate exception with invalid parameters');
            } catch (InvalidArgumentException $e) {}
        }
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("Content-Location: /test.png\r\n", @strval($this->Header), 'ContentLocation::__toSting() returned an invalid format');
    }
}
