<?php
/**
 * RuntimeExceptionTest.php | May 12, 2014
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

class MockRuntimeException1089 extends \BLW\Type\ARuntimeException {}

/**
 * Tests BLW Libraries Runtime exception
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Type\ARuntimeException
 */
class RuntimeExceptionTest  extends \PHPUnit_Framework_TestCase
{
    /**
     * @var resource
     */
    protected $fp = NULL;

    /**
     * @var \BLW\Type\ARuntimeException
     */
    protected $Exception = NULL;

    protected function setUp()
    {
        $this->fp = fopen(__FILE__, 'r');
        $throw    = function () {
            throw new MockRuntimeException1089('Test String', -1, new Exception('Previous'));
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
        $Exception = new MockRuntimeException1089('Test String', -1);

        $this->assertSame(-1, $Exception->getCode(), 'RuntimeException::getCode() Returned an invalid value');
        $this->assertFileExists($Exception->getFile(), 'RuntimeException::getFile() Returned an invalid value');
        $this->assertGreaterThan(1, $Exception->getLine(), 'RuntimeException::getLine() Returned an invalid value');
        $this->assertSame('Test String', $Exception->getMessage(), 'RuntimeException::getMessage() Returned an invalid value');
        $this->assertNull($Exception->getPrevious(), 'RuntimeException::getPrevious() Returned an invalid value');
        $this->assertContains('BLW\Type\RuntimeExceptionTest->test_construct()', $Exception->getTraceAsString(), 'RuntimeException::getTraceAsString() Returned an invalid value');
    }

    /**
     * @covers ::getFields
     */
    public function test_getFields()
    {
        $Fields = $this->Exception->getFields();

        $this->assertSame('%class%::%func%(%args%):', $Fields['%header%'], 'RuntimeException::getFields() Returned an invalid value');
        $this->assertSame("1, 1.5, 'string', array, stdClass, resource, 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa...aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'", $Fields['%args%'], 'RuntimeException::getFields() Returned an invalid value');
        $this->assertContains('closure', $Fields['%func%'], 'RuntimeException::getFields() Returned an invalid value');
        $this->assertContains(__FILE__, $Fields['%caused%'], 'RuntimeException::getFields() Returned an invalid value');
    }
}
