<?php

namespace Sonata\Testing;

use Sonata\Authorization;
use Sonata\Entities\Abstracts\AbstractUser;
use Sonata\Interfaces\AuthInterface;

class Auth
{
	private static array $usedGuards = [];
	/**
	 * Authenticate as the given user for testing purposes.
	 */
	public static function actingAs(AbstractUser $user, ?string $guard = null): void
	{
		self::$usedGuards[] = $guard ?? null;
		self::$usedGuards = array_unique(self::$usedGuards);
		self::guard($guard)->authenticate($user);
	}

	/**
	 * Retrieve the guard instance for the given guard key.
	 */
	public static function guard(?string $guard = null): AuthInterface
	{
		self::$usedGuards[] = $guard ?? null;
		self::$usedGuards = array_unique(self::$usedGuards);;
		$auth = app()->get(Authorization::class);
		$auth = $guard !== null ? $auth->guard($guard) : $auth;
		return $auth;
	}

	/**
	 * Logout all the guards used in the tests.
	 */
	public static function clear(): void
	{
		foreach (self::$usedGuards as $guard) {
			self::guard($guard)->logout();
		}
		self::$usedGuards = [];
	}
}