<?php
/**
 * ObjectStorageTest.php | Feb 12, 2014
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

use stdClass;
use PHPUnit_Framework_Error_Notice;
use DOMElement;

/**
 * Tests BLW Library Adaptor type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 *  @coversDefaultClass \BLW\Type\AObjectStorage
 */
class ObjectStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\IObjectStorage
     */
    protected $ObjectStorage = NULL;

    protected function setUp()
    {
        $this->ObjectStorage = $this->getMockForAbstractClass('\\BLW\\Type\\AObjectStorage');
    }

    protected function tearDown()
    {
        $this->ObjectStorage = NULL;
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->ObjectStorage[new stdClass] = 1;
        $this->ObjectStorage[new stdClass] = 1;
        $this->ObjectStorage[new stdClass] = 'test';
        $this->ObjectStorage[new stdClass] = new DOMElement('span', 'test');

        $this->assertEquals('[IObjectStorage:stdClass,stdClass,stdClass,stdClass]', strval($this->ObjectStorage),'(string) IObjectStorage returned an invalid format');
    }

    /**
     * @covers ::getID
     */
    public function test_getID()
    {
        $this->ObjectStorage[new stdClass] = 1;
        $this->ObjectStorage[new stdClass] = 1;
        $this->ObjectStorage[new stdClass] = 'test';
        $this->ObjectStorage[new stdClass] = new DOMElement('span', 'test');

        $this->assertEquals('d49b84b773cb6bc2fe49c6c478fa6b4a', strval($this->ObjectStorage->getID()),'IObjectStorage::getID() returned an invalid value');
    }
}
