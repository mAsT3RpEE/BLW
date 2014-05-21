<?php
/**
 * LogicExceptionTest.php | May 12, 2014
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
namespace BLW\Type;

use Exception;

class MockLogicException1089 extends \BLW\Type\ALogicException {}

/**
 * Tests BLW Libraries logic exception
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\ALogicException
 */
class LogicExceptionTest  extends \PHPUnit_Framework_TestCase
{
    /**
     * @var resource
     */
    protected $fp = NULL;

    /**
     * @var \BLW\Type\ALogicException
     */
    protected $Exception = NULL;

    protected function setUp()
    {
        $this->fp = fopen(__FILE__, 'r');
        $throw    = function () {
            throw new MockLogicException1089('Test String', -1, new Exception('Previous'));
        };

        try {
            $throw(1, 1.5, 'string', array(), new \stdClass(), $this->fp, str_repeat('a', 512));
        } catch (\Exception $e) {
            $this->Exception = $e;
        }
    }

    protected function tearDown()
    {
        $this->Exception = NULL;
        $this->fp        = NULL;
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        $Exception = new MockLogicException1089('Test String', -1);

        $this->assertSame(-1, $Exception->getCode(), 'LogicException::getCode() Returned an invalid value');
        $this->assertFileExists($Exception->getFile(), 'LogicException::getFile() Returned an invalid value');
        $this->assertGreaterThan(1, $Exception->getLine(), 'LogicException::getLine() Returned an invalid value');
        $this->assertSame('Test String', $Exception->getMessage(), 'LogicException::getMessage() Returned an invalid value');
        $this->assertNull($Exception->getPrevious(), 'LogicException::getPrevious() Returned an invalid value');
        $this->assertContains('BLW\Type\LogicExceptionTest->test_construct()', $Exception->getTraceAsString(), 'LogicException::getTraceAsString() Returned an invalid value');
    }

    /**
     * @covers ::getFields
     */
    public function test_getFields()
    {
        $Fields = $this->Exception->getFields();

        $this->assertSame('%class%::%func%(%args%):', $Fields['%header%'], 'LogicException::getFields() Returned an invalid value');
        $this->assertSame("1, 1.5, 'string', array, stdClass, resource, 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa...aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'", $Fields['%args%'], 'LogicException::getFields() Returned an invalid value');
        $this->assertContains('closure', $Fields['%func%'], 'LogicException::getFields() Returned an invalid value');
        $this->assertContains(__FILE__, $Fields['%caused%'], 'LogicException::getFields() Returned an invalid value');
    }
}
