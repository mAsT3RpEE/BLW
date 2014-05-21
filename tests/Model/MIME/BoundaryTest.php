<?php
/**
 * BoundaryTest.php | Mar 20, 2014
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
 * Tests BLW Library MIME Boundary header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\Boundary
 */
class BoundaryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\MIME\Boundary
     */
    protected $Header = NULL;

    /**
     * @var \ReflectionProperty[]
     */
    protected $Properties = array();

    protected function setUp()
    {
        $this->Header      = new Boundary('0-00000:=000000', true);
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

    public function generateValidIDs()
    {
        return array(
             array('abcdefgh@example.com', 'abcdefgh@example.com')
            ,array('12345678@example.com', '12345678@example.com')
            ,array('"abcd.1234"@example.com', '"abcd.1234"@example.com')
            ,array(';;;ab_cd.12-34@exam-pl_e.co.uk;;;', 'ab_cd.12-34@exam-pl_e.co.uk')
        );
    }

    public function generateInvalidIDs()
    {
        return array(
             array('', self::CONTENT_ID)
            ,array('root', self::CONTENT_ID)
            ,array('1234567', self::CONTENT_ID)
            ,array(false, self::CONTENT_ID)
            ,array(NULL, self::CONTENT_ID)
        );
    }
    public function generateValidBoundarys()
    {
        return array(
             array('test', 'test')
            ,array('test with space', 'test')
            ,array('"""""st-ill_okay:="""""""', 'st-ill_okay:=')
        );
    }

    public function generateInvalidBoundarys()
    {
        return array(
             array('', '')
            ,array('"""', '')
            ,array(false, '')
        );
    }

    /**
     * @covers ::parseBoundary
     */
    public function test_parseBoundary()
    {
        # Valid Boundary
        foreach ($this->generateValidBoundarys() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseBoundary($Original), 'Boundary::parseBoundary() returned an invalid format');
        }

        # Invalid Boundary
        foreach ($this->generateInvalidBoundarys() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseBoundary($Original), 'Boundary::parseBoundary() returned an invalid format');
        }
    }

    /**
     * @depends test_parseBoundary
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Check params
        $this->assertEquals(Boundary::START, $this->Properties['Type']->getValue($this->Header), 'Boundary::__construct() failed to set $_Type');
        $this->assertEquals('0-00000:=000000'.Boundary::END, $this->Properties['Value']->getValue($this->Header), 'Boundary::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            new Boundary(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        } catch (InvalidArgumentException $e) {}

        try {
            new Boundary('a');
            $this->fail('Failed to generate exception with invalid parameters');
        } catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("--0-00000:=000000--\r\n", @strval($this->Header), 'Boundary::__toSting() returned an invalid format');
    }
}
