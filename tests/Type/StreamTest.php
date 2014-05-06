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
namespace BLW\Tests\Model\Stream;

use BLW\Type\IFile;
use BLW\Type\IStream;
use BLW\Model\Stream\File as Stream;
use BLW\Model\InvalidArgumentException;

/**
 * Tests BLW Library Adaptor type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Stream\File
 */
class StreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $fp = NULL;

    /**
     * @var \BLW\Type\IStream
     */
    protected $Stream = NULL;

    protected function setUp()
    {
        $this->fp     = fopen('php://memory', 'r+');
        $this->Stream = $this->getMockForAbstractClass('\\BLW\\Type\\AStream');

        $fp           = new \ReflectionProperty($this->Stream, '_fp');

        $fp->setAccessible(true);
        $fp->setValue($this->Stream, $this->fp);
        $fp->setAccessible(false);
    }

    protected function tearDown()
    {
        $this->Stream = NULL;

        fclose($this->fp);
    }

    /**
     * @covers ::getContents
     */
    public function test_getContents()
    {
        $this->assertEquals(stream_get_contents($this->fp), $this->Stream->getContents(), 'IStream::getContents() should return contents of stream');
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
     * @dataProvider generateContents
     * @covers ::putContents
     */
    public function test_putContents($String, $Data)
    {
        $this->assertEquals(strlen($String), $this->Stream->putContents($Data), 'IStream::putContents() returned an invalid byte count');
        $this->assertEquals($String, $this->Stream->getContents(), sprintf('IStream::getContents() should equal `%s`', $String));
    }

    /**
     * @depends test_putContents
     * @dataProvider generateContents
     * @covers ::addFilter
     */
    public function test_addFilter($String, $Data)
    {
        $this->assertTrue(is_resource($this->Stream->addFilter('string.rot13', STREAM_FILTER_WRITE)), 'IStream::addFilter() should return a filter resource');
        $this->assertEquals(strlen($String), $this->Stream->putContents($Data), 'IStream::putContents() returned an invalid byte count');
        $this->assertEquals(str_rot13($String), $this->Stream->getContents(), sprintf('IStream::getContents() should equal `str_rot13(%s)`', $String));

        try {
            $this->Stream->addFilter('invalid.filter', STREAM_FILTER_WRITE);
            $this->fail('IStream::addFilter() should throw an exception');
        }

        catch(InvalidArgumentException $e) {}
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
     * @covers ::getID
     */
    public function test_getID()
    {
        $this->assertEquals(print_r($this->fp, true), $this->Stream->getID(), 'IStream::getID() returned an invalid value');

    }
}