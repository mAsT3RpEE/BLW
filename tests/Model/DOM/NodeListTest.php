<?php
/**
 * NodeListTest.php | May 14, 2014
 *
 * @filesource
 * @license MIT
 * @copyright Copyright (c) 2013-2018, mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * @package BLW\DOM
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model\DOM;

use ArrayObject;
use DOMElement;


/**
 * Tests BLW NodeList class
 * @package BLW\DOM
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\DOM\NodeList
 */
class NodeListTest  extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BLW\Model\DOM\NodeList
     */
    protected $List = NULL;

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Valid arguments
        $List = new NodeList(new ArrayObject(array(
            new DOMElement('span', 'foo'),
            new DOMElement('span', 'foo')
        )));

        $this->assertAttributeSame(array('DOMNode'), '_Types', $List, 'NodeList::__construct() Failed to set $_Type');
        $this->assertCount(2, $List, 'NodeList::__construct() Failed to add items');
        $this->assertContainsOnlyInstancesOf('DOMNode', $List, 'NodeList::__construct() Failed to add items');

        # Invalid arguments
    }
}
