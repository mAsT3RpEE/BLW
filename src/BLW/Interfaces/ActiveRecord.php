<?php
/**
 * ActiveRecord.php | Dec 28, 2013
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
 *	@package BLW\Core
 *	@version 1.0.0
 *	@author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Interfaces;

/**
 * Core ActiveRecord pattern interface.
 *
 * <h4>Notice:</h4>
 *
 * <p>All <code>DAO</code> must either implement this interface or
 * extend the <code>\BLW\Type\ActiveRecord</code> class.
 *
 * <hr>
 * @package BLW\Core
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 */
interface ActiveRecord
{
    /**
     * Retrieves the name of the table for this Model.
     * @return string
     */
    public static function table_name();

	/**
	 * Retrieve the connection for this model.
	 * @return \ActiveRecord\Connection
	 */
	public static function connection();

	/**
	 * Re-establishes the database connection with a new connection.
	 * @return \ActiveRecord\Connection
	 */
	public static function reestablish_connection();

	/**
	 * Returns the <code>ActiveRecord\Table</code> object for this model.
	 * @note Be sure to call in static scoping: static::table()
	 * @return ActiveRecord\Table
	 */
	public static function table();

	/**
	 * Creates a model and saves it to the database.
	 * @param array $attributes Array of the models attributes
	 * @param boolean $validate True if the validators should be run
	 * @return Model
	 */
	public static function create($attributes, $validate=true);


	/**
	 * Save the model to the database.
	 *
	 * <h3>About</h3>
	 *
	 * <p>This function will automatically determine if an INSERT or UPDATE needs to occur.
	 * If a validation or a callback for this model returns false, then the model will
	 * not be saved and this will return false.</p>
	 *
	 * <p>If saving an existing model only data that has changed will be saved.</p>
	 * <hr>
	 * @param boolean $validate Set to true or false depending on if you want the validators to run or not
	 * @return boolean True if the model was saved to the database otherwise false
	 */
	public function save($validate=true);

	/**
	 * Deletes this model from the database and returns true if successful.
	 * @return boolean
	 */
	public function delete();

	/**
	 * Deletes records matching conditions in $options
	 *
	 * <h3>About</h3>
	 *
	 * <p>Does not instantiate models and therefore does not invoke callbacks</p>
	 *
	 * <p>Delete all using a hash:</p>
	 *
	 * <pre><code>
	 * YourModel::delete_all(array('conditions' => array('name' => 'Tito')));
	 * </code></pre>
	 *
	 * <p>Delete all using an array:</p>
	 *
	 * <pre><code>
	 * YourModel::delete_all(array('conditions' => array('name = ?', 'Tito')));
	 * </code></pre>
	 *
	 * <p>Delete all using a string:</p>
	 *
	 * <pre><code>
	 * YourModel::delete_all(array('conditions' => 'name = "Tito"));
	 * </code></pre>
	 *
	 * <p>An options array takes the following parameters:</p>
	 *
	 * <ul>
	 * <li><b>conditions:</b> Conditions using a string/hash/array</li>
	 * <li><b>limit:</b> Limit number of records to delete (MySQL & Sqlite only)</li>
	 * <li><b>order:</b> A SQL fragment for ordering such as: 'name asc', 'id desc, name asc' (MySQL & Sqlite only)</li>
	 * </ul>
	 *
	 * @param array $options See Documentation
	 * return integer Number of rows affected
	 */
	public static function delete_all($options=array());

	/**
	 * Updates records using set in $options
	 *
	 * <h3>About</h3>
	 *
	 * <p>Does not instantiate models and therefore does not invoke callbacks</p>
	 *
	 * <p>Update all using a hash:</p>
	 *
	 * <pre><code>
	 * YourModel::update_all(array('set' => array('name' => "Bob")));
	 * </code></pre>
	 *
	 * <p>Update all using a string:</p>
	 *
	 * <pre><code>
	 * YourModel::update_all(array('set' => 'name = "Bob"'));
	 * </code></pre>
	 *
	 * <p>An options array takes the following parameters:</p>
	 *
	 * <ul>
	 * <li><b>set:</b> String/hash of field names and their values to be updated with
	 * <li><b>conditions:</b> Conditions using a string/hash/array</li>
	 * <li><b>limit:</b> Limit number of records to update (MySQL & Sqlite only)</li>
	 * <li><b>order:</b> A SQL fragment for ordering such as: 'name asc', 'id desc, name asc' (MySQL & Sqlite only)</li>
	 * </ul>
	 *
	 * @param array $options See documentation
	 * return integer Number of rows affected
	 */
	public static function update_all($options=array());

	/**
	 * Get a count of qualifying records.
	 *
	 * <h4>Example:</h4>
	 *
	 * <pre><code>
	 * YourModel::count(array('conditions' => 'amount > 3.14159265'));
	 * </code></pre>
	 * <hr>
	 * @see \ActiveRecord\Model::find()
	 * @param ...
	 * @return int Number of records that matched the query
	 */
	public static function count(/* ... */);

	/**
	 * Determine if a record exists.
	 *
	 * <h4>Example:</h4>
	 *
	 * <pre><code>
	 * SomeModel::exists(123);
	 * SomeModel::exists(array('conditions' => array('id=? and name=?', 123, 'Tito')));
	 * SomeModel::exists(array('id' => 123, 'name' => 'Tito'));
	 * </code></pre>
	 * <hr>
	 * @see \ActiveRecord\Model::find()
	 * @param ...
	 * @return boolean
	 */
	public static function exists(/* ... */);

	/**
	 * Alias for self::find('first').
	 * @see \ActiveRecord\Model::find()
	 * @return Model The first matched record or null if not found
	 */
	public static function first(/* ... */);

	/**
	 * Alias for self::find('last')
	 * @see \ActiveRecord\Model::find()
	 * @return Model The last matched record or null if not found
	 */
	public static function last(/* ... */);

	/**
	 * Find records in the database.
	 *
	 * <h3>About</h3>
	 *
	 * <p>Finding by the primary key:</p>
	 *
	 * <pre><code>
	 * # queries for the model with id=123
	 * YourModel::find(123);
	 *
	 * # queries for model with id in(1,2,3)
	 * YourModel::find(1,2,3);
	 *
	 * # finding by pk accepts an options array
	 * YourModel::find(123,array('order' => 'name desc'));
	 * </code></pre>
	 *
	 * <p>Finding by using a conditions array:</p>
	 *
	 * <pre><code>
	 * YourModel::find('first', array('conditions' => array('name=?','Tito'),
	 *   'order' => 'name asc'))
	 * YourModel::find('all', array('conditions' => 'amount > 3.14159265'));
	 * YourModel::find('all', array('conditions' => array('id in(?)', array(1,2,3))));
	 * </code></pre>
	 *
	 * <p>Finding by using a hash:</p>
	 *
	 * <pre><code>
	 * YourModel::find(array('name' => 'Tito', 'id' => 1));
	 * YourModel::find('first',array('name' => 'Tito', 'id' => 1));
	 * YourModel::find('all',array('name' => 'Tito', 'id' => 1));
	 * </code></pre>
	 *
	 * <p>An options array can take the following parameters:</p>
	 *
	 * <ul>
	 * <li><b>select:</b> A SQL fragment for what fields to return such as: '*', 'people.*', 'first_name, last_name, id'</li>
	 * <li><b>joins:</b> A SQL join fragment such as: 'JOIN roles ON(roles.user_id=user.id)' or a named association on the model</li>
	 * <li><b>include:</b> TODO not implemented yet</li>
	 * <li><b>conditions:</b> A SQL fragment such as: 'id=1', array('id=1'), array('name=? and id=?','Tito',1), array('name IN(?)', array('Tito','Bob')),
	 * array('name' => 'Tito', 'id' => 1)</li>
	 * <li><b>limit:</b> Number of records to limit the query to</li>
	 * <li><b>offset:</b> The row offset to return results from for the query</li>
	 * <li><b>order:</b> A SQL fragment for order such as: 'name asc', 'name asc, id desc'</li>
	 * <li><b>readonly:</b> Return all the models in readonly mode</li>
	 * <li><b>group:</b> A SQL group by fragment</li>
	 * </ul>
	 * <hr>
	 * @throws \ActiveRecord\RecordNotFound if no options are passed or finding by pk and no records matched
	 * @return mixed
	 * <p>An array of records found if doing a find_all otherwise a
	 * single Model object or null if it wasn't found. NULL is only return when
	 * doing a first/last find. If doing an all find and no records matched this
	 * will return an empty array.</p>
	 */
	public static function find(/* $type, $options */);

	/**
	 * Finder method which will find by a single or array of primary keys for this model.
	 * @see \ActiveRecord\Model::find()
	 * @param array $values An array containing values for the pk
	 * @param array $options An options array
	 * @return Model
	 * @throws \ActiveRecord\RecordNotFound if a record could not be found
	 */
	public static function find_by_pk($values, $options);

	/**
	 * Find using a raw SELECT query.
	 *
	 * <h4>Example:</h4>
	 * <pre><code>
	 * YourModel::find_by_sql("SELECT * FROM people WHERE name=?",array('Tito'));
	 * YourModel::find_by_sql("SELECT * FROM people WHERE name='Tito'");
	 * </code></pre>
	 * <hr>
	 * @param string $sql The raw SELECT query
	 * @param array $values An array of values for any parameters that needs to be bound
	 * @return array An array of models
	 */
	public static function find_by_sql($sql, $values=null);

	/**
	 * Helper method to run arbitrary queries against the model's database connection.
	 * @param string $sql SQL to execute
	 * @param array $values Bind values, if any, for the query
	 * @return object A PDOStatement object
	 */
	public static function query($sql, $values=null);

}