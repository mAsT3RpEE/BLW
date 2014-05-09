<?php
/**
 * ViaTest.php | Apr 8, 2014
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
use BLW\Model\MIME\Via;


/**
 * Tests BLW Library MIME Via header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\Via
 */
class ViaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\MIME\Via
     */
    protected $Header = NULL;

    /**
     * @var \ReflectionProperty[]
     */
    protected $Properties = array();

    protected function setUp()
    {
        $this->Header      = new Via('1.0 fred, 1.1 nowhere.com (Apache/1.1)');
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

    public function generateValidGateways()
    {
        return array(
        	 array('1.0 fred', '1.0 fred')
        	,array('1.1 example.com, ;;; unicode-1-1;;', '1.1 example.com')
        	,array('"1.0 nowhere.com (Apache/1.1)"', '1.0 nowhere.com (Apache/1.1)')
        );
    }

    public function generateInvalidGateways()
    {
        return array(
             array(false, '')
        	,array(new \stdClass, '')
            ,array(array(), '')
        );
    }

    /**
     * @covers ::parseVia
     */
    public function test_parseVia()
    {
        # Valid type
        foreach($this->generateValidGateways() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseVia($Original), 'Via::parseVia() returned an invalid format');
        }

        # Invalid type
        foreach($this->generateInvalidGateways() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseVia($Original), 'Via::parseVia() returned an invalid format');
        }
    }

    /**
     * @depends test_parseVia
     * @covers ::__construct
     * @covers ::_combine
     */
    public function test_construct()
    {
        $this->Header = new Via('1.0 fred,1.1 nowhere.com (Apache/1.1)');

        # Check params
        $this->assertEquals('Via', $this->Properties['Type']->getValue($this->Header), 'Via::__construct() failed to set $_Type');
        $this->assertEquals('1.0 fred, 1.1 nowhere.com (Apache/1.1)', $this->Properties['Value']->getValue($this->Header), 'Via::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            new Via(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("Via: 1.0 fred, 1.1 nowhere.com (Apache/1.1)\r\n", @strval($this->Header), 'Via::__toSting() returned an invalid format');
    }
}