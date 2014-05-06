<?php
/**
 * FileTest.php | Feb 28, 2014
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
use BLW\Model\Stream\File as Stream;

/**
 * Tests BLW Library Adaptor type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Stream\File
 */
class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $Path = NULL;

    /**
     * @var \BLW\Type\IFile
     */
    protected $File = NULL;

    /**
     * @var \BLW\Type\IStream
     */
    protected $Stream = NULL;

    protected function setUp()
    {
        $this->Path   = tempnam(sys_get_temp_dir(), 'IFile');
        $this->File   = $this->getMockForAbstractClass('\\BLW\\Type\\AFile', array($this->Path));
        $this->Stream = new Stream($this->File, IFile::READ | IFile::WRITE | IFile::TRUNCATE);
    }

    protected function tearDown()
    {
        $this->File   = NULL;
        $this->Stream = NULL;

        unset($this->Path);
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        $this->assertTrue(file_exists($this->Path), sprintf('File `%s` should exist', $this->Path));
        $this->assertEmpty(file_get_contents($this->Path), sprintf('File `%s` should be empty', $this->Path));
    }

    /**
     * @covers ::getContents
     */
    public function test_getContents()
    {
        $this->assertEquals(file_get_contents($this->Path), $this->Stream->getContents(), 'IStream::getContents() should return contents of file');
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
        $this->assertEquals($String, file_get_contents($this->Path), 'IStream::getContents() should equal file_get_contents()');
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

        # File
        $this->assertInstanceOf('\\BLW\\Type\\IFile', $this->Stream->File, 'IStream::$File should be an instance of IFile');

        # Context
        $this->assertTrue(is_resource($this->Stream->Context) || empty($tihs->Stream->Context), 'IStream::$Context should be an resource / empty');

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

        # File
        $this->assertTrue(isset($this->Stream->File), 'IStream::$File should exist');

        # Context
        $this->assertTrue(isset($this->Stream->Context), 'IStream::$Context should exist');

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

	    # File
        try {
            $this->Stream->File = 0;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

	    # Context
        try {
            $this->Stream->Context = 0;
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