<?php
/**
 * Section.php | Mar 10, 2014
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

use ReflectionMethod;
use ReflectionProperty;
use BLW\Model\InvalidArgumentException;
use BLW\Model\MIME\Section;


/**
 * Tests BLW Library MIME Section header.
 * @package BLW\MIME
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\Section
 */
class SectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\MIME\Section
     */
    protected $Section = NULL;

    protected function setUp()
    {
        $this->Section   = new Section('multipart/mixed', '0-00000:=00000');
        $this->Section[] = $this->Section->createBoundary('0-00000:=00000');
        $this->Section[] = "Test Part\r\n";
    }

    protected function tearDown()
    {
        $this->Section = NULL;
    }

    /**
     * @covers ::buildBoundary
     */
    public function test_buildBoundary()
    {
        $this->assertNotEmpty($this->Section->buildBoundary(), 'Section::buildBoundary should not be empty');
    }

    public function generateValidArguments()
    {
        return array(
             array('multipart/mixed',       '1-000:=000')
            ,array('multipart/altentative', '2-000:=000')
            ,array('multipart/relative',    '3-000:=000')
        );
    }

    public function generateInvalidArguments()
    {
        return array(
             array(array(),            '1-000:=000')
            ,array(new \stdClass,      '2-000:=000')
            ,array('multipart/mixed',  array())
            ,array('multipart/mixed',  new \stdClass)
        );
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Check params
        $Type     = new ReflectionProperty($this->Section, '_Type');
        $Boundary = new ReflectionProperty($this->Section, '_Boundary');

        $Type->setAccessible(true);
        $Boundary->setAccessible(true);

        $this->assertSame('multipart/mixed', $Type->getValue($this->Section), 'Section::__construct() failed to set $_Type');
        $this->assertSame('0-00000:=00000', $Boundary->getValue($this->Section), 'Section::__construct() failed to set $_Section');

        # Valid arguments
        foreach($this->generateValidArguments() as $Arguments) {
            list ($InputType, $InputBoundary) = $Arguments;

            $this->Section = new Section($InputType, $InputBoundary);

            $this->assertSame($InputType, $Type->getValue($this->Section), sprintf('Section::__construct(%s, %s) failed to set $_Type', $InputType, $InputBoundary));
            $this->assertSame($InputBoundary, $Boundary->getValue($this->Section), sprintf('Section::__construct(%s, %s) failed to set $_Section', $InputType, $InputBoundary));
        }

        # Invalid arguments
        foreach($this->generateInvalidArguments() as $Arguments) {
            list ($InputType, $InputBoundary) = $Arguments;

            // No warning prior to 5.4
            if (version_compare(PHP_VERSION, '5.4.0', '<') && ($InputType === array() || $InputBoundary === array()))
                continue;

            try {
                new Section($InputType, $InputBoundary);
                $this->fail(sprintf('Failed to generate exception with invalid arguments: %s', print_r($Arguments, true)));
            }

            catch (InvalidArgumentException $e) {}

            catch (\PHPUnit_Framework_Error $e) {}
        }
    }

    /**
     * @depends test_construct
     * @covers ::getBoundary
     */
    public function test_getBoundary()
    {
        $this->assertSame('0-00000:=00000', $this->Section->getBoundary(), 'Section::getBoundary() returned an invalid value');
    }

    /**
     * @covers ::getFactoryMethods
     */
    public function test_getFactoryMethods()
    {
        $Expected = array(
             new ReflectionMethod($this->Section, 'createStart')
            ,new ReflectionMethod($this->Section, 'createBoundary')
            ,new ReflectionMethod($this->Section, 'createEnd')
        );

        $this->assertEquals($Expected, $this->Section->getFactoryMethods(), 'Section::getFactoryMethods() returned an invalid value');
    }

    /**
     * @depends test_construct
     * @covers ::createStart
     */
    public function test_createStart()
    {
        $Expected = "Content-Type: multipart/mixed; boundary=\"0-00000:=00000\"\r\n";

        $this->assertInstanceOf('\\BLW\\Model\\MIME\\ContentType', $this->Section->createStart(), 'Section::createStart() returned an invalid value');
        $this->assertEquals($Expected, strval($this->Section->createStart()), 'Section::createStart() returned an invalid value');
    }

    /**
     * @depends test_construct
     * @covers ::createEnd
     */
    public function test_createEnd()
    {
        $Expected = "--0-00000:=00000--\r\n";

        $this->assertInstanceOf('\\BLW\\Model\\MIME\\Boundary', $this->Section->createEnd(), 'Section::createStart() returned an invalid value');
        $this->assertEquals($Expected, strval($this->Section->createEnd()), 'Section::createStart() returned an invalid value');
    }

    /**
     * @depends test_construct
     * @covers ::createBoundary
     */
    public function test_createBoundary()
    {
        $Expected = "--0-00000:=00000\r\n";

        $this->assertInstanceOf('\\BLW\\Model\\MIME\\Boundary', $this->Section->createBoundary(), 'Section::createStart() returned an invalid value');
        $this->assertEquals($Expected, strval($this->Section->createBoundary()), 'Section::createStart() returned an invalid value');
    }

    /**
     * @depends test_createStart
     * @depends test_createEnd
     * @depends test_createBoundary
     * @covers ::__toString
     */
    public function test_toString()
    {
        $Expected = <<<EOT
Content-Type: multipart/mixed; boundary="0-00000:=00000"\r\n\r\n--0-00000:=00000\r\nTest Part\r\n\r\n--0-00000:=00000--\r\n
EOT;

        $this->assertEquals($Expected, @strval($this->Section), 'Section::__toSting() returned an invalid format');
    }
}
