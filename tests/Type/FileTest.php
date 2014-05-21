<?php
/**
 * FileTest.php | Feb 25, 2014
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
namespace BLW\Type;

use SplFileObject;

use BLW\Model\InvalidArgumentException;
use BLW\Model\FileException;

/**
 * Tests BLW Library Adaptor type.
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 *  @coversDefaultClass \BLW\Type\AFile
 */
class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\AFile
     */
    protected $File;

    protected function setUp()
    {
        $this->Serializer = new \BLW\Model\Serializer\Mock;
        $this->File       = $this->getMockForAbstractClass('\\BLW\\Type\\AFile', array(__FILE__));
    }

    protected function tearDown()
    {
        $this->File = NULL;
    }

    /**
     * @covers ::getFactoryMethods
     */
    public function test_getFactoryMethods()
    {
        $this->assertNotEmpty($this->File->getFactoryMethods(), 'IFile::getFactoryMethods() Returned an invalid value');
        $this->assertContainsOnlyInstancesOf('ReflectionMethod', $this->File->getFactoryMethods(), 'IFile::getFactoryMethods() Returned an invalid value');
    }

    /**
     * @covers ::getMimeType
     */
    public function test_getMimeType()
    {
        $this->assertRegExp('!text/html|text/[\w\x2b\x2d]+; charset=us-ascii!', $this->File->getMimeType());
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Valid values
        $File = $this->getMockForAbstractClass('\\BLW\\Type\\AFile', array(__FILE__));

        $this->assertAttributeSame(__FILE__, '_FileName', $File, 'IFile::__construct() Failed to save file path');

        # SplFileObject
        $File = $this->getMockForAbstractClass('\\BLW\\Type\\AFile', array(new SplFileObject(__FILE__, 'r')));

        $this->assertAttributeSame(__FILE__, '_FileName', $File, 'IFile::__construct() Failed to save file path');

        # Object implementing toString
        $File = $this->getMockForAbstractClass('\\BLW\\Type\\AFile', array($this->File));

        $this->assertAttributeSame($this->File->getPathname(), '_FileName', $File, 'IFile::__construct() Failed to save file path');

        # Invalid file
        try {
            $this->getMockForAbstractClass('\\BLW\\Type\\AFile', array(NULL));
            $this->fail('Failed to generate exception with invalid argument');
        } catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::getContents
     */
    public function test_getContents()
    {
        $this->assertEquals(file_get_contents(__FILE__), $this->File->getContents(), 'IFile::getContents() should equal file_get_contents(__FILE__)');

        # With context
        $Context = stream_context_create(array(), array(
        'notification' => function () {}
        ));

        $this->assertEquals(file_get_contents(__FILE__), $this->File->getContents(IFile::FILE_FLAGS, $Context), 'IFile::getContents() should equal file_get_contents(__FILE__)');
    }

    /**
     * @covers ::putContents
     */
    public function test_putContents()
    {
        $Test = 'foo bar foo bar foo bar';
        $Path = tempnam(sys_get_temp_dir(), 'foo');
        $File = $this->getMockForAbstractClass('\\BLW\\Type\\AFile', array($Path));

        $File->putContents($Test);
        $this->assertEquals($Test, file_get_contents($Path), 'IFile::putContents() created a currupt file');

        unlink($Path);

        # With context
        $Context = stream_context_create(array(), array(
            'notification' => function () {}
        ));

        $File->putContents($Test, $Context);

        $this->assertEquals($Test, file_get_contents($Path), 'IFile::putContents() created a currupt file');

        // Invalid Arguments
        try {
            $this->File->putContents(NULL);
            $this->fail('Failed to generate exception with invalid arguments');
        } catch (InvalidArgumentException $e) {}
    }

    public function generateFlags()
    {
        return array(
             array(IFile::READ, 'r')
            ,array(IFile::READ | IFile::WRITE, 'r+')
            ,array(IFile::READ | IFile::WRITE | IFile::TRUNCATE, 'w+')
            ,array(IFile::READ | IFile::WRITE | IFile::APPEND, 'a+')
            ,array(IFile::WRITE | IFile::TRUNCATE, 'w')
            ,array(IFile::WRITE | IFile::APPEND, 'a')
            ,array(IFile::WRITE, 'r')
        );
    }

    /**
     * @covers ::buildMode
     */
    public function test_buildMode()
    {
        foreach ($this->generateFlags() as $Arguments) {

            list ($flags, $Mode) = $Arguments;

            $this->assertSame($Mode, $this->File->buildMode($flags), 'IFile::buildMode() Returned an invalid read format');
        }
    }

    /**
     * @covers ::createResource
     */
    public function test_createResource()
    {
        $context = stream_context_create(array(
            'http' => array(
                'method' => 'GET',
                'header' => array(
                    'Accept-language: en'
                ),
                'user_agent' => 'PHP/' . PHP_VERSION
            )
        ), array(
            'notification' => function ($notification_code, $severity, $message, $message_code, $bytes_transferred, $bytes_max) {}
        ));

        $fp = $this->File->createResource();

        $this->assertInternalType('resource', $fp, 'IFile::createResource() Returned an invalid value');
        @fclose($fp);

        $fp = $this->File->createResource(IFile::FILE_FLAGS, $context);

        $this->assertInternalType('resource', $fp, 'IFile::createResource() Returned an invalid value');
        @fclose($fp);
    }

    /**
     * @covers ::openFile
     */
    public function test_openFile()
    {
        $this->assertInstanceOf('SplFileInfo', $this->File->Component, 'IFile::$Component should be an instance of SplFileInfo');
        $this->File->openFile(IFile::READ);
        $this->assertInstanceOf('SplFileObject', $this->File->Component, 'IFile::$Component should be an instance of SplFileObject');

        $context = stream_context_create(array(
            'http' => array(
                'method' => 'GET',
                'header' => array(
                    'Accept-language: en'
                ),
                'user_agent' => 'PHP/' . PHP_VERSION
            )
        ), array(
            'notification' => function ($notification_code, $severity, $message, $message_code, $bytes_transferred, $bytes_max) {}
        ));

        $this->File->openFile(IFile::READ, $context);
        $this->assertInstanceOf('SplFileObject', $this->File->Component, 'IFile::$Component should be an instance of SplFileObject');

        # Invalid file
        $File = $this->getMockForAbstractClass('\\BLW\\Type\\AFile', array('x:\\undefined\\!!!'));

        try {
            $File->openFile();
            $this->fail('Failed to generate exception with invalid file');

        } catch (FileException $e) {

        }
    }

    /**
     * @depends test_openFile
     * @covers ::isOpen
     */
    public function test_isOpen()
    {
        $this->assertFalse($this->File->isOpen(), 'IFile::isOpen() should return FALSE');
        $this->File->openFile(IFile::READ);
        $this->assertTrue($this->File->isOpen(), 'IFile::isOpen() should return FALSE');
    }

    /**
     * @depends test_openFile
     * @covers ::closeFile
     */
    public function test_closeFile()
    {
        $this->File->openFile(IFile::READ);
        $this->assertTrue($this->File->closeFile(), 'IFile::closeFile() should return TRUE');
        $this->assertInstanceOf('SplFileInfo', $this->File->Component, 'IFile::$Component should be an instance of SplFileInfo');
        $this->assertFalse($this->File->closeFile(), 'IFile::closeFile() should return FALSE');
    }

    /**
     * @depeds test_openFile
     * @covers ::valid()
     */
    public function test_valid()
    {
        $File = $this->getMockForAbstractClass('\\BLW\\Type\\AFile', array("data:text/plain,line1\r\nline2\r\nline3"));

        try {
            $File->valid();
            $this->fail('Failed to generate warning with closed file');
        } catch (\PHPUnit_Framework_Error_Notice $e) {}

        @$File->valid();

        $File->openFile();
        $this->assertTrue($File->valid(), 'IFile::valid() should return true');
    }

    /**
     * @depeds test_openFile
     * @covers ::current
     */
    public function test_current()
    {
        $File = $this->getMockForAbstractClass('\\BLW\\Type\\AFile', array("data:text/plain,line1\r\nline2\r\nline3"));

        try {
            $File->current();
            $this->fail('Failed to generate warning with closed file');
        } catch (\PHPUnit_Framework_Error_Notice $e) {}

        @$File->current();

        $File->openFile();
        $this->assertSame("line1\r\n", $File->current(), 'IFile::current() should return `line1`');
    }

    /**
     * @depeds test_openFile
     * @covers ::key
     */
    public function test_key()
    {
        $File = $this->getMockForAbstractClass('\\BLW\\Type\\AFile', array("data:text/plain,line1\r\nline2\r\nline3"));

        try {
            $File->key();
            $this->fail('Failed to generate warning with closed file');
        } catch (\PHPUnit_Framework_Error_Notice $e) {}

        @$File->key();

        $File->openFile();
        $this->assertSame(0, $File->key(), 'IFile::key() should return 1');
    }

    /**
     * @depends test_current
     * @depends test_key
     * @covers ::next
     * @covers ::key
     * @covers ::current
     * @covers ::valid
     */
    public function test_next()
    {
        $File = $this->getMockForAbstractClass('\\BLW\\Type\\AFile', array("data:text/plain,line1\r\nline2\r\nline3"));

        try {
            $File->next();
            $this->fail('Failed to generate warning with closed file');
        } catch (\PHPUnit_Framework_Error_Notice $e) {}

        @$File->next();

        $File->openFile();
        $this->expectOutputString("0: line1\r\n1: line2\r\n2: line3");

        for(;$File->valid() ; $File->next())
            echo $File->key(). ': ' . $File->current();
    }

    /**
     * @depends test_next
     * @covers ::next
     * @covers ::key
     * @covers ::current
     * @covers ::valid
     * @covers ::rewind
     */
    public function test_rewind()
    {
        $File = $this->getMockForAbstractClass('\\BLW\\Type\\AFile', array("data:text/plain,line1\r\nline2\r\nline3\r\n"));

        try {
            $File->rewind();
            $this->fail('Failed to generate warning with closed file');
        } catch (\PHPUnit_Framework_Error_Notice $e) {}

        @$File->rewind();

        $File->openFile();
        $this->expectOutputString("0: line1\r\n1: line2\r\n2: line3\r\n0: line1\r\n1: line2\r\n2: line3\r\n");

        foreach($File as $k => $v)
            echo "$k: $v";

        foreach($File as $k => $v)
            echo "$k: $v";
    }

    /**
     * @depeds test_getChildren
     * @covers ::getChildren
     */
    public function test_getChildren()
    {
        $File = $this->getMockForAbstractClass('\\BLW\\Type\\AFile', array("data:text/plain,line1\r\nline2\r\nline3"));

        try {
            $File->getChildren();
            $this->fail('Failed to generate warning with closed file');
        } catch (\PHPUnit_Framework_Error_Notice $e) {}

        @$File->getChildren();

        $File->openFile();
        $this->assertNull($File->getChildren(), 'IFile::getChildren() should return NULL');
    }

    /**
     * @depeds test_openFile
     * @covers ::hasChildren
     */
    public function test_hasChildren()
    {
        $File = $this->getMockForAbstractClass('\\BLW\\Type\\AFile', array("data:text/plain,line1\r\nline2\r\nline3"));

        try {
            $File->hasChildren();
            $this->fail('Failed to generate warning with closed file');
        } catch (\PHPUnit_Framework_Error_Notice $e) {}

        @$File->hasChildren();

        $File->openFile();
        $this->assertFalse($File->hasChildren(), 'IFile::hasChildren() should return 1');
    }

    /**
     * @depends test_openFile
     * @covers ::seek
     */
    public function test_seek($File)
    {
        $File = $this->getMockForAbstractClass('\\BLW\\Type\\AFile', array("data:text/plain,line1\r\nline2\r\nline3"));

        try {
            $File->seek(2);
            $this->fail('Failed to generate warning with closed file');
        } catch (\PHPUnit_Framework_Error_Notice $e) {}

        @$File->seek(2);

        $File->openFile();
        $File->seek(2);
        $this->assertSame("line3", $File->current(), 'IFile::seek() should advance pointer');
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals(__FILE__, @strval($this->File), 'strval(IFile) should equal `__FILE__`');
    }

    /**
     * @covers ::getID
     */
    public function test_getID()
    {
        $this->assertEquals(md5(__FILE__), $this->File->getID(), 'IFile::getID() should equal md5(__FILE__)');
    }

    /**
     * @covers ::doSerialize()
     */
    public function test_serializeWith()
    {
        $this->File->openFile();

        $Hash       = spl_object_hash($this->File);
        $Serialized = $this->File->serializeWith($this->Serializer, -1);

        $this->assertEquals($Hash, $Serialized, 'ISerializable::serializeWith(MockSerializer) should return spl_object_hash() of object.');
        $this->assertEquals(-1, $this->Serializer->flags);
    }

    /**
     * @covers ::doUnserialize()
     */
    public function test_unserializeWith()
    {
        $this->File->openFile();

        $Unserialized    = $this->getMockForAbstractClass('\\BLW\\Type\AFile', array($this->File));
        $this->File->foo = 1;
        $this->File->bar = 1;
        $this->File->pie = 1;
        $Serialized      = $this->File->serializeWith($this->Serializer, 0);

        $this->assertNotEquals($this->File, $Unserialized, '$Unserialized should not equal $this->File');
        $this->assertTrue($Unserialized->unserializeWith($this->Serializer, $Serialized, -1), 'ISerializable::unserializeWith() should return TRUE');
        $this->assertEquals($this->File, $Unserialized, '$Unserialized should equal $this->File');
        $this->assertEquals(-1, $this->Serializer->flags);
    }
}
