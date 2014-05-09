<?php
/**
 * StreamTest.php | Feb 28, 2014
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
use BLW\Model\InvalidArgumentException;

/**
 * Tests BLW Library Adaptor type.
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\AStream
 */
class StreamTest extends \BLW\Type\IterableTest
{
    /**
     * @var string
     */
    protected $fp = NULL;

    /**
     * @var \BLW\Type\AStream
     */
    protected $Stream = NULL;

    protected function setUp()
    {
        $this->fp       = fopen('php://memory', 'r+');
        $this->Stream   = $this->getMockForAbstractClass('\\BLW\\Type\\AStream');
        $this->Iterable = $this->Stream;

        $Property = new \ReflectionProperty($this->Stream, '_fp');

        $Property->setAccessible(true);
        $Property->setValue($this->Stream, $this->fp);
    }

    protected function tearDown()
    {
        $this->Stream   = NULL;
        $this->Iterable = NULL;

        fclose($this->fp);
    }

    /**
     * @covers ::getContents
     */
    public function test_getContents()
    {
        $this->assertEquals(stream_get_contents($this->fp), $this->Stream->getContents(), 'IStream::getContents() should return contents of stream');

        # Invalid stream
        $Property = new \ReflectionProperty($this->Stream, '_fp');

        $Property->setAccessible(true);
        $Property->setValue($this->Stream, null);

        try {
            $this->Stream->getContents();
            $this->fail('Failed to generate notice with invalid $_fp');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('invalid resource', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Stream->getContents();
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
     * @depends test_getContents
     * @covers ::putContents
     */
    public function test_putContents()
    {
        # Valid contents
        foreach ($this->generateContents() as $Arguments) {

            list ($String, $Data) = $Arguments;

            $this->assertEquals(strlen($String), $this->Stream->putContents($Data), 'IStream::putContents() returned an invalid byte count');
            $this->assertEquals($String, $this->Stream->getContents(), sprintf('IStream::getContents() should equal `%s`', $String));
        }

        # Invalid arguments
        try {
            $this->Stream->putContents(null);
            $this->fail('Failed to generate exception with invalid arguments');
        }

        catch (InvalidArgumentException $e) {}

        # Invalid stream
        $Property = new \ReflectionProperty($this->Stream, '_fp');

        $Property->setAccessible(true);
        $Property->setValue($this->Stream, null);

        try {
            $this->Stream->putContents('foo');
            $this->fail('Failed to generate notice with invalid $_fp');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('invalid resource', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Stream->putContents('foo');
    }

    /**
     * @depends test_putContents
     * @dataProvider generateContents
     * @covers ::addFilter
     * @covers ::_testFilter
     */
    public function test_addFilter($String, $Data)
    {
        # Valid arguments
        $this->assertTrue(is_resource($this->Stream->addFilter('string.rot13', STREAM_FILTER_WRITE)), 'IStream::addFilter() should return a filter resource');
        $this->assertEquals(strlen($String), $this->Stream->putContents($Data), 'IStream::putContents() returned an invalid byte count');
        $this->assertEquals(str_rot13($String), $this->Stream->getContents(), sprintf('IStream::getContents() should equal `str_rot13(%s)`', $String));

        # Invalid arguments
        try {
            $this->Stream->addFilter('invalid.filter', STREAM_FILTER_WRITE);
            $this->fail('IStream::addFilter() should throw an exception');
        }

        catch(InvalidArgumentException $e) {}

        # Invalid stream
        $Property = new \ReflectionProperty($this->Stream, '_fp');

        $Property->setAccessible(true);
        $Property->setValue($this->Stream, null);

        try {
            $this->Stream->addFilter('string.rot13');
            $this->fail('Failed to generate notice with invalid $_fp');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('invalid resource', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Stream->addFilter('string.rot13');
    }

    /**
     * @depends test_putContents
     * @covers ::remFilter
     */
    public function test_remFilter()
    {
        $String   = 'trololololo string contents';
        $Encoded1 = str_rot13($String);
        $Encoded2 = quoted_printable_encode($String);
        $Filter   = $this->Stream->addFilter('string.rot13', STREAM_FILTER_WRITE);

        $this->assertTrue(is_resource($Filter), 'IStream::addFilter() should return a filter resource');
        $this->assertEquals(strlen($String), $this->Stream->putContents($String), 'IStream::putContents() returned an invalid byte count');
        $this->assertEquals($Encoded1, $this->Stream->getContents(), sprintf('IStream::getContents() should equal `%s`', $Encoded1));
        $this->assertTrue($this->Stream->remFilter($Filter), 'IStream::remFilter() should return true');

        $this->assertTrue(is_resource($this->Stream->addFilter('convert.quoted-printable-encode', STREAM_FILTER_WRITE)), 'IStream::addFilter() should return a filter resource');
        $this->assertEquals(strlen($String), $this->Stream->putContents($String), 'IStream::putContents() returned an invalid byte count');
        $this->assertEquals($Encoded2, $this->Stream->getContents(), sprintf('IStream::getContents() should equal `%s`', $Encoded2));

        try {
            $this->Stream->remFilter($Filter);
            $this->fail('IStream::remFilter() should throw an exception');
        }

        catch(InvalidArgumentException $e) {}
    }

    /**
     * @depends test_putContents
     * @dataProvider generateContents
     * @covers ::__toString
     */
    public function test_toString($String, $Data)
    {
        $this->assertEquals(strlen($String), $this->Stream->putContents($Data), 'IStream::putContents() returned an invalid byte count');
        $this->assertEquals($String, strval($this->Stream), sprintf('string(IStream) should equal `%s`', $String));
    }

    /**
     * @covers ::__get
     */
    public function test_get()
    {
        # Status
        $this->assertAttributeSame($this->Stream->Status, '_Status', $this->Stream, 'IStream::$Status should equal IStream::_Status');

        # Serializer
        $this->assertSame($this->Stream->Serializer, $this->Stream->getSerializer(), 'IStream::$Serializer should equal IStream::getSerializer()');

        # Parent
        $this->assertSame($this->Stream->getParent(), $this->Stream->Parent, 'IStream::$Parent should equal IStream::getParent()');

        # ID
        $this->assertSame($this->Stream->ID, $this->Stream->getID(), 'IStream::$ID should equal IStream::getID()');

        # fp
        $this->assertSame($this->fp, $this->Stream->fp, 'IStream::$fp should equal $_fp');

        # Undefined
        try {
            $this->Stream->undefined;
            $this->fail('Failed to generate notice with undefined property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {}

        $this->assertNull(@$this->Stream->undefined, 'IStream::$undefined should be NULL');
    }


    /**
     * @covers ::__isset
     */
    public function test_isset()
    {
        # Status
       $this->assertTrue(isset($this->Stream->Serializer), 'IStream::$Status should exist');

        # Serializer
        $this->assertTrue(isset($this->Stream->Serializer), 'IStream::$Serializer should exist');

        # Parent
        $this->assertFalse(isset($this->Stream->Parent), 'IStream::$Parent should not exist');

        # ID
        $this->assertTrue(isset($this->Stream->ID), 'IStream::$ID should exist');

        # fp
        $this->assertTrue(isset($this->Stream->fp), 'IStream::$fp should exist');

        # Undefined
        $this->assertFalse(isset($this->Stream->undefined), 'IStream::$undefined should not exist');
    }

    /**
     * @depends test_get
     * @covers ::__set
     */
    public function test_set()
    {
        # Status
        try {
            $this->Stream->Status = 0;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Stream->Status = 0;

        # Serializer
        try {
            $this->Stream->Serializer = 0;
            $this->fail('Failed to generate notice on readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '.$e->getMessage());
        }

        @$this->Stream->Serializer = 0;

        # Parent
        $Parent               = $this->getMockForAbstractClass('\\BLW\\Type\\AObject');
        $this->Stream->Parent = $Parent;

        $this->assertSame($Parent, $this->Stream->Parent, 'IStream::$Parent should equal IStream::getParent()');

        try {
            $this->Stream->Parent = null;
            $this->fail('Failed to generate notice with invalid value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Invalid value', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Stream->Parent = null;

        try {
            $this->Stream->Parent = $Parent;
            $this->fail('Failed to generate notice with oneshot value');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        # ID
        try {
            $this->Stream->ID = 'foo';
            $this->fail('Failed to generate notice with redonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Stream->ID = 'foo';

        # fp
        try {
            $this->Stream->fp = null;
            $this->fail('Failed to generate notice with readonly property');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Stream->fp = null;

        # Undefined
        try {
            $this->Stream->undefined = '';
            $this->fail('Failed to generate notice with undefined property');
        }

        catch (\PHPUnit_Framework_Error $e) {
            $this->assertContains('non-existant property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Stream->undefined = '';
    }

    /**
     * @depends test_get
     * @depends test_set
     * @covers ::__unset
     */
    public function test_unset()
    {
        # Parent
        $this->Stream->Parent = $this->getMockForAbstractClass('\\BLW\Type\IObject');

        unset($this->Stream->Parent);

        $this->assertNull($this->Stream->Parent, 'unset(IStream::$Parent) Did not reset $_Parent');

        # Status
        unset($this->Stream->Status);

        $this->assertSame(0, $this->Stream->Status, 'unset(IStream::$Status) Did not reset $_Status');

        # Undefined
        unset($this->Stream->undefined);
    }
}