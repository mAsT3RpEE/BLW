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
namespace BLW\Tests\Type;

use BLW\Type\IFile;


/**
 * Tests BLW Library Adaptor type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\IFile
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
     * @covers ::getMimeType
     */
    public function test_getMimeType()
    {
        $this->assertEquals('text/html', $this->File->getMimeType());
    }

    /**
     * @covers ::getContents
     */
    public function test_getContents()
    {
        $this->assertEquals(file_get_contents(__FILE__), $this->File->getContents(), 'IFile::getContents() should equal file_get_contents(__FILE__)');
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
    }

    /**
     * @covers ::openFile
     */
    public function test_openFile()
    {
        $this->assertInstanceOf('SplFileInfo', $this->File->Component, 'IFile::$Component should be an instance of SplFileInfo');
        $this->assertTrue($this->File->openFile(IFile::READ), 'IFile::openFile() should return true');
        $this->assertInstanceOf('SplFileObject', $this->File->Component, 'IFile::$Component should be an instance of SplFileObject');

        return $this->File;
    }

    /**
     * @depends test_openFile
     * @covers ::isOpen
     */
    public function test_isOpen($File)
    {
        $this->assertTrue($File->isOpen(), 'IFile::isOpen() should return TRUE');
        $this->assertFalse($this->File->isOpen(), 'IFile::isOpen() should return FALSE');
    }

    /**
     * @depends test_openFile
     * @covers ::seek
     */
    public function test_seek($File)
    {
        $File->seek(2);
        $this->assertContains('FileTest.php | Feb 25, 2014', $File->current(), 'IFile::seek() should advance pointer');
    }

    /**
     * @depends test_openFile
     * @covers ::closeFile
     */
    public function test_closeFile($File)
    {
        $this->assertTrue($File->closeFile(), 'IFile::closeFile() should return TRUE');
        $this->assertInstanceOf('SplFileInfo', $File->Component, 'IFile::$Component should be an instance of SplFileInfo');
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
     * @covers ::serializeWith
     */
    public function test_serializeWith()
    {
        $Hash       = spl_object_hash($this->File);
        $Serialized = $this->File->serializeWith($this->Serializer, -1);

        $this->assertEquals($Hash, $Serialized, 'ISerializable::serializeWith(MockSerializer) should return spl_object_hash() of object.');
        $this->assertEquals(-1, $this->Serializer->flags);
    }

    /**
     * @covers ::unserializeWith
     */
    public function test_unserializeWith()
    {
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