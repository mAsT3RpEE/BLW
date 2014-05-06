<?php
/**
 * ConfigTest.php | Feb 12, 2014
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

use ArrayObject;
use PHPUnit_Framework_Error_Notice;
use BadMethodCallException;
use DOMElement;

/**
 * Tests BLW Library IConfig type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\IConfig
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\IConfig
     */
    protected $Config = NULL;

    protected function setUp()
    {
        $this->Config = $this->getMockForAbstractClass('\\BLW\\Type\\AConfig', array(array(
        	 'foo'    => 1
            ,'bar'    => 1
            ,'object' => new ArrayObject(array('foo' => 1))
        )));
    }

    protected function tearDown()
    {
        $this->Config = NULL;
    }

    /**
     * @covers ::offsetSet
     */
    public function test_offsetSet()
    {
        # Test valid
        $Test = new ArrayObject(array(
        	 'foo'    => 1
            ,'bar'    => 1
            ,'object' => new ArrayObject(array('foo' => 1))
        ));

        $this->Config['test'] = $Test;
        $this->assertEquals($Test, $this->Config['test'], 'IConfig[test] should equal $Test');

        $this->Config['test'] = 1;
        $this->assertEquals(1, $this->Config['test'], 'IConfig[test] should equal 1');

        $this->Config['test'] = 'foo';
        $this->assertEquals('foo', $this->Config['test'], 'IConfig[test] should equal `foo`');

        # Test invalid
        try {
            $this->Config['test'] = array();
            $this->fail('Unable to generate exception with invalid value');
        }

        catch(\UnexpectedValueException $e) {
            $this->assertContains('Instance of ArrayAccess expected', $e->getMessage(), 'Invalid exception: '.$e->getMessage());
        }
    }

    /**
     * @covers ::append
     */
    public function test_append()
    {
        # Test valid
        $Test = new ArrayObject(array(
        	 'foo'    => 1
            ,'bar'    => 1
            ,'object' => new ArrayObject(array('foo' => 1))
        ));

        $this->Config->append($Test);
        $this->assertEquals($Test, $this->Config[0], 'IConfig[0] should equal $Test');

        $this->Config->append(1);
        $this->assertEquals(1, $this->Config[1], 'IConfig[test] should equal 1');

        $this->Config->append('foo');
        $this->assertEquals('foo', $this->Config[2], 'IConfig[test] should equal `foo`');

        # Test invalid
        try {
            $this->Config->append(array());
            $this->fail('Unable to generate exception with invalid value');
        }

        catch(\UnexpectedValueException $e) {
            $this->assertContains('Instance of ArrayAccess expected', $e->getMessage(), 'Invalid exception: '.$e->getMessage());
        }
    }

    /**
     * @covers ::__toString
     */
    public function test__toString()
    {
        $this->assertEquals('[IConfig:3]', strval($this->Config),'(string) IConfig returned an invalid format');
    }
}