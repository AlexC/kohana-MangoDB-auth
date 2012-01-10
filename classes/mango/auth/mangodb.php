<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana MangoDB (MongoDB) Auth driver
 *
 * @author     Alex Cartwright <alexc223@gmail.com>
 * @copyright  Copyright (c) 2012, Alex Cartwright
 * @license    BSD 3-Clause License, see LICENSE file
 */
class Mango_Auth_Mangodb extends Auth {

	/**
	 * Compares password with original (hashed) of the current user
	 *
	 * @param   string  $password
	 * @return  boolean
	 */
	public function check_password($password)
	{
		$user = $this->get_user();
		if ( ! $user)
			return FALSE;

		return ($this->hash($password) === $user->password);
	}

	/**
	 * Gets the stored password for a given user
	 *
	 * @param   mixed  $user  Username string or MangoDB object
	 * @return  string
	 */
	public function password($user)
	{
		if ( ! ($user instanceof Mango))
		{
			$user = Mango::factory('user', array(
				'username' => $user
			))->load();
		}

		return $user->password;
	}

	/**
	 * Gets the currently logged in user from the session (with auto
	 * login checks).
	 *
	 * Returns NULL if no user is logged in
	 *
	 * @param   mixed  $default
	 * @return  mixed
	 */
	public function get_user($default = NULL)
	{
		$user = parent::get_user($default);
		if ( ! $user)
		{
			// Check for auto login cookie
			$token = Cookie::get('authautologin');
			if ($token)
			{
				$user = Mango::factory('user')->load(array(
					'criteria' => array(
						'tokens.token'      => $token,
						'tokens.expires'    => array('$gt' => time()),
						'tokens.user_agent' => sha1(Request::$user_agent),
					),
				));

				if ($user->loaded())
				{
					// Token is valid, recreate a unique value
					foreach ($user->tokens as $user_token)
					{
						if ($user_token->token === $token)
						{
							$user_token->token = sha1(uniqid(Text::random('alnum', 32), TRUE));
							Cookie::set('authautologin', $user_token->token, $user_token->expires - time());
							break;
						}
					}

					$user->update();
				}
				else
				{
					// Token is not valid, we can not auto login
					$user = $default;
				}
			}
		}

		return $user;
	}

	/**
	 * Logs a user in
	 *
	 * @param   mixed    $user      Username string or MongoDB object
	 * @param   string   $password
	 * @param   boolean  $remember
	 * @return  boolean
	 */
	protected function _login($user, $password, $remember)
	{
		if ( ! ($user instanceof Mango))
		{
			$user = Mango::factory('user', array(
				(Valid::email($user) ? 'email' : 'username') => $user,
			))->load();
		}

		$password = $this->hash($password);

		if ($user->password === $password)
		{
			$user->logins->increment();
			$user->last_login = time();

			if ($remember)
			{
				$token = Mango::factory('user_token', array(
					'token'      => sha1(uniqid(Text::random('alnum', 32), TRUE)),
					'expires'    => time() + $this->_config['lifetime'],
					'user_agent' => sha1(Request::$user_agent),
				));

				$user->tokens[] = $token;

				// Set the autologin cookie
				Cookie::set('authautologin', $token->token, $this->_config['lifetime']);
			}

			$user->update();
			$this->complete_login($user);

			return TRUE;
		}

		return FALSE;
	}

}
