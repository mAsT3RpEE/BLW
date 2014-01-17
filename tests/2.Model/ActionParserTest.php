<?php
/**
 * ActionParser Test.php | Jan 14, 2014
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
namespace BLW\Tests\Model;

use BLW\Model\ActionParser;

/**
 * Tests ActionParser Module type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class ActionParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\ActionParser
     */
    private $ActionParser = NULL;

    public function setUp()
    {
        $this->ActionParser = ActionParser::GetInstance();
    }

    public function tearDown()
    {
        $this->ActionParser = NULL;

        unset(
             $_GET[ActionParser::ACTION]
            ,$_GET[ActionParser::OBJECT]
            ,$_POST[ActionParser::ACTION]
            ,$_POST[ActionParser::OBJECT]
        );
    }

    public function test_ParseActions()
    {
        $this->assertEquals(0, count($this->ActionParser));

        $_GET[ActionParser::ACTION] = 'foo';
        $_GET[ActionParser::OBJECT] = 'test';

        $_POST[ActionParser::ACTION] = 'foo';
        $_POST[ActionParser::OBJECT] = 'test';

        $this->ActionParser->ParseActions();

        $this->assertEquals(2, count($this->ActionParser));

        foreach ($this->ActionParser as $Action) {
            $this->assertEquals('Action.foo', $Action->Name);
            $this->assertEquals('test',       $Action->Object);
        }

        $_GET[ActionParser::ACTION] = 'bar';
        $_GET[ActionParser::OBJECT] = array('test','test','test');

        $_POST[ActionParser::ACTION] = 'bar';
        $_POST[ActionParser::OBJECT] = array('test','test','test');

        $this->ActionParser->ParseActions();

        $this->assertEquals(2, count($this->ActionParser));

        foreach ($this->ActionParser as $Action) {
            $this->assertEquals('Action.bar', $Action->Name);
            $this->assertEquals('test',       $Action->Object);
        }
    }

    /**
     * @depends test_ParseActions
     */
    public function test_serialize()
    {
        $Serialized = unserialize(serialize($this->ActionParser));

        $this->assertEquals($this->ActionParser, $Serialized);
    }
}