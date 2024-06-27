<?php

namespace Sonata;

use Orkestra\Interfaces\AppContainerInterface;
use Sonata\Interfaces\SessionInterface;

/**
 * A class to easily register session drivers in the application.
 */
class SessionDrivers
{
    private static array $sessions = [];

	public function __construct(
		private AppContainerInterface $app
	) {
		//
	}

	/**
	 * Get all registered session drivers.
	 *
	 * @return array<string, SessionInterface>
	 */
	public function getAll(): array
	{
		$sessions = self::$sessions;
		$sessions = array_filter($sessions, fn ($session) => $session['instance'] ?? false);
		return array_map(fn ($session) => $session['instance'], $sessions);
	}

	public function get(string $name = 'default'): SessionInterface
	{
		if (!isset(self::$sessions[$name])) {
			throw new \RuntimeException("Session driver $name not found.");
		}

		if (self::$sessions[$name]['instance'] ?? false) {
			return self::$sessions[$name]['instance'];
		}

		$session = $this->app->get(self::$sessions[$name]['class']);
		$session->ttl(self::$sessions[$name]['ttl']);
		$session->useCookie(self::$sessions[$name]['addCookie']);
		self::$sessions[$name]['instance'] = $session;
		return $session;
	}

	/**
	 * Register a session driver.
	 *
	 * @param class-string $class The class name of the session driver.
	 */
	public static function register(string $name, string $class, int $ttl = 0, bool $addCookie = false): void
	{
		self::$sessions[$name] = [
			'class' => $class,
			'ttl' => $ttl,
			'addCookie' => $addCookie
		];
	}
}
