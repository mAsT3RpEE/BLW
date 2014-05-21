<?php
/**
 * AcceptLanguageTest.php | Apr 8, 2014
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
 * Tests BLW Library MIME Contetn-Type header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\AcceptLanguage
 */
class AcceptLanguageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\MIME\AcceptLanguage
     */
    protected $Header = NULL;

    /**
     * @var \ReflectionProperty[]
     */
    protected $Properties = array();

    protected function setUp()
    {
        $this->Header      = new AcceptLanguage('da, en-gb;q=0.8, en;q=0.7');
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
             array('en-gb', 'en-gb')
            ,array(';;en-gb, ;;; da;;', 'en-gb')
            ,array('"en-gb"', 'en-gb')
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
     * @covers ::parseLanguage
     */
    public function test_parseLanguage()
    {
        # Valid type
        foreach ($this->generateValidTypes() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseLanguage($Original), 'AcceptLanguage::parseLanguage() returned an invalid format');
        }

        # Invalid type
        foreach ($this->generateInvalidTypes() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseLanguage($Original), 'AcceptLanguage::parseLanguage() returned an invalid format');
        }
    }

    /**
     * @depends test_parseLanguage
     * @covers ::__construct
     * @covers ::_combine
     */
    public function test_construct()
    {
        $this->Header = new AcceptLanguage('da, en-gb;q=0.8, en;q=0.7');

        # Check params
        $this->assertEquals('Accept-Language', $this->Properties['Type']->getValue($this->Header), 'AcceptLanguage::__construct() failed to set $_Type');
        $this->assertEquals('da, en-gb;q=0.8, en;q=0.7', $this->Properties['Value']->getValue($this->Header), 'AcceptLanguage::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            new AcceptLanguage(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        } catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("Accept-Language: da, en-gb;q=0.8, en;q=0.7\r\n", @strval($this->Header), 'AcceptLanguage::__toSting() returned an invalid format');
    }
}
