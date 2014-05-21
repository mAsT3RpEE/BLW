<?php
/**
 * Version.php | Mar 10, 2014
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
 * Tests BLW Library MIME Version header.
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\MIMEVersion
 */
class MIMEVersionTest extends \PHPUnit_Framework_TestCase
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
        $this->Header      = new MIMEVersion('1.0');
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

    public function generateValidVersions()
    {
        return array(
             array('1.0')
            ,array(1)
            ,array(1.0)
        );
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Check params
        $this->assertEquals('MIME-Version', $this->Properties['Type']->getValue($this->Header), 'MIMEVersion::__construct() failed to set $_Type');
        $this->assertSame(1.0, $this->Properties['Value']->getValue($this->Header), 'MIMEVersion::__construct() failed to set $_Value');

        # Valid arguments
        foreach ($this->generateValidVersions() as $Arguments) {
            list($Version) = $Arguments;

            $this->Header = new MIMEVersion($Version);

            $this->assertGreaterThan(0.0, $this->Properties['Value']->getValue($this->Header), 'MIMEVersion::__construct() failed to set $_Value');
        }

        # Invalid arguments
        try {
            new MIMEVersion(NULL);
            $this->fail('Failed to generate exception with invalid parameters');
        } catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals("MIME-Version: 1.0\r\n", @strval($this->Header), 'MIMEVersion::__toSting() returned an invalid format');

        @strval($this->Header);

        # Update Type
        $Property = new \ReflectionProperty($this->Header, '_Type');

        $Property->setAccessible(true);
        $Property->setValue($this->Header, '');

        @strval($this->Header);

        $e = error_get_last();

        $this->assertContains('Type or Value', $e['message'], 'Failed to generate warning on (string) IHeader');
    }
}
