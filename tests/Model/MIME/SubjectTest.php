<?php
/**
 * SubjectTest.php | Mar 10, 2014
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
 * Tests BLW Library MIME Contetn-Subject header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\Subject
 */
class SubjectTest extends \PHPUnit_Framework_TestCase
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
        $this->Header      = new Subject('Test subject');
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

    public function generateValidSubjects()
    {
        return array(
             array('test', 'test')
            ,array('test with space', 'test with space')
            ,array('`~!@#$%^&*()-_=+[{]}\\|;:\',<.>/?', '`~!@#$%^&*()-_=+[{]}\\|;:\',<.>/?')
            ,array('"""""still okay"""""""', 'still okay')
        );
    }

    public function generateInvalidSubjects()
    {
        return array(
             array('', '')
            ,array('"""', '')
            ,array(false, '')
        );
    }

    /**
     * @covers ::parseSubject
     */
    public function test_parseSubject()
    {
        # Valid Subject
        foreach ($this->generateValidSubjects() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseSubject($Original), 'Subject::parseSubject() returned an invalid format');
        }

        # Invalid Subject
        foreach ($this->generateInvalidSubjects() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseSubject($Original), 'Subject::parseSubject() returned an invalid format');
        }
    }

    /**
     * @depends test_parseSubject
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Check params
        $this->assertEquals('Subject', $this->Properties['Type']->getValue($this->Header), 'Subject::__construct() failed to set $_Type');
        $this->assertEquals('Test subject', $this->Properties['Value']->getValue($this->Header), 'Subject::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            new Subject(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        } catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("Subject: Test subject\r\n", @strval($this->Header), 'Subject::__toSting() returned an invalid format');
    }
}
