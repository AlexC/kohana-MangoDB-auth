<?php defined('SYSPATH') or die('No direct script access.');
/**
 * MangoDB (MongoDB) 'User' model
 *
 * @author     Alex Cartwright <alexc223@gmail.com>
 * @copyright  Copyright (c) 2012, Alex Cartwright
 * @license    BSD 3-Clause License, see LICENSE file
 */
class Model_Mango_User extends Mango {

	protected $_fields = array(
		'username' => array(
			'type'     => 'string',
			'unique'   => TRUE,
			'required' => TRUE,
		),
		'tokens' => array(
			'model' => 'user_token',
			'type'  => 'has_many',
		),
		'roles' => array(
			'type'   => 'set',
			'unique' => TRUE,
		),
		'password' => array(
			'type'     => 'string',
			'required' => TRUE,
			'filters'  => array(
				array(array(':model', 'hash')),
			),
		),
		'email' => array(
			'type'     => 'email',
			'unique'   => TRUE,
			'required' => TRUE,
		),
		'logins' => array(
			'type' => 'counter',
		),
		'last_login' => array(
			'type' => 'int',
		),
	);

	/**
	 * Filter to hash the value using Auth::hash()
	 *
	 * @param   string  $value
	 * @return  string
	 */
	public function hash($value)
	{
		return Auth::instance()->hash($value);
	}

	/**
	 * Checks if the current user has the provided role
	 *
	 * @param   string  $role
	 * @return  boolean
	 */
	public function has_role($role)
	{
		return in_array($role, $this->roles->as_array());
	}

}
