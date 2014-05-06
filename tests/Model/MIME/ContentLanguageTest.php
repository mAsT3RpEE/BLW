<?php
/**
 * ContentLanguageTest.php | Apr 8, 2014
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
use BLW\Model\MIME\ContentLanguage;


/**
 * Tests BLW Library MIME Contetn-Type header.
 * @package BLW\MIME
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\ContentLanguage
 */
class ContentLanguageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\MIME\ContentLanguage
     */
    protected $Header = NULL;

    /**
     * @var \ReflectionProperty[]
     */
    protected $Properties = array();

    protected function setUp()
    {
        $this->Header      = new ContentLanguage('en, en-gb');
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

    public function generateValidLanguages()
    {
        return array(
        	 array('en-gb', 'en-gb')
        	,array('en-gb;q=0', 'en-gb')
            ,array(';;en-gb, ;;; da;;', 'en-gb')
        	,array('"en-gb"', 'en-gb')
        );
    }

    public function generateInvalidLanguages()
    {
        return array(
        	 array(false, 'en')
        	,array(new \stdClass, 'en')
            ,array(array(), 'en')
        );
    }

    /**
     * @covers ::parseLanguage
     */
    public function test_parseLanguage()
    {
        # Valid type
        foreach($this->generateValidLanguages() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseLanguage($Original), 'ContentLanguage::parseLanguage() returned an invalid format');
        }

        # Invalid type
        foreach($this->generateInvalidLanguages() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseLanguage($Original), 'ContentLanguage::parseLanguage() returned an invalid format');
        }
    }

    /**
     * @depends test_parseLanguage
     * @covers ::__construct
     */
    public function test_construct()
    {
        $this->Header = new ContentLanguage('en, en-gb');

        # Check params
        $this->assertEquals('Content-Language', $this->Properties['Type']->getValue($this->Header), 'ContentLanguage::__construct() failed to set $_Type');
        $this->assertEquals('en, en-gb', $this->Properties['Value']->getValue($this->Header), 'ContentLanguage::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            new ContentLanguage(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("Content-Language: en, en-gb\r\n", @strval($this->Header), 'ContentLanguage::__toSting() returned an invalid format');
    }
}