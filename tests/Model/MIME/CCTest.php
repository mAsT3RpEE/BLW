<?php
/**
 * CCTest.php | Mar 10, 2014
 *
 * @filesource
 * @license MIT
 * @copyright Copyright (c) 2013-2018, mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * @package BLW\MIME
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Tests\Model\MIME;

use BLW\Model\InvalidArgumentException;
use BLW\Model\GenericContainer;
use BLW\Model\GenericEmailAddress;
use BLW\Model\MIME\CC;


/**
 * Tests BLW Library MIME Contetn-Location header.
 * @package BLW\MIME
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Mime\CC
 */
class CCTest extends \PHPUnit_Framework_TestCase
{
    const ADDR_LIST = 'test foo <test@foo.com>, "test \\"example" <test@example.com>, test@noname.com';

    /**
     * @var \BLW\Type\IContainer
     */
    protected $AddressList = array();

    /**
     * @var \BLW\Model\MIME\CC
     */
    protected $Field = NULL;

    /**
     * @var \ReflectionProperty[]
     */
    protected $Properties = array();

    protected function setUp()
    {
        $this->AddressList   = new GenericContainer;
        $this->AddressList[] = new GenericEmailAddress('test@foo.com', 'test foo');
        $this->AddressList[] = new GenericEmailAddress('test@example.com', '"test \\"example"');
        $this->AddressList[] = new GenericEmailAddress('test@noname.com');
        $this->AddressList[] = new GenericEmailAddress('root');

        $this->Field         = new CC($this->AddressList);
        $this->Properties     = array(
             'Type'  => new \ReflectionProperty($this->Field, '_Type')
        	,'Value' => new \ReflectionProperty($this->Field, '_Value')
        );

        $this->Properties['Type']->setAccessible(true);
        $this->Properties['Value']->setAccessible(true);
    }

    protected function tearDown()
    {
        $this->Properties = NULL;
        $this->Field     = NULL;
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Check params
        $this->assertEquals('Cc', $this->Properties['Type']->getValue($this->Field), 'CC::__construct() failed to set $_Type');
        $this->assertEquals(self::ADDR_LIST, $this->Properties['Value']->getValue($this->Field), 'CC::__construct() failed to set $_Value');

        # Invalid Addres list
        try {
            new CC(new GenericContainer);
            $this->fail('Failed to generate exception with invalid parameters');
        }

        catch (InvalidArgumentException $e) {}
    }

    /**
     * @covers ::__toString
     */
    public function test_toString()
    {
        $this->assertEquals(sprintf("Cc: %s\r\n", self::ADDR_LIST), @strval($this->Field), 'CC::__toSting() returned an invalid format');
    }
}
