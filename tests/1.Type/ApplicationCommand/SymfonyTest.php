<?php
/**
 * SymfonyTest.php | Dec 30, 2013
 *
 * Copyright (c) 2013-2018 mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 *
 * @filesource
 * @copyright mAsT3RpEE's Zone
 * @license MIT
 */

/**
 * @package BLW\Core
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Tests\Type\ApplicationCommand;

use BLW;
use ApplicationCommand;
use BLW\Frontend\Console\Symfony as Application;
use Symfony\Component\Console\Tester\CommandTester as Tester;

require_once __DIR__ . '/../../Config/ApplicationCommand/Symfony.php';

/**
 * Tests BLW Library Symfony ApplicationCommand type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class SymfonyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Frontend\Console\Symfony
     */
    private $_Application = NULL;

    /**
     * @var \BLW\Type\ApplicationCommand\Symfony
     */
    private $_Command = NULL;

    /**
     * @var \Symfony\Component\Console\Tester\CommandTester
     */
    private $_Tester = NULL;

    public function setUp()
    {
        $this->_Application = Application::GetInstance();
        $this->_Command     = ApplicationCommand::GetInstance();
        $this->_Application->push($this->_Command);
        $this->_Tester      = new Tester($this->_Command);
    }

    public function tearDown()
    {
        $this->_Application   = NULL;
        $this->_Command       = NULL;
        $this->_Tester        = NULL;
    }

    public function test_Run()
    {
        $this->_Tester->execute(
             array('command' => $this->_Command->getName(), 'test-argument' => 'Argument', '--test-option' => 'Option')
            ,array('verbosity' => 3)
        );

        $Output = $this->_Tester->getDisplay();

        $this->assertContains('10/10 [============================] 100%', $Output);
        $this->assertContains('Argument is: Argument', $Output);
        $this->assertContains('Option is: Option', $Output);
    }

    /**
     * @depends test_Run
     */
    public function test_serialize()
    {
        $Serialized = unserialize(serialize($this->_Command));

        $this->assertEquals($this->_Command, $Serialized);
    }

    /**
     * @depends test_Run
     */
    public function test_GetID()
    {
        $this->assertEquals('test', $this->_Command->GetID());
    }

    /**
     * @depends test_GetID
     */
    public function test_SetID($ID)
    {
        $this->_Command->SetID('foo');
        $this->assertEquals('foo', $this->_Command->GetID());
    }

    /**
     * @depends test_Run
     */
    public function test_SetParent()
    {
        $this->assertNull($this->_Command->GetParent());

        $this->_Command->SetParent(BLW::$Base);

        $this->assertEquals(BLW::$Base, $this->_Command->GetParent());
        $this->assertEquals($this->_Command->GetParent(), $this->_Command->parent());
    }
}