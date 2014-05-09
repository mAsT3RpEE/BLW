<?php
/**
 * ContentDispositionTest.php | Mar 10, 2014
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
use BLW\Model\MIME\ContentDisposition;


/**
 * Tests BLW Library MIME Contetn-Type header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\ContentDisposition
 */
class ContentDispositionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\MIME\ContentDisposition
     */
    protected $Header = NULL;

    /**
     * @var \ReflectionProperty[]
     */
    protected $Properties = array();

    protected function setUp()
    {
        $this->Header      = new ContentDisposition('inline', array('filename' => 'test.png'));
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

    public function generateValidDispositions()
    {
        return array(
        	 array('inline', 'inline')
            ,array('test', 'test')
        );
    }

    public function generateInvalidDispositions()
    {
        return array(
        	 array('pre-;-post', 'attachment')
            ,array('pre-?-post', 'attachment')
            ,array('pre-(-post', 'attachment')
            ,array('pre-)-post', 'attachment')
        	,array(false, 'attachment')
        );
    }

    /**
     * @covers ::parseDisposition
     */
    public function test_parseDisposition()
    {
        # Valid type
        foreach($this->generateValidDispositions() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseDisposition($Original), 'ContentDisposition::parseDisposition() returned an invalid format');
        }

        # Invalid type
        foreach($this->generateInvalidDispositions() as $Parameters) {
            list($Original, $Expected) = $Parameters;

            $this->assertEquals($Expected, $this->Header->parseDisposition($Original), 'ContentDisposition::parseDisposition() returned an invalid format');
        }
    }

    /**
     * @depends test_parseDisposition
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Check params
        $this->assertEquals('Content-Disposition', $this->Properties['Type']->getValue($this->Header), 'ContentDisposition::__construct() failed to set $_Type');
        $this->assertEquals('inline; filename=test.png', $this->Properties['Value']->getValue($this->Header), 'ContentDisposition::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            new ContentDisposition(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        }

        catch (InvalidArgumentException $e) {}

        # No parameters
        $this->Header = new ContentDisposition('inline');
        $this->assertEquals('inline', $this->Properties['Value']->getValue($this->Header), 'ContentDisposition::__construct() failed to set $_Value');

        # Invalid property
        try {
            new ContentDisposition('inline', array('???' => '???'));
            $this->fail('Failed to generate notice with invalid arguments');
        }

        catch (\PHPUnit_Framework_Error_Notice $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("Content-Disposition: inline; filename=test.png\r\n", @strval($this->Header), 'ContentDisposition::__toSting() returned an invalid format');
    }
}
