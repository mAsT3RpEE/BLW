<?php
/**
 * Archiver.php | May 3, 2014
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
use BLW\Model\Archiver;
use BLW\Model\InvalidArgumentException;
use BLW\Model\FileException;
use BLW\Model\Mediator\Symfony as Mediator;


/**
 * Test for BLW Archiver
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Archiver
 */
class ArchiverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\Archiver
     */
    protected $Archiver = NULL;

    protected function setUp()
    {
        $Temp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'foo';

        @mkdir($Temp);

        $this->Archiver = new Archiver(
	       new GenericFile($Temp),
           new GenericFile(dirname(dirname(__DIR__))),
           null,
           new Mediator
        );
    }

    protected function tearDown()
    {
        $Temp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'foo';

        $this->Archiver = NULL;

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($Temp), RecursiveIteratorIterator::SELF_FIRST) as $File) {
            usleep(100);
            @unlink($File);
        }

        @rmdir($Temp);
    }

    /**
     * @covers ::compile
     */
    public function test_compile()
    {
        $Dir = dirname(dirname(__DIR__)) . '/vendor/mrclay';
        $Dir = new GenericFile($Dir);

        // Valid arguments
        $this->assertTrue($this->Archiver->addDir($Dir, 'php', 'txt', 'js', 'css'), 'Archiver::addFiles() Returned an invalid value');
        $this->assertTrue($this->Archiver->compile('BLW'), 'Archiver::compile() Returned an invalid value');

        // Invalid Project
        try {
            $this->Archiver->compile(NULL);
            $this->fail('Failed to generate exception with invalid arguments');
        }

        catch(InvalidArgumentException $e) {}
    }
}
