<?php
/**
 * OptionTest.php | Mar 29, 2014
 *
 * @filesource
 * @license MIT
 * @copyright Copyright (c) 2013-2018, mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * @package BLW\Command
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type\Command;

use ReflectionMethod;
use BLW\Model\GenericContainer;
use BLW\Model\InvalidArgumentException;

/**
 * Tests Command\Option data type
 * @package BLW\Command
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\Command\AOption
 */
class OptionTest extends \PHPUnit_Framework_TestCase
{
    const COMMANDLINE = "command file.txt\t-a\r\"value for a\" -b 'value \\' for b' -c\"value \\\" for c\" -d'value for d' -e1 foo -f\t--g gvalue --h=\"value for h\" --i=1 --j jvalue -k \"\n\n\r\n\" -x arg1 arg2 http://example.com \"\n\n\r\n\"";

    /**
     * @var \BLW\Type\Command\AOption
     */
    protected $Option = NULL;

    protected function setUp()
    {
        $this->Option = $this->getMockForAbstractClass('\BLW\Type\Command\AOption', array('test', 'test option'));
    }

    protected function tearDown()
    {
        $this->Option = NULL;
    }

    /**
     * @covers ::getFactoryMethods
     */
    public function test_getFactoryMethods()
    {
        $this->assertGreaterThanOrEqual(2, count($this->Option->getFactoryMethods()), 'IOption::getFactoryMethods() Returned an invalid value');
        $this->assertContainsOnlyInstancesOf('ReflectionMethod', $this->Option->getFactoryMethods(), 'IOption::getFactoryMethods() Returned an invalid value');
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        $this->Option = $this->getMockForAbstractClass('\BLW\Type\Command\AOption', array('test', 'test option'));

        # Check properties
        $this->assertAttributeSame('test', '_Name', $this->Option,  'IOption::__construct() failed to set $_Name');
        $this->assertAttributeSame('test option',  '_Value', $this->Option, 'IOption::__construct() failed to set $_Value');

        # true value
        $this->Option = $this->getMockForAbstractClass('\BLW\Type\Command\AOption', array('test', true));

        $this->assertAttributeSame('',  '_Value', $this->Option, 'IOption::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            $this->getMockForAbstractClass('\\BLW\\Type\\Command\\AOption', array(NULL, 'foo'));
            $this->fail('Failed to generate exception with invalid arguments');
        } catch (InvalidArgumentException $e) {}

        try {
            $this->getMockForAbstractClass('\\BLW\\Type\\Command\\AOption', array('foo', NULL));
            $this->fail('Failed to generate exception with invalid arguments');
        } catch (InvalidArgumentException $e) {}
    }

    /**
     * @depends test_construct
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertRegExp('!--test=["\']test option["\']!', strval($this->Option), '(strval) IOption returned an invalid value');

        $this->Option = $this->getMockForAbstractClass('\BLW\Type\Command\AOption', array('t', true));

        $this->assertEquals('-t', strval($this->Option), '(strval) IOption returned an invalid value');
    }

    /**
     * @covers ::splitCommandLine
     */
    public function test_splitCommandLine()
    {
        $Expected  = array(
            'command','file.txt','-a','value for a','-b',"value \\' for b",'-c', 'value \\" for c', '-d','value for d','-e1','foo','-f'
            ,'--g','gvalue','--h=', 'value for h','--i=1', '--j','jvalue','-k',"\n\n\r\n",'-x','arg1','arg2','http://example.com',"\n\n\r\n"
        );

        $this->assertEquals($Expected, $this->Option->splitCommandLine(self::COMMANDLINE), 'AOption::_splitCommandLine() returned an invalid value');
    }

    public function generateArgV()
    {
        $Expected1      = new GenericContainer(IOption::CLASSNAME);
        $Expected2      = new GenericContainer(IOption::CLASSNAME);
        $Expected1['x'] = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\AOption', array('x', true));
        $Expected2['a'] = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\AOption', array('a', 'foo'));
        $Tricky         = new GenericContainer(IOption::CLASSNAME);

        return array(
             array(array('command', 'file', '-x',           'argument'), array('x'), $Expected1)
            ,array(array('command', 'file', '-a',   'foo',  'argument'), array('x'), $Expected2)
            ,array(array('command', 'file', '-afoo',        'argument'), array('x'), $Expected2)
            ,array(array('command', 'file', '--a',  'foo',  'argument'), array('x'), $Expected2)
            ,array(array('command', 'file', '--a=', 'foo',  'argument'), array('x'), $Expected2)
            ,array(array('command', 'file', '--a=foo',      'argument'), array('x'), $Expected2)
            ,array(new \ArrayObject(array('command', 'file', '--a=foo', 'argument')), array('x'), $Expected2)
            ,array(array( '', null, array(), false, 0, 0.0), array('x'), $Tricky)
        );
    }

