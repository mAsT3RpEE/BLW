<?php
/**
 * Compiler.php | May 3, 2014
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
namespace BLW\Model;

use ReflectionProperty;
use ReflectionMethod;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

use BLW\Model\GenericFile;
use BLW\Model\Compiler;
use BLW\Model\InvalidArgumentException;
use BLW\Model\FileException;
use BLW\Model\Mediator\Symfony as Mediator;


/**
 * Test for BLW Compiler
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Compiler
 */
class CompilerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\Compiler
     */
    protected $Compiler = NULL;

    protected function setUp()
    {
        $Temp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'foo';

        @mkdir($Temp);

        $this->Compiler = new Compiler(
	       new GenericFile($Temp),
           new GenericFile(dirname(dirname(__DIR__))),
           null,
           new Mediator
        );
    }

    protected function tearDown()
    {
        $Temp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'foo';

        $this->Compiler = NULL;

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($Temp), RecursiveIteratorIterator::SELF_FIRST) as $File) {
            usleep(100);
            @unlink($File);
        }

        @rmdir($Temp);
    }

    public function generateInvalidArgs()
    {
        return array(
        	array(NULL, NULL, NULL),
            array(sys_get_temp_dir(), sys_get_temp_dir(), sys_get_temp_dir()),
            array(array(), new GenericFile(sys_get_temp_dir()), new GenericFile(sys_get_temp_dir())),
            array(new GenericFile(sys_get_temp_dir()), array(), new GenericFile(sys_get_temp_dir())),
            array(new GenericFile(sys_get_temp_dir()), new GenericFile(sys_get_temp_dir()), array()),
            array(new GenericFile(__FILE__), new GenericFile(sys_get_temp_dir()), new GenericFile(sys_get_temp_dir())),
            array(new GenericFile(sys_get_temp_dir()), new GenericFile(__FILE__), new GenericFile(sys_get_temp_dir())),
            array(new GenericFile(sys_get_temp_dir()), new GenericFile(sys_get_temp_dir()), new GenericFile(__FILE__)),
            array(new GenericFile('z:\\undefined\\'), new GenericFile(sys_get_temp_dir()), new GenericFile(sys_get_temp_dir())),
            array(new GenericFile(sys_get_temp_dir()), new GenericFile('z:\\undefined\\'), new GenericFile(sys_get_temp_dir())),
            array(new GenericFile(sys_get_temp_dir()), new GenericFile(sys_get_temp_dir()), new GenericFile('z:\\undefined\\')),
        );
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        $Build    = new GenericFile(__DIR__);
        $Root     = new GenericFile(getcwd());
        $Temp     = new GenericFile(sys_get_temp_dir());
        $Compiler = new Compiler($Build);

        // Check properties
        $Property = new ReflectionProperty($Compiler, '_Build');

        $Property->setAccessible(true);
        $this->assertSame($Build, $Property->getValue($Compiler), 'Compiler::__construct() Failed to set $_Build');

        $Property = new ReflectionProperty($Compiler, '_Root');

        $Property->setAccessible(true);
        $this->assertEquals($Root, $Property->getValue($Compiler), 'Compiler::__construct() Failed to set $_Root');

        $Property = new ReflectionProperty($Compiler, '_Temp');

        $Property->setAccessible(true);
        $this->assertEquals($Temp, $Property->getValue($Compiler), 'Compiler::__construct() Failed to set $_Temp');

        // Invalid arguments
        foreach ($this->generateInvalidArgs() as $Arguments) {

            list ($Build, $Root, $Temp) = $Arguments;

            try {
                $Compiler = new Compiler($Build, $Root, $Temp);
                $this->fail('Failed to generate error with invalid arguments');
            }

            catch (InvalidArgumentException $e) {}

            catch (\PHPUnit_Framework_Error $e) {}
        }
    }

    /**
     * @depends test_construct
     * @covers ::doAdvance
     */
    public function test_doAdvance()
    {
        # Set up function.
        $Called    = 0;
        $Arguments = array();

        $this->Compiler->_on('Advance', function() use (&$Called, &$Arguments) {
            $Called++;
            $Arguments = func_get_args();
        });

        # Valid arguments
        $this->Compiler->doAdvance(-1);
        $this->assertEquals(1, $Called, 'Compiler::doAdvance() Failed to trigger callback');
        $this->assertNotEmpty($Arguments, 'Compiler::doAdvance() Caused an exceptional behaviour');
        $this->assertInstanceOf('\\BLW\\Type\\IEvent', $Arguments[0], 'Compiler::doAdvance() Created and invalid event');
        $this->assertEquals(-1, $Arguments[0]->Steps, 'Compiler::doAdvance() Created and invalid event');

        # Invalid arguments
        try {
            $this->Compiler->doAdvance(NULL);
            $this->fail('Failed to generate exception with invalid arguments');
        }

        catch (InvalidArgumentException $e) {}

        # No mediator
        $this->Compiler->clearMediator();
        $this->Compiler->doAdvance(- 1);
    }

    /**
     * @depend test_doAdvance
     * @covers ::onAdvance
     */
    public function test_onAdvance()
    {
        // Set up monitors
        $Called = 0;
        $Steps  = 0;

        // Valid arguments
        $this->Compiler->onAdvance(function ($Event) use(&$Called, &$Steps)
        {
            $Called ++;
            $Steps = $Event->Steps;
        });

        $this->Compiler->doAdvance(-1);

        $this->assertEquals(1, $Called, 'Compiler::onAdvance() Failed to register callback');
        $this->assertEquals(-1, $Steps, 'Compiler::onAdvance() Failed to register callback');

        // Invalid arguments
        try {
            $this->Compiler->onAdvance(NULL);
            $this->fail('Failed to generate exception with invalid arguments');
        }

        catch (InvalidArgumentException $e) {}

        // No mediator
        $this->Compiler->clearMediator();

        $this->Compiler->onAdvance(function(){});
        $this->assertEquals(1, $Called, 'Compiler::onAdvance() Failed to register callback');
    }

    public function generateOptimizations()
    {
        $Config = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR;

        return array(
        	array(__FILE__, 0.75),
            array("{$Config}jquery.fancybox.js", 0.75),
        	array("{$Config}style.css", 0.85),
        );
    }

    /**
     * @depends test_construct
     * @covers ::optimize
     */
    public function test_optimize()
    {
        // Valid arguments
        foreach($this->generateOptimizations() as $Arguments) {

            list ($Input, $Expected) = $Arguments;

            $Actual    = strlen(file_get_contents($Input));
            $Optimized = $this->Compiler->optimize($Input);

            $this->assertNotEmpty($Optimized, 'Compiler::optimize() Returned an emtpy string');
            $this->assertLessThan($Expected, (float) strlen($Optimized) / $Actual, 'Compiler::optimmize() Failed to optimize file: ' . $Input);
        }

        // Invalid arguments
        try {
        	$this->Compiler->optimize(NULL);
        	$this->fail('Failed generating exception with invalid arguments');
        }

        catch (FileException $e) {}

        // Unoptimmizable file
        $this->Compiler->optimize(BLW_DIR . DIRECTORY_SEPARATOR . 'LICENSE.md');
    }

    /**
     * @depends test_construct
     * @covers ::addFile
     */
    public function test_addFile()
    {
        $getFiles = function ($Compiler)
        {
            $Property = new ReflectionProperty($Compiler, '_Files');

            $Property->setAccessible(true);

            return $Property->getValue($Compiler);
        };

        // Valid arguments
        $this->Compiler->addFile(new GenericFile(__FILE__));

        $Files = $getFiles($this->Compiler);

        $this->assertCount(1, $Files, 'Compiler::addFile() Failed to update $_Files');
        $this->assertInstanceOf('\\BLW\\Type\\IFile', $Files[0], 'Compiler::addFile() Has corrupted $_Files');
        $this->assertEquals(__FILE__, strval($Files[0]), 'Compiler::addFile() Failed to add file to $_Files');

        // Invalid Arguments
        try {
            $this->Compiler->addFile(new GenericFile(__DIR__));
            $this->fail('Failed generating exception with invalid arguments');
        }

        catch (FileException $e) {}
    }

    public function getInvalidDirs()
    {
        $Dir = dirname(dirname(__DIR__)) . '/vendor/mrclay';
        $Dir = new GenericFile($Dir);

        return array(
            array(NULL, 'txt'),
            array(array(), 'txt'),
            array(new GenericFile(__FILE__), 'txt'),
            array(new GenericFile('z:\\invalid\\\undefined\\'), 'txt'),
            array($Dir, 'in valid'),
            array($Dir, 'in?valid'),
            array($Dir, 'in"valid'),
            array($Dir, 'in<valid'),
            array($Dir, 'in>valid'),
            array($Dir, 'in\\valid'),
            array($Dir, 'in//valid'),
        );
    }

    /**
     * @depends test_construct
     * @covers ::addDir
     * @covers ::_extractExctentionRegex
     */
    public function test_addDir()
    {
        $getFiles = function ($Compiler)
        {
            $Property = new ReflectionProperty($Compiler, '_Files');

            $Property->setAccessible(true);

            return $Property->getValue($Compiler);
        };

        // Valid arguments
        $Dir = dirname(dirname(__DIR__)) . '/vendor/mrclay';
        $Dir = new GenericFile($Dir);

        $this->assertTrue($this->Compiler->addDir($Dir, 'php', 'txt', 'js', 'css', 'tar.gz'), 'Compiler::addFiles() Returned an invalid value');

        $Files = $getFiles($this->Compiler);

        $this->assertGreaterThan(20, count($Files), 'Compiler::addFiles() Added too few files');

        foreach ($Files as $File) {

            $Test = $File->getExtension() == 'php' ||
                    $File->getExtension() == 'txt' ||
                    $File->getExtension() == 'js'  ||
                    $File->getExtension() == 'css';

            $this->assertTrue($Test, 'Compiler::addFiles Added an illegal file');
        }

        // Excluded items
        $Old = count($Files);

        $this->assertFalse($this->Compiler->addDir(new GenericFile(dirname(dirname(__DIR__)) . '/tests'), 'php', 'txt', 'js', 'css', 'tar.gz'), 'Compiler::addFiles() should return FALSE');
        $this->assertSame($Old, count($getFiles($this->Compiler)));

        // Invalid arguments
        foreach ($this->getInvalidDirs() as $Arguments) {

            list($Dir, $Ext) = $Arguments;

            try {
                $this->Compiler->addDir($Dir, $Ext);
                $this->fail('Failed to generate message with invalid arguments');
            }

            catch (FileException $e) {}

            catch (InvalidArgumentException $e) {}

            catch (\PHPUnit_Framework_Error $e) {}
        }
    }

    /**
     * @depends test_addFile
     * @depends test_addDir
     * @covers ::compile
     */
    public function test_compile()
    {
        $Dir = dirname(dirname(__DIR__)) . '/vendor/mrclay';
        $Dir = new GenericFile($Dir);

        // Valid arguments
        $this->assertTrue($this->Compiler->addDir($Dir, 'php', 'txt', 'js', 'css'), 'Compiler::addFiles() Returned an invalid value');
        $this->assertTrue($this->Compiler->compile('BLW'), 'Compiler::compile() Returned an invalid value');

        // Invalid Project
        try {
            $this->Compiler->compile(NULL);
            $this->fail('Failed to generate exception with invalid arguments');
        }

        catch(InvalidArgumentException $e) {}
    }
}
