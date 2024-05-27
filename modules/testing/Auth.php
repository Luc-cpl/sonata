<?php

namespace Sonata\Testing;

use Sonata\Authorization;
use Sonata\Interfaces\AuthInterface;

class Auth
{
	private static array $usedGuards = [];

	/**
	 * Authenticate as the given subject for testing purposes.
	 */
	public static function actingAs(object $subject, ?string $guard = null): void
	{
		self::$usedGuards[] = $guard ?? null;
		self::$usedGuards = array_unique(self::$usedGuards);
		self::guard($guard)->authenticate($subject);
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
	 * revoke all authentication guards used in the tests.
	 */
	public static function clear(): void
	{
		foreach (self::$usedGuards as $guard) {
			self::guard($guard)->revoke();
		}
		self::$usedGuards = [];
	}
}