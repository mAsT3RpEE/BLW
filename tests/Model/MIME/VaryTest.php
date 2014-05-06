<?php
/**
 * VaryTest.php | Apr 8, 2014
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

use BLW\Model\InvalidArgumentException;
use BLW\Model\MIME\Vary;


/**
 * Tests BLW Library MIME Contetn-Type header.
 * @package BLW\MIME
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\Vary
 */
class VaryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\MIME\Vary
     */
    protected $Header = NULL;

    /**
     * @var \ReflectionProperty[]
     */
    protected $Properties = array();

    protected function setUp()
    {
        $this->Header      = new Vary('Content-Type');
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

    public function generateValidFields()
    {
        return array(
        	 array('token', 'token')
        	,array(';;token, ;;; foo;;', 'token')
        	,array('"token"', 'token')
        );
    }

    public function generateInvalidFields()
    {
        return array(
        	 array(false, '*')
        	,array(new \stdClass, '*')
            ,array(array(), '*')
        );
    }

    /**
     * @covers ::parseFieldName
     */
    public function test_parseFieldName()
    {
        # Valid type
        foreach($this->generateValidFields() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseFieldName($Original), 'Vary::parseFieldName() returned an invalid format');
        }

        # Invalid type
        foreach($this->generateInvalidFields() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseFieldName($Original), 'Vary::parseFieldName() returned an invalid format');
        }
    }

    /**
     * @depends test_parseFieldName
     * @covers ::__construct
     */
    public function test_construct()
    {
        $this->Header = new Vary('Content-Type');

        # Check params
        $this->assertEquals('Vary', $this->Properties['Type']->getValue($this->Header), 'Vary::__construct() failed to set $_Type');
        $this->assertEquals('Content-Type', $this->Properties['Value']->getValue($this->Header), 'Vary::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            new Vary(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("Vary: Content-Type\r\n", @strval($this->Header), 'Vary::__toSting() returned an invalid format');
    }
}