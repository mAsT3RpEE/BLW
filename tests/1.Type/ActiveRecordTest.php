<?php
/**
 * ActiveRecord.php | Dec 30, 2013
 *
 * Copyright (c) mAsT3RpEE's Zone
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
namespace BLW\Tests\Type;

use BLW\Interfaces\Object as ObjectInterface;
use BLW\Model\Object;

require_once __DIR__ . '/../Config/ActiveRecord.php';

/**
 * Tests BLW Library ActiveRecord type.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class ActiveRecordTest extends \PHPUnit_Framework_TestCase
{
    const AUTHOR_NAME1 = 'Test Author';
    const AUTHOR_NAME2 = 'Test Child';
    const AUTHOR_NAME3 = 'Test Author Modified';

    const BOOK_NAME1 = 'Test Book1';
    const BOOK_NAME2 = 'Test Book2';
    const BOOK_NAME3 = 'Test Book3';

    public function test_create()
    {
        $Time           = sprintf('@%d', time() - time()%3600 - 3600);
        $DAO            = new \Author();
        $DAO->name      = self::AUTHOR_NAME1;
        $DAO->some_date = new \DateTime('yesterday');
        $DAO->some_time = new \DateTime($Time);
        $DAO->some_text = 'Some text';
        $DAO->password  = 'foo';
        $DAO->save();

        $DAO            = new \Author();
        $DAO->name      = self::AUTHOR_NAME2;
        $DAO->some_date = new \DateTime('yesterday');
        $DAO->some_time = new \DateTime($Time);
        $DAO->some_text = 'Some text';
        $DAO->password  = 'foo';
        $DAO->save();

        $DAO            = new \Book();
        $DAO->name      = self::BOOK_NAME1;
        $DAO->author_id = 1;
        $DAO->save();

        $DAO            = new \Book();
        $DAO->name      = self::BOOK_NAME2;
        $DAO->author_id = 1;
        $DAO->save();

        $DAO            = new \Book();
        $DAO->name      = self::BOOK_NAME3;
        $DAO->author_id = 1;
        $DAO->save();
    }

    /**
     * @depends test_create
     */
    public function test_serialize()
    {
        $DAO        = \Author::find(1);
        $Serialized = unserialize(serialize($DAO));
        $this->assertEquals($DAO, $Serialized);
        $Serialized->save();
    }

    /**
     * @depends test_create
     */
    public function test_find()
    {
        $DAO  = \Author::find(1);
        $Date = new \DateTime('yesterday');
        $Time = date('Y-m-d H:i:s', time() - time()%3600 - 3600);
        $this->assertEquals(strtoupper(self::AUTHOR_NAME1), $DAO->name);
        $this->assertInstanceof('\\Activerecord\\Datetime', $DAO->some_date);
        $this->assertEquals($Date->getTimestamp(), $DAO->some_date->getTimestamp());
        $this->assertEquals($Time, $DAO->some_time);
        $this->assertEquals('Some text', $DAO->some_text);
        $this->assertEquals(md5('foo'), $DAO->encrypted_password);

        $DAO = current(\Author::find_by_sql('SELECT * FROM authors LIMIT 1'));
        $Date = new \DateTime('yesterday');
        $Time = date('Y-m-d H:i:s', time() - time()%3600 - 3600);
        $this->assertEquals(strtoupper(self::AUTHOR_NAME1), $DAO->name);
        $this->assertInstanceof('\\Activerecord\\Datetime', $DAO->some_date);
        $this->assertEquals($Date->getTimestamp(), $DAO->some_date->getTimestamp());
        $this->assertEquals($Time, $DAO->some_time);
        $this->assertEquals('Some text', $DAO->some_text);
    }

    /**
     * @depends test_find
     */
    public function test_update()
    {
        $Date = new \DateTime('tomorrow');

        \Author::update_all(array(
        	'set'          => array('name' => self::AUTHOR_NAME3)
            ,'conditions'  => array(1)
        ));

        $DAO  = \Author::find(1);
        $this->assertEquals(self::AUTHOR_NAME3, $DAO->name);

        $DAO            = \Author::find(2);
        $DAO->name      = self::AUTHOR_NAME3;
        $DAO->some_date = $Date;
        $DAO->save();

        $DAO            = \Author::find(2);
        $this->assertEquals(strtoupper(self::AUTHOR_NAME3), $DAO->name);
        $this->assertInstanceof('\\Activerecord\\Datetime', $DAO->some_date);
        $this->assertEquals($Date->getTimestamp(), $DAO->some_date->getTimestamp());
    }

	/**
	 * @depends test_find
	 */
	public function test_delete()
	{
        $this->assertEquals(2, \Author::count());

        $DAO = \Author::find(2);
        $DAO->delete();

        $this->assertEquals(1, \Author::count());

        \Author::delete_all(array(
        	'conditions' => array()
        ));

        $this->assertEquals(0, \Author::count());
	}
}