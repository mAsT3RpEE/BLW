<?php
class Author extends \BLW\Type\ActiveRecord
{
	static $pk = 'author_id';
	static $has_many = array('books');
	static $has_one = array(
		array('parent_author', 'class_name' => 'Author', 'foreign_key' => 'parent_author_id')
	);
	static $belongs_to = array();

	public function set_password($plaintext)
	{
		$this->encrypted_password = md5($plaintext);
	}

	public function set_name($value)
	{
		$value = strtoupper($value);
		$this->assign_attribute('name',$value);
	}

	public function return_something()
	{
		return array("sharks" => "lasers");
	}
};

class Book extends \BLW\Type\ActiveRecord
{
	static $belongs_to = array('author');
	static $has_one = array();
	static $use_custom_get_name_getter = false;

	public function upper_name()
	{
		return strtoupper($this->name);
	}

	public function name()
	{
		return strtolower($this->name);
	}

	public function get_name()
	{
		if (self::$use_custom_get_name_getter)
			return strtoupper($this->read_attribute('name'));
		else
			return $this->read_attribute('name');
	}

	public function get_upper_name()
	{
		return strtoupper($this->name);
	}

	public function get_lower_name()
	{
		return strtolower($this->name);
	}
};
