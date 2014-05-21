<?php
/**
 * GenericEventTest.php | May 15, 2014
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
namespace BLW\Model;



/**
 * Tests BLW Generic event
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\GenericEvent
 */
class GenericEventTest  extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        $Subject = new \stdClass;
        $Params  = array(
            'foo' => 1,
            'bar' => -1,
        );

        $Event = new GenericEvent($Subject, $Params);

        $this->assertAttributeSame($Subject, '_Subject', $Event, 'GenericEvent::__construct() Failed to set $_Subject');
        $this->assertAttributeEquals($Params, '_Context', $Event, 'GenericEvent::__construct() Failed to set $_Context');
    }
}
