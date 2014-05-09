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
namespace BLW\Model\HTTP;

use stdClass;
use BLW\Model\HTTP\Event;


/**
 * Test for BLW command event
 * @package BLW\Command
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\HTTP\Event
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
        $Event   = new Event($Subject, $Params);

        # Check properties
        $this->assertAttributeSame($Subject, '_Subject', $Event, 'IEvent::__construct() Failed to set $_Subject');
        $this->assertAttributeInternalType('array', '_Context', $Event, 'IEvent::__construct() Failed to set $_Context');
        $this->assertSame($Params, $this->readAttribute($Event, '_Context'), 'IEvent::__construct() Failed to set $_Context');

        # Invalid arguments
    }
}
