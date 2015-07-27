<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Authenticate\Method;

use Windwalker\Authenticate\Authenticate;
use Windwalker\Authenticate\Credential;

/**
 * The LocalMethod class.
 * 
 * @since  2.0
 */
class LocalMethod extends AbstractMethod
{
	/**
	 * Property users.
	 *
	 * @var  array
	 */
	protected $users = array();

	/**
	 * Property verifyHandler.
	 *
	 * @var callable
	 */
	protected $verifyHandler;

	/**
	 * Class init.
	 *
	 * @param array $users
	 */
	public function __construct(array $users = array())
	{
		$this->users = $users;
	}

	/**
	 * authenticate
	 *
	 * @param Credential $credential
	 *
	 * @return  integer
	 */
	public function authenticate(Credential $credential)
	{
		$username = $credential->username;
		$password = $credential->password;

		if (!$username || !$password)
		{
			$this->status = Authenticate::EMPTY_CREDENTIAL;

			return false;
		}

		foreach ($this->users as $user)
		{
			if (!isset($user['username']))
			{
				continue;
			}

			if ($user['username'] !== $username)
			{
				continue;
			}

			if (!isset($user['password']))
			{
				$this->status = Authenticate::INVALID_CREDENTIAL;

				return false;
			}

			$handler = $this->getVerifyHandler();

			if (call_user_func_array($handler, array($password, $user['password'])))
			{
				$this->status = Authenticate::SUCCESS;

				return true;
			}

			$this->status = Authenticate::INVALID_CREDENTIAL;

			return false;
		}

		$this->status = Authenticate::USER_NOT_FOUND;

		return false;
	}

	/**
	 * Method to get property VerifyHandler
	 *
	 * @return  callable
	 */
	public function getVerifyHandler()
	{
		if (is_callable($this->verifyHandler))
		{
			return $this->verifyHandler;
		}

		return function ($password, $hash)
		{
			if (version_compare(PHP_VERSION, '5.5', '<'))
			{
				throw new \LogicException('Please set a custom verify handler is your PHP version less than 5.5');
			}

			return password_verify($password, $hash);
		};
	}

	/**
	 * Method to set property verifyHandler
	 *
	 * @param   callable $verifyHandler
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setVerifyHandler($verifyHandler)
	{
		$this->verifyHandler = $verifyHandler;

		return $this;
	}

	/**
	 * Method to get property Users
	 *
	 * @return  array
	 */
	public function getUsers()
	{
		return $this->users;
	}

	/**
	 * Method to set property users
	 *
	 * @param   array $users
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setUsers($users)
	{
		$this->users = $users;

		return $this;
	}
}
 