<?php
/**
 * EventTest.php | Apr 1, 2014
 *
 * @filesource
 * @license MIT
 * @copyright Copyright (c) 2013-2018, mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * @package BLW\Command
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model\Command;

use stdClass;
use BLW\Type\Command\ICommand;


/**
 * Test for BLW command event
 * @package BLW\Command
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\Command\Event
 */
class EventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        $Subject = new stdClass;
        $Params  = array('foo' => 1);
        $Event   = new Event($Subject, ICommand::SHUTDOWN,  $Params);

        # Check properties
        $this->assertAttributeSame($Subject, '_Subject', $Event, 'IEvent::__construct() Failed to set $_Subject');
        $this->assertAttributeInternalType('array', '_Context', $Event, 'IEvent::__construct() Failed to set $_Event');
        $Context = $this->readAttribute($Event, '_Context');
        $this->assertArrayHasKey('foo', $Context, 'IEvent::__construct() Failed to set $_Event');
        $this->assertSame(ICommand::SHUTDOWN, $Context['Type'], 'IEvent::__construct() Failed to set $_Event');

        # Invalid arguments
    }
}
