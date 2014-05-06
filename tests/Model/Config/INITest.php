<?php
/**
 * INITest.php | Apr 27, 2014
 *
 * @filesource
 * @license MIT
 * @copyright Copyright (c) 2013-2018, mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 *
 * @package BLW\Core
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Tests\Model\Config;

use ReflectionProperty;
use ReflectionMethod;

use BLW\Model\GenericFile;
use BLW\Model\Config\INI as Config;

/**
 * Tests BLW Library INI configuration
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Config\INI
 */
class INITest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\IFile
     */
    protected $File = null;

    /**
     * @var \BLW\Model\Config\INI
     */
    protected $Config = null;

    protected function setUp()
    {
        $File         = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'test.ini';
        $this->File   = new GenericFile($File);
        $this->Config = new Config($this->File);
    }

    protected function tearDown()
    {
        $this->Config = null;
        $this->File   = null;
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Valid arguments
        $Config = new Config($this->File);

        $this->assertTrue($Config->offsetExists('owner'), 'INI::__construct() Failed to parse sections');
        $this->assertTrue($Config->offsetExists('database'), 'INI::__construct() Failed to parse sections');

        $this->assertArrayHasKey('name', $Config['owner'], 'INI::__construct() Failed to parse sections');
        $this->assertArrayHasKey('organization', $Config['owner'], 'INI::__construct() Failed to parse sections');

        $this->assertArrayHasKey('server', $Config['database'], 'INI::__construct() Failed to parse sections');
        $this->assertArrayHasKey('port', $Config['database'], 'INI::__construct() Failed to parse sections');
        $this->assertArrayHasKey('file', $Config['database'], 'INI::__construct() Failed to parse sections');
    }
}