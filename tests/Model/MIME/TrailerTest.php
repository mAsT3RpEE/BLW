<?php
/**
 * TrailerTest.php | Apr 8, 2014
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
use BLW\Model\MIME\Trailer;


/**
 * Tests BLW Library MIME Contetn-Type header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\Trailer
 */
class TrailerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\MIME\Trailer
     */
    protected $Header = NULL;

    /**
     * @var \ReflectionProperty[]
     */
    protected $Properties = array();

    protected function setUp()
    {
        $this->Header      = new Trailer('Content-Type');
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
        	 array(false, '')
        	,array(new \stdClass, '')
            ,array(array(), '')
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

            $this->assertEquals($Expected, $this->Header->parseFieldName($Original), 'Trailer::parseFieldName() returned an invalid format');
        }

        # Invalid type
        foreach($this->generateInvalidFields() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseFieldName($Original), 'Trailer::parseFieldName() returned an invalid format');
        }
    }

    /**
     * @depends test_parseFieldName
     * @covers ::__construct
     * @covers ::_combine
     */
    public function test_construct()
    {
        $this->Header = new Trailer('Content-Type, Content-Encoding');

        # Check params
        $this->assertEquals('Trailer', $this->Properties['Type']->getValue($this->Header), 'Trailer::__construct() failed to set $_Type');
        $this->assertEquals('Content-Type, Content-Encoding', $this->Properties['Value']->getValue($this->Header), 'Trailer::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            new Trailer(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("Trailer: Content-Type\r\n", @strval($this->Header), 'Trailer::__toSting() returned an invalid format');
    }
}