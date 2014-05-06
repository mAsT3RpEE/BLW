<?php
/**
 * StringTest.php | Feb 28, 2014
 *
 * @filesource
 * @license MIT
 * @copyright Copyright (c) 2013-2018, mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * @package BLW\Core
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Tests\Model\Stream;

use ReflectionProperty;
use PHPUnit_Framework_Error_Notice;

use BLW\Type\IFile;
use BLW\Type\IStream;
use BLW\Model\Stream\String as Stream;
use BLW\Model\InvalidArgumentException;

/**
 * Tests BLW Library Adaptor type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Stream\String
 */
class StringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $String = '';

    /**
     * @var \BLW\Type\IStream
     */
    protected $Stream = NULL;

    protected function setUp()
    {
        $this->Stream = new Stream($this->String, 'text/plain', IFile::READ | IFile::WRITE | IFile::TRUNCATE);
    }

    protected function tearDown()
    {
        $this->String = '';
        $this->Stream = NULL;
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Invalid String
        try {
            $Input   = 1;
            $Invalid = new Stream($Input);
            $this->fail('Failded to generate exception with invalid parameters');
        }

    	catch (InvalidArgumentException $e) {}

        # Invalid Mode
        try {
            $Invalid = new Stream($this->String, 'text/plain', IFile::WRITE | IFile::APPEND);
            $this->fail('Failded to generate exception with invalid parameters');
        }

    	catch (InvalidArgumentException $e) {}

        # Invalid Mode
        try {
            $Invalid = new Stream($this->String, 'text/plain', IFile::READ | IFile::WRITE | IFile::APPEND);
            $this->fail('Failded to generate exception with invalid parameters');
        }

    	catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::getContents
     */
    public function test_getContents()
    {
        $this->assertEquals($this->String, $this->Stream->getContents(), sprintf('IStream::getContents() should equal `%s`', $this->String));
    }

    public function generateContents()
    {
        $TestString = 'trololololo string contents';
        $TestFile   = sys_get_temp_dir() . '/blw_test_file.tmp';

        $Stream     = $this->getMockForAbstractClass('\\BLW\\Type\\AStream');
        $fp         = new \ReflectionProperty($Stream, '_fp');

        $fp->setAccessible(true);
        $fp->setValue($Stream, fopen("data://text/plain,$TestString", 'r'));
        $fp->setAccessible(false);


        return array(
        	 array($TestString, $TestString)
            ,array($TestString, fopen("data://text/plain,$TestString", 'r'))
            ,array($TestString, $Stream)
        );
    }

    /**
     * @dataProvider generateContents
     * @covers ::putContents
     */
    public function test_putContents($String, $Data)
    {
        $this->assertEquals(strlen($String), $this->Stream->putContents($Data), 'IStream::putContents() returned an invalid byte count');
        $this->assertEquals($String, $this->Stream->getContents(), sprintf('IStream::getContents() should return `%s`', $String));
    }

    /**
     * @depends test_putContents
     * @dataProvider generateContents
     * @covers ::__destruct
     */
    public function test_destruct($String, $Data)
    {
        $this->assertEquals(strlen($String), $this->Stream->putContents($Data), 'IStream::putContents() returned an invalid byte count');

        # Destroy Object
        $this->Stream = NULL;

        $this->assertEquals($String, $this->String, sprintf('Original string should now equal `%s`', $String));
    }

    /**
     * @covers ::__get
     */
    public function test__get()
    {
	    # Make property readable / writable
	    $Status = new ReflectionProperty($this->Stream, '_Status');
	    $Status->setAccessible(true);

	    # Status
        $this->assertSame($this->Stream->Status, $Status->getValue($this->Stream), 'IObject::$Status should equal IObject::_Status');

	    # Serializer
	    $this->assertSame($this->Stream->Serializer, $this->Stream->getSerializer(), 'IObject::$Serializer should equal IObject::getSerializer()');

	    # Parent
        $this->assertNULL($this->Stream->Parent, 'IObject::$Parent should initially be NULL');

        # ID
        $this->assertSame($this->Stream->ID, $this->Stream->getID(), 'IObject::$ID should equal IObject::getID()');

        # fp
        $this->assertTrue(is_resource($this->Stream->fp), 'IStream::$fp should be a resource');

        # String
        $this->assertEquals($this->String, $this->Stream->String, sprintf('IStream::$String should equal `%s`', $this->String));

        # Test undefined property
        try {
            $this->Stream->bar;
            $this->fail('IObject::$bar is undefined and should raise a notice');
        }

        catch (PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Undefined property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }
   }

   /**
    * @covers ::__isset
    */
   public function test__isset()
   {
        # Status
       $this->assertTrue(isset($this->Stream->Serializer), 'IObject::$Status should exist');

	    # Serializer
	    $this->assertTrue(isset($this->Stream->Serializer), 'IObject::$Serializer should exist');

	    # Parent
        $this->assertFalse(isset($this->Stream->Parent), 'IObject::$Parent should not exist');

	    # ID
        $this->assertTrue(isset($this->Stream->ID), 'IObject::$ID should exist');

       # fp
        $this->assertTrue(isset($this->Stream->fp), 'IStream::$fp should exist');

        # String
        $this->assertTrue(isset($this->Stream->String), 'IStream::$String should exist');

        # Test undefined property
       $this->assertFalse(isset($this->Stream->bar), 'IObject::$bar shouldn\'t exist');
  }

    /**
     * @covers ::__set
     */
    public function test__set()
    {
        # Status
        try {
            $this->Stream->Status = 0;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        # Serializer
        try {
            $this->Stream->Serializer = 0;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        # Parent
        $this->Stream->Parent = $this->getMockForAbstractClass('\\BLW\\Type\\IObject');
        $this->assertSame($this->Stream->Parent, $this->Stream->getParent(), 'IObject::$Parent should equal IObject::getParent');
        $this->assertTrue(isset($this->Stream->Parent), 'IObject::$Parent should exist');

	    # ID
        try {
            $this->Stream->ID = 0;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

	    # fp
        try {
            $this->Stream->fp = 0;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

	    # String
        try {
            $this->Stream->String = 0;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

	    # Invalid property
        try {
            $this->Stream->bar = 0;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }
    }
}