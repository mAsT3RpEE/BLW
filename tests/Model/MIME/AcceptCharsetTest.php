<?php
/**
 * AcceptCharsetTest.php | Apr 8, 2014
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
use BLW\Model\MIME\AcceptCharset;


/**
 * Tests BLW Library MIME Accept-Charset header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\AcceptCharset
 */
class AcceptCharsetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\MIME\AcceptCharset
     */
    protected $Header = NULL;

    /**
     * @var \ReflectionProperty[]
     */
    protected $Properties = array();

    protected function setUp()
    {
        $this->Header      = new AcceptCharset('unicode-1-1; q=1');
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

    public function generateValidTypes()
    {
        return array(
        	 array('iso-8859-5', 'iso-8859-5')
        	,array(';;iso-8859-5, ;;; unicode-1-1;;', 'iso-8859-5')
        	,array('"iso-8859-5"', 'iso-8859-5')
        );
    }

    public function generateInvalidTypes()
    {
        return array(
        	 array(false, '*')
        	,array(new \stdClass, '*')
            ,array(array(), '*')
        );
    }

    /**
     * @covers ::parseCharset
     */
    public function test_parseCharset()
    {
        # Valid type
        foreach($this->generateValidTypes() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseCharset($Original), 'AcceptCharset::parseCharset() returned an invalid format');
        }

        # Invalid type
        foreach($this->generateInvalidTypes() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseCharset($Original), 'AcceptCharset::parseCharset() returned an invalid format');
        }
    }

    /**
     * @depends test_parseCharset
     * @covers ::__construct
     * @covers ::_combine
     */
    public function test_construct()
    {
        $this->Header = new AcceptCharset('unicode-1-1; q=1, utf-8; q=0.7');

        # Check params
        $this->assertEquals('Accept-Charset', $this->Properties['Type']->getValue($this->Header), 'AcceptCharset::__construct() failed to set $_Type');
        $this->assertEquals('unicode-1-1; q=1, utf-8; q=0.7', $this->Properties['Value']->getValue($this->Header), 'AcceptCharset::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            new AcceptCharset(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("Accept-Charset: unicode-1-1; q=1\r\n", @strval($this->Header), 'AcceptCharset::__toSting() returned an invalid format');
    }
}