    /**
     * @depends test_construct
     * @covers ::createFromArray
     */
    public function test_createFromArray()
    {
        # Valid input
        foreach ($this->generateArgV() as $Arguments) {

            list($Input, $NoValue, $Expected) = $Arguments;

            $this->assertEquals($Expected, $this->Option->createFromArray($Input, $NoValue), 'IOption::createFromArray() returned an ivalid value');
        }

        # Invalid input
        try {
            $this->Option->createFromArray(NULL);
            $this->fail('Failedto generate exception with invalid arguments');
        } catch (InvalidArgumentException $e) {}
    }

    /**
     * @depends test_createFromArray
     * @covers ::createFromString
     */
    public function test_createFromString()
    {
        $Expected      = new GenericContainer(IOption::CLASSNAME);
        $Expected['a'] = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\AOption', array('a', 'value for a'));
        $Expected['b'] = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\AOption', array('b', 'value \\\' for b'));
        $Expected['c'] = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\AOption', array('c', 'value \\" for c'));
        $Expected['d'] = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\AOption', array('d', 'value for d'));
        $Expected['e'] = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\AOption', array('e', '1'));
        $Expected['f'] = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\AOption', array('f', true));
        $Expected['g'] = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\AOption', array('g', 'gvalue'));
        $Expected['h'] = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\AOption', array('h', 'value for h'));
        $Expected['i'] = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\AOption', array('i', '1'));
        $Expected['j'] = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\AOption', array('j', 'jvalue'));
        $Expected['k'] = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\AOption', array('k', "\n\n\r\n"));
        $Expected['x'] = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\AOption', array('x', true));

        #Valid values
        $this->assertEquals($Expected, $this->Option->createFromString(self::COMMANDLINE, array('x')), 'IOption::createFromString() returned an invalid value');

        # Invalid arguments
        try {
            $this->Option->createFromString(NULL);
            $this->fail('Failedto generate exception with invalid arguments');
        } catch (InvalidArgumentException $e) {}
    }

    /**
     * @depends test_construct
     * @covers ::__get
     */
    public function test_get()
    {
        # Name
        $this->assertSame('test', $this->Option->Name, 'IOption::$Name should be `test`');

        # Value
        $this->assertSame('test option', $this->Option->Value, 'IOption::$Value should be `foo`');

        # Undefined
        try {
            $this->Option->undefined;
            $this->fail('Failed to generate notice with undefined property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {}

        $this->assertNull(@$this->Option->undefined, 'IOption::$undefined should be NULL');
    }


    /**
     * @depends test_construct
     * @covers ::__isset
     */
    public function test_isset()
    {
        # Name
        $this->assertTrue(isset($this->Option->Name), 'IOption::$Name should exist');

        # Value
        $this->assertTrue(isset($this->Option->Value), 'IOption::$Value should exist');

        # Undefined
        $this->assertFalse(isset($this->Option->undefined), 'IOption::$undefined should not exist');
    }

    /**
     * @depends test_construct
     * @covers ::__set
     */
    public function test_set()
    {
        # Name
        try {
            $this->Option->Name = '';
            $this->fail('Failed to genereate notice with readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Option->Name = '';

        # Value
        try {
            $this->Option->Value = '';
            $this->fail('Failed to genereate notice with readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Option->Value = '';

        # Undefined
        try {
            $this->Option->undefined = '';
            $this->fail('Failed to generate notice with undefined property');
        } catch (\PHPUnit_Framework_Error $e) {
            $this->assertContains('non-existant property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Option->undefined = '';
    }

    /**
     * @depends test_construct
     * @covers ::__unset
     */
    public function test_unset()
    {
        # Name
        try {
            unset($this->Option->Name);
            $this->fail('Failed to generate notice with readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Option->__unset('Name');

        # Value
        try {
            unset($this->Option->Value);
            $this->fail('Failed to generate notice with readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Option->__unset('Value');

        # Undefined
        unset($this->Option->undefined);
    }
}
