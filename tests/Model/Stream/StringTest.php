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
namespace BLW\Model\Stream;

use ReflectionProperty;
use PHPUnit_Framework_Error_Notice;

use BLW\Type\IFile;
use BLW\Type\IStream;
use BLW\Model\Stream\String as Stream;
use BLW\Model\InvalidArgumentException;

/**
 * Tests BLW Library Adaptor type.
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
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
     * @var array
     */
    protected $Context = array();

    /**
     *
     * @var \BLW\Type\IStream
     */
    protected $Stream = NULL;

    protected function setUp()
    {
        $this->Context = array(
            'http' => array(
                'method' => "GET",
                'header' => "Accept-language: en\r\n" .
                            "Cookie: foo=bar\r\n"
            )
        );

        $this->Stream = new Stream($this->String, IFile::READ | IFile::WRITE | IFile::TRUNCATE, $this->Context);
    }

    protected function tearDown()
    {
        $this->String  = '';
        $this->Stream  = NULL;
        $this->Context = array();
    }

    public function generateFlags()
    {
        return array(
            array(IFile::READ, 'r'),
            array(IFile::WRITE, 'r'),
            array(IFile::READ | IFile::WRITE, 'r+'),
            array(IFile::READ | IFile::WRITE | IFile::TRUNCATE, 'w+'),
            array(IFile::WRITE | IFile::TRUNCATE, 'w')
        );
    }

    /**
     * @covers ::__construct
     * @covers ::_getfp
     */
    public function test_construct()
    {
        foreach ($this->generateFlags() as $Arguments) {

            list($flags) = $Arguments;

            $String = '';
            $Stream = new Stream($String, $flags);

            $this->assertInternalType('resource', $this->readAttribute($Stream, '_fp'), 'String::__construct() Failed to create file pointer');

            unset($Stream);
        }

        # Invalid String
        try {
            $Input   = 1;
            $Invalid = new Stream($Input);
            $this->fail('Failded to generate exception with invalid parameters');
        } catch (InvalidArgumentException $e) {}

        # Invalid Mode
        try {
            $Invalid = new Stream($this->String, IFile::WRITE | IFile::APPEND);
            $this->fail('Failded to generate exception with invalid parameters');
        } catch (InvalidArgumentException $e) {}

        # Invalid Mode
        try {
            $Invalid = new Stream($this->String, IFile::READ | IFile::WRITE | IFile::APPEND);
            $this->fail('Failded to generate exception with invalid parameters');
        } catch (InvalidArgumentException $e) {}
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
    public function test_get()
    {
        # Status
        $this->assertAttributeSame($this->Stream->Status, '_Status', $this->Stream, 'IObject::$Status should equal IObject::_Status');

        # Serializer
        $this->assertSame($this->Stream->getSerializer(), $this->Stream->Serializer, 'IObject::$Serializer should equal IObject::getSerializer()');

        # Parent
        $this->assertNULL($this->Stream->Parent, 'IObject::$Parent should initially be NULL');

        # ID
        $this->assertSame($this->Stream->getID(), $this->Stream->ID, 'IObject::$ID should equal IObject::getID()');

        # fp
        $this->assertTrue(is_resource($this->Stream->fp), 'IStream::$fp should be a resource');

        # String
        $this->assertEquals($this->String, $this->Stream->String, sprintf('IStream::$String should equal `%s`', $this->String));

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

        # String
        $this->assertTrue(isset($this->Stream->String), 'IStream::$String should exist');

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

        # String
        try {
            $this->Stream->String = 0;
            $this->fail('Failed to generate notice on readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Stream->String = 0;

        # Invalid property
        try {
            $this->Stream->undefined = 0;
            $this->fail('Failed to generate notice on readonly property');
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
     * @covers ::doSerialize
     */
    public function test_serialize()
    {
        $this->assertInternalType('string', serialize($this->Stream), 'serialize(File) Returned an invalid value');
        $this->assertNotEmpty(serialize($this->Stream), 'serialize(File) Returned an invalid value');
    }

    /**
     * @depends test_serialize
     * @covers ::doUnserialize
     */
    public function test_unserialize()
    {
        $this->assertEquals(strval($this->Stream), strval(unserialize(serialize($this->Stream))), 'unserialize(serialize(File)) should equal File');
    }
}
