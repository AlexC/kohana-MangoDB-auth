<?php defined('SYSPATH') or die('No direct script access.');
/**
 * MangoDB (MongoDB) 'User_Token' model that gets embedded into the
 * 'User' model.
 *
 * @author     Alex Cartwright <alexc223@gmail.com>
 * @copyright  Copyright (c) 2012 Alex Cartwright
 * @license    BSD 3-Clause License, see LICENSE file
 */
class Model_Mango_User_Token extends Mango {

	protected $_embedded = TRUE;

	protected $_fields = array(
		'token' => array(
			'type'     => 'string',
			'required' => TRUE,
		),
		'user_agent' => array(
			'type'     => 'string',
			'required' => TRUE,
		),
		'expires' => array(
			'type'     => 'int',
			'required' => TRUE,
		),
	);

}
