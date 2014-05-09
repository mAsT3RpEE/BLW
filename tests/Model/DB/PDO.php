<?php
/**
 * PDO.php | May 21, 2014
 *
 * @filesource
 * @license MIT
 * @copyright Copyright (c) 2013-2018, mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * @package BLW\DB
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model\DB;

use ReflectionProperty;
use ReflectionMethod;


/**
 * Tests BLW PDO Database connection.
 * @package BLW\DB
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @coversDefaultClass \BLW\Model\DB\PDO
 */
class PDOTest  extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PDO
     */
    protected $PDO = NULL;

    /**
     * @var \BLW\Model\DB\PDO
     */
    protected $DB = NULL;

    /**
     * @var array
     */
    protected $Data = array();

    /**
     * @var string
     */
    protected $SQL = '';

    protected function setUp()
    {
        $this->PDO = new \PDO('sqlite::memory:');
        $this->DB  = new PDO($this->PDO);

        $this->PDO->exec(<<<EOSQL
CREATE TABLE messages (
  id INTEGER PRIMARY KEY,
  title TEXT,
  message TEXT,
  time TEXT)
EOSQL
        );

        $this->Data = array(
            array('title' => 'Hello!', 'message' => 'Just testing...', 'time' => 1327301464),
            array('title' => 'Hello again!', 'message' => 'More testing...', 'time' => 1339428612),
            array('title' => 'Hi!', 'message' => 'SQLite3 is cool...', 'time' => 1327214268)
        );

        $this->SQL = "INSERT INTO `messages` (`title`, `message`, `time`) VALUES (:title, :message, :time)";
    }

    protected function tearDown()
    {
        $this->DB  = NULL;
        $this->PDO = NULL;
    }

    /**
     * @covers ::__construct
     */
    public function test_construct()
    {
        # Insert
        $Query = $this->DB->query($this->SQL);

        foreach($this->Data as $Data) {
            $this->assertInstanceOf('Zend\Db\Adapter\Driver\ResultInterface', $Query->execute($Data), 'PDO::__construct() Failed to pass PDO object');
        }

        # Select
        $Query  = $this->DB->query('SELECT * FROM `messages`');
        $Result = $Query->execute();

        $this->assertInstanceOf('Zend\Db\Adapter\Driver\ResultInterface', $Result, 'PDO::__construct() Failed to pass PDO object');

        $this->assertSame(3, $Result->count(), 'PDO::__construct() Failed to pass PDO object');

        # Update

        // TODO Add update test

        # Delete

        // TODO Add delete test

        # Drop

        // TODO Add drop test
    }
}