<?php
/**
 * YAMLTest.php | Apr 27, 2014
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
namespace BLW\Model\Config;

use BLW\Model\GenericFile;
use BLW\Model\Config\YAML as Config;
use BLW\Model\FileException;


/**
 * Tests BLW Library YAML configuration
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Config\YAML
 */
class YAMLTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\IFile
     */
    protected $File = null;

    /**
     * @var \BLW\Model\Config\YAML
     */
    protected $Config = null;

    protected function setUp()
    {
        $File         = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'test.yml';
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

        $this->assertTrue($Config->offsetExists('owner'), 'YAML::__construct() Failed to parse sections');
        $this->assertTrue($Config->offsetExists('database'), 'YAML::__construct() Failed to parse sections');

        $this->assertArrayHasKey('name', $Config['owner'], 'YAML::__construct() Failed to parse sections');
        $this->assertArrayHasKey('organization', $Config['owner'], 'YAML::__construct() Failed to parse sections');

        $this->assertArrayHasKey('server', $Config['database'], 'YAML::__construct() Failed to parse sections');
        $this->assertArrayHasKey('port', $Config['database'], 'YAML::__construct() Failed to parse sections');
        $this->assertArrayHasKey('file', $Config['database'], 'YAML::__construct() Failed to parse sections');

        # Invalid arguments
        try {
            new Config(new GenericFile('z:\\undefined'));
            $this->fail('Failed to generate error with invalid arguments');
        }

        catch (FileException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $Expected = "owner:\n    name: 'John Doe'\n    organization: 'Acme Widgets Inc.'\ndatabase:\n    server: 192.0.2.62\n    port: 143\n    file: payroll.dat";

        $this->assertStringStartsWith($Expected, strval($this->Config), '(string) YAML Produced an invalid value');
    }
}