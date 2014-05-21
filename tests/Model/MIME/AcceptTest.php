<?php
/**
 * AcceptTest.php | Apr 8, 2014
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
 * @coversDefaultClass \BLW\Model\Mime\Accept
 */
class AcceptTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\MIME\Accept
     */
    protected $Header = NULL;

    /**
     * @var \ReflectionProperty[]
     */
    protected $Properties = array();

    protected function setUp()
    {
        $this->Header      = new Accept(', image/*; q=0.5, , */*; q=1');
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
             array('image/*', 'image/*')
            ,array(';;image/*, ;;; text/*;;', 'image/*')
            ,array('"image/*"', 'image/*')
            ,array('x-test/x-test', 'x-test/x-test')
        );
    }

    public function generateInvalidTypes()
    {
        return array(
             array('foo', '*/*')
            ,array('image / gif', '*/*')
            ,array(false, '*/*')
            ,array('test/plain', '*/*')
            ,array(array(), '*/*')
        );
    }

    /**
     * @covers ::parseType
     */
    public function test_parseType()
    {
        # Valid type
        foreach ($this->generateValidTypes() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseType($Original), 'Accept::parseType() returned an invalid format');
        }

        # Invalid type
        foreach ($this->generateInvalidTypes() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseType($Original), 'Accept::parseType() returned an invalid format');
        }
    }

    /**
     * @depends test_parseType
     * @covers ::__construct
     * @covers ::_combine
     */
    public function test_construct()
    {
        $this->Header = new Accept('*/*; q=1', array('foo'=>1));

        # Check params
        $this->assertEquals('Accept', $this->Properties['Type']->getValue($this->Header), 'Accept::__construct() failed to set $_Type');
        $this->assertEquals('*/*; q=1; foo=1', $this->Properties['Value']->getValue($this->Header), 'Accept::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            new Accept(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        } catch (InvalidArgumentException $e) {}

        # No parameters
        $this->Header = new Accept('text/plain,text/html,image/*; q=0.8');
        $this->assertEquals('text/plain, text/html, image/*; q=0.8', $this->Properties['Value']->getValue($this->Header), 'Accept::__construct() failed to set $_Value');

        # Invalid property
        try {
            new Accept('*/*', array('???' => '???'));
            $this->fail('Failed to generate notice with invalid arguments');
        } catch (\PHPUnit_Framework_Error_Notice $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("Accept: */*, image/*; q=0.5, */*, */*; q=1\r\n", @strval($this->Header), 'Accept::__toSting() returned an invalid format');
    }
}
