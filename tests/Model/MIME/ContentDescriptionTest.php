<?php
/**
 * ContentDescriptionTest.php | Mar 10, 2014
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
 * Tests BLW Library MIME Contetn-Description header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\ContentDescription
 */
class ContentDescriptionTest extends \PHPUnit_Framework_TestCase
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
        $this->Header      = new ContentDescription('Test description');
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

    public function generateValidDescriptions()
    {
        return array(
             array('test', 'test')
            ,array('test with space', 'test with space')
            ,array('`~!@#$%^&*()-_=+[{]}\\|;:\',<.>/?', '`~!@#$%^&*()-_=+[{]}\\|;:\',<.>/?')
            ,array('"""""still okay"""""""', 'still okay')
        );
    }

    public function generateInvalidDescriptions()
    {
        return array(
             array('', '')
            ,array('"""', '')
            ,array(false, '')
        );
    }

    /**
     * @covers ::parseDescription
     */
    public function test_parseDescription()
    {
        # Valid Description
        foreach ($this->generateValidDescriptions() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseDescription($Original), 'ContentDescription::parseDescription() returned an invalid format');
        }

        # Invalid Description
        foreach ($this->generateInvalidDescriptions() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseDescription($Original), 'ContentDescription::parseDescription() returned an invalid format');
        }
    }

    /**
     * @depends test_parseDescription
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Check params
        $this->assertEquals('Content-Description', $this->Properties['Type']->getValue($this->Header), 'ContentDescription::__construct() failed to set $_Type');
        $this->assertEquals('Test description', $this->Properties['Value']->getValue($this->Header), 'ContentDescription::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            new ContentDescription(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        } catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("Content-Description: Test description\r\n", @strval($this->Header), 'ContentDescription::__toSting() returned an invalid format');
    }
}
