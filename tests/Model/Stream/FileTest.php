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
namespace BLW\Model\Stream;

use BLW\Type\IFile;
use BLW\Type\IStream;
use BLW\Model\Stream\File as Stream;

/**
 * Tests BLW Library Adaptor type.
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
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
     * @var \BLW\Model\Stream\File
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
     * @covers ::__destruct
     */
    public function test_destruct()
    {
        $this->Stream->__destruct();

        $this->assertFalse(is_resource($this->readAttribute($this->Stream, '_fp')), 'File::__destruct() Failed to close file pointer');
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
    public function test_get()
    {
        # Status
        $this->assertAttributeSame($this->Stream->Status, '_Status', $this->Stream, 'IObject::$Status should equal IObject::_Status');

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

        # Flags
        $this->assertTrue(is_int($this->Stream->Flags), 'IStream::$Flags should be an integer');

        # Options
        $this->assertTrue(is_array($this->Stream->Options), 'IStream::$Options should be an array');

        # Test undefined property
        try {
            $this->Stream->undefined;
            $this->fail('Failed to generate notice with undefined property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Undefined property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Stream->undefined;
    }

   /**
    * @covers ::__isset
    */
   public function test_isset()
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

        # Flags
        $this->assertTrue(isset($this->Stream->Flags), 'IStream::$Flags should exist');

        # Options
        $this->assertTrue(isset($this->Stream->Options), 'IStream::$Options should exist');

        # Test undefined property
       $this->assertFalse(isset($this->Stream->bar), 'IObject::$bar shouldn\'t exist');
  }

    /**
     * @covers ::__set
     */
    public function test_set()
    {
        # Status
        try {
            $this->Stream->Status = 0;
            $this->fail('Failed to generate notice on readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Stream->Status = 0;

        # Serializer
        try {
            $this->Stream->Serializer = 0;
            $this->fail('Failed to generate notice on readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Stream->Serializer = 0;

        # Parent
        $Parent                = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Stream->Parent = $Parent;

        $this->assertSame($Parent, $this->Stream->Parent, 'IStream::$Parent should equal IStream::getParent()');

        try {
            $this->Stream->Parent = null;
            $this->fail('Failed to generate notice with invalid value');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Stream->Parent = null;

        try {
            $this->Stream->Parent = $Parent;
            $this->fail('Failed to generate notice with oneshot value');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # ID
        try {
            $this->Stream->ID = 'foo';
            $this->fail('Failed to generate notice with invalid value');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Stream->ID = 'foo';

        # fp
        try {
            $this->Stream->fp = 0;
            $this->fail('Failed to generate notice on readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Stream->fp = 0;

        # File
        try {
            $this->Stream->File = 0;
            $this->fail('Failed to generate notice on readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Stream->File = 0;

        # Flags
        try {
            $this->Stream->Flags = 0;
            $this->fail('Failed to generate notice on readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Stream->Flags = 0;

        # Options
        try {
            $this->Stream->Options = 0;
            $this->fail('Failed to generate notice on readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Stream->Options = 0;

        # undefined property
        try {
            $this->Stream->undefined = 0;
            $this->fail('Failed to generate warning on readonly property');
        } catch (\PHPUnit_Framework_Error_Warning $e) {
            $this->assertContains('non-existant', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Stream->undefined = 0;
    }

    /**
     * @depends test_get
     * @depends test_set
     * @covers ::__unset
     */
    public function test_unset()
    {
        # Parent
        $this->Stream->Parent = $this->getMockForAbstractClass('\\BLW\Type\AObject');

        unset($this->Stream->Parent);

        $this->assertNull($this->Stream->Parent, 'unset(IStream::$Parent) Did not reset $_Parent');

        # Status
        unset($this->Stream->Status);

        $this->assertSame(0, $this->Stream->Status, 'unset(IStream::$Status) Did not reset $_Status');

        # Undefined
        unset($this->Stream->undefined);
    }

    /**
     * @depends test_putContents
     * @covers ::doSerialize
     */
    public function test_serialize()
    {
        $this->Stream->putContents("line1\r\nline2");
        fseek($this->Stream->fp, 2);

        $this->assertInternalType('string', serialize($this->Stream), 'serialize(File) Returned an invalid value');
        $this->assertNotEmpty(serialize($this->Stream), 'serialize(File) Returned an invalid value');
    }

    /**
     * @depends test_serialize
     * @covers ::doUnserialize
     */
    public function test_unserialize()
    {
        $this->Stream->putContents("line1\r\nline2");
        fseek($this->Stream->fp, 2);

        $this->assertEmpty(strval(unserialize(serialize($this->Stream))), 'unserialize(serialize(File)) should equal empty file');
    }
}
