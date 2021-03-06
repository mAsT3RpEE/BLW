<?php
/**
 * ArgumentTest.php | Mar 29, 2014
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
 * Tests Command\Argument data type
 * @package BLW\Command
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\Command\AArgument
 */
class ArgumentTest extends \PHPUnit_Framework_TestCase
{
    const COMMANDLINE = "command file.txt\t-a\r\"value for a\" -b 'value \\' for b' -c\"value \\' for c\" -d'value for d' -e1 foo -f\t--g gvalue --h=\"value for h\" --i=1 --j jvalue -k \"\n\n\r\n\" -x arg1 arg2 http://example.com \"\n\n\r\n\"";


    /**
     * @var \BLW\Type\Command\AArgument
     */
    protected $Argument = NULL;

    protected function setUp()
    {
        $this->Argument = $this->getMockForAbstractClass('\BLW\Type\Command\AArgument', array('test argument'));
    }

    protected function tearDown()
    {
        $this->Argument = NULL;
    }

    /**
     * @covers ::getFactoryMethods
     */
    public function test_getFactoryMethods()
    {
        $this->assertGreaterThanOrEqual(2, count($this->Argument->getFactoryMethods()), 'IArgument::getFactoryMethods() Returned an invalid value');
        $this->assertContainsOnlyInstancesOf('ReflectionMethod', $this->Argument->getFactoryMethods(), 'IArgument::getFactoryMethods() Returned an invalid value');
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Check properties
        $this->assertAttributeSame('test argument', '_Value', $this->Argument, 'IArgument::__construct() failed to set $_Value');

        # Invalid arguments
        try {
            $this->getMockForAbstractClass('\\BLW\\Type\\Command\\AArgument', array(NULL));
            $this->fail('Failed to generate exception with invalid arguments');
        } catch (InvalidArgumentException $e) {}
    }

    /**
     * @depends test_construct
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertRegExp('!["\']test argument["\']!', strval($this->Argument), '(strval) IArgument returned an invalid value');

        $this->Argument = $this->getMockForAbstractClass('\BLW\Type\Command\AArgument', array('test'));

        $this->assertEquals('test', strval($this->Argument), '(strval) IArgument returned an invalid value');
    }

    public function generateArgV()
    {
        $Test1      = array('command file -a argument');
        $Expected   = new GenericContainer(IArgument::CLASSNAME);
        $Expected[] = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\AArgument', array('command'));
        $Expected[] = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\AArgument', array('file'));
        $Expected[] = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\AArgument', array('argument'));
        $Tricky     = new GenericContainer(IArgument::CLASSNAME);

        return array(
             array(array('command', 'file', '-x',           'argument'), array('x'), $Expected)
            ,array(array('command', 'file', '-a',   'foo',  'argument'), array('x'), $Expected)
            ,array(array('command', 'file', '-afoo',        'argument'), array('x'), $Expected)
            ,array(array('command', 'file', '--a',  'foo',  'argument'), array('x'), $Expected)
            ,array(array('command', 'file', '--a=', 'foo',  'argument'), array('x'), $Expected)
            ,array(array('command', 'file', '--a=foo',      'argument'), array('x'), $Expected)
            ,array(new \ArrayObject(array('command', 'file', '--a=foo', 'argument')), array('x'), $Expected)
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

            $this->assertEquals($Expected, $this->Argument->createFromArray($Input, $NoValue), 'IArgument::createFromArray() returned an ivalid value');
        }

        # Invalid input
        try {
            $this->Argument->createFromArray(NULL);
            $this->fail('Failedto generate exception with invalid arguments');
        } catch (InvalidArgumentException $e) {}
    }

    /**
     * @depends test_createFromArray
     * @covers ::createFromString
     */
    public function test_createFromString()
    {
        #Valid values
        $Expected   = new GenericContainer(IArgument::CLASSNAME);
        $Expected[] = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\AArgument', array('command'));
        $Expected[] = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\AArgument', array('file.txt'));
        $Expected[] = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\AArgument', array('foo'));
        $Expected[] = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\AArgument', array('arg1'));
        $Expected[] = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\AArgument', array('arg2'));
        $Expected[] = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\AArgument', array('http://example.com'));
        $Expected[] = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\AArgument', array("\n\n\r\n"));

        $this->assertEquals($Expected, $this->Argument->createFromString(self::COMMANDLINE, array('x')), 'IArgument::createFromString() returned an invalid value');


        $Expected   = new GenericContainer(IArgument::CLASSNAME);
        $Expected[] = $this->getMockForAbstractClass('\\BLW\\Type\\Command\\AArgument', array(__FILE__));

        $this->assertEquals($Expected, $this->Argument->createFromString(new \SplFileInfo(__FILE__)), 'IArgument::createFromString() returned an invalid value');

        # Invalid arguments
        try {
            $this->Argument->createFromString(NULL);
            $this->fail('Failedto generate exception with invalid arguments');
        } catch (InvalidArgumentException $e) {}
    }

    /**
     * @depends test_construct
     * @covers ::__get
     */
    public function test_get()
    {
        #Value
        $this->assertSame('test argument', $this->Argument->Value, 'IArgument::$Value should be `foo`');

        # Undefined
        try {
            $this->Argument->undefined;
            $this->fail('Failed to generate notice with undefined property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {}

        $this->assertNull(@$this->Argument->undefined, 'IArgument::__get() Should return NULL for undefined value');
    }


    /**
     * @depends test_construct
     * @covers ::__isset
     */
    public function test_isset()
    {
        # Value
        $this->assertSame($this->readAttribute($this->Argument, '_Value') !== null, isset($this->Argument->Value), 'IArgument::$Value should exist');

        # Undefined
        $this->assertFalse(isset($this->Argument->undefined), 'IArgument::$undefined should not exist');
    }

    /**
     * @depends test_construct
     * @covers ::__set
     */
    public function test_set()
    {
        # Value
        try {
            $this->Argument->Value = '';
            $this->fail('Failed to generate notice with readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Argument->Value = '';

        # Undefined
        try {
            $this->Argument->undefined = '';
            $this->fail('Failed to generate notice with undefined property');
        } catch (\PHPUnit_Framework_Error $e) {
            $this->assertContains('non-existant property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Argument->undefined = '';
    }

    /**
     * @depends test_construct
     * @covers ::__unset
     */
    public function test_unset()
    {
        # Value
        try {
            unset($this->Argument->Value);
            $this->fail('Failed to generate notice with readonly property');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertContains('Cannot modify readonly property', $e->getMessage(), 'Invalid notice: '. $e->getMessage());
        }

        @$this->Argument->__unset('Value');

        # Undefined
        unset($this->Argument->undefined);
    }
}
