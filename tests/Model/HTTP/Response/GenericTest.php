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
namespace BLW\Model\HTTP\Response;


use BLW\Type\HTTP\IResponse;

use BLW\Model\HTTP\Response\Generic as Response;


/**
 * Test for BLW Response base class
 * @package BLW\HTTP
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\HTTP\AResponse
 */
class GenericTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Type\IURI
     */
    protected $URI = NULL;

    /**
     * @var \BLW\Type\HTTP\AResponse
     */
    protected $Response = NULL;

    protected function setUp()
    {
        $this->URI      = $this->getMockForAbstractClass('\BLW\Type\AURI', array('http://example.com/'));
        $this->Response = new Response;
    }

    protected function tearDown()
    {
        $this->Response = NULL;
        $this->URI     = NULL;
    }

    /**
     * @coversNothing
     */
    public function test_construct()
    {
        $this->assertInstanceof('\\BLW\\Type\\HTTP\\IResponse', new Response, 'IResponse::__construct() Failed to return an instance of IResponse');
    }
}
