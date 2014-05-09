<?php
/**
 * GenericTest.php | Apr 10, 2014
 *
 * @filesource
 * @license MIT
 * @copyright Copyright (c) 2013-2018, mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * @package BLW\HTTP
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model\HTTP\Request;

use ReflectionProperty;
use ReflectionMethod;

use BLW\Type\IDataMapper;
use BLW\Type\HTTP\IRequest;

use BLW\Model\Config\Generic as GenericConfig;
use BLW\Model\HTTP\Request\Generic as Request;


/**
 * Test for BLW Request base class
 * @package BLW\HTTP
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\HTTP\ARequest
 */
class GenericTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\IURI
     */
    protected $URI = NULL;

    /**
     * @var \BLW\Type\HTTP\ARequest
     */
    protected $Request = NULL;

    protected function setUp()
    {
        $this->URI     = $this->getMockForAbstractClass('\BLW\Type\AURI', array('http://example.com/'));
        $this->Request = new Request;
    }

    protected function tearDown()
    {
        $this->Request = NULL;
        $this->URI     = NULL;
    }

    /**
     * @coversNothing
     */
    public function test_construct()
    {
        $this->assertInstanceof('\\BLW\\Type\\HTTP\\IRequest', new Request, 'IRequest::__construct() Failed to return an instance of IRequest');
    }
}