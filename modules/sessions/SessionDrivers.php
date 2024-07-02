<?php

namespace Sonata\Sessions;

use Orkestra\App;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use SessionHandlerInterface;

/**
 * A class to easily register session drivers in the application.
 */
class SessionDrivers
{
	/**
	 * @var array<string, array{driver: class-string, options: array, instance: SessionHandlerInterface|null, id: string}>
	 */
    private static array $sessions = [];

	private ?string $current = null;

	public function __construct(
		private App $app,
		private ServerRequestInterface $request,
	) {
		$this->initialize();
	}

	public function current(): ?string
	{
		return $this->current;
	}

	public function use(string $name): void
	{
		$key = self::hashName($name);
		$sessionName = $this->getSessionName($key);

		if (!isset(self::$sessions[$key])) {
			throw new RuntimeException("Session driver \"$name\" not registered.");
		}

		if ($this->current() === $name && $sessionName === session_name()) {
			if (session_status() !== PHP_SESSION_ACTIVE) {
				$this->start($sessionName, self::$sessions[$key]['options']);
			}
			return;
		}

		// Close and clear the current session if it's open
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		unset($_SESSION);
		/** @var SessionHandlerInterface */
		$driver = $this->app->get(self::$sessions[$key]['driver']);

		session_set_save_handler($driver, true);

		if (!isset(self::$sessions[$key]['id'])) {
			self::$sessions[$key]['id'] = session_create_id($key);
		}

		session_id(self::$sessions[$key]['id']);
		$this->start($sessionName, self::$sessions[$key]['options']);

		self::$sessions[$key]['id'] = session_id();
		self::$sessions[$key]['instance'] = $driver;

		$this->current = $name;
	}

	private function initialize(): void
	{
		$authHeader = $this->request->getHeader('Authorization')[0] ?? '';
		if (str_starts_with($authHeader, 'Bearer ')) {
			$auth = substr($authHeader, 7);
			$length = strlen(array_key_first(self::$sessions));
			$key = substr($auth, 0, $length);
			$session = self::$sessions[$key] ?? ['options' => []];
			if (($session['options']['use_only_cookies'] ?? true) === false) {
				self::$sessions[$key]['id'] = $auth;
			}
		}

		// Get possible session cookies from registered session drivers
		foreach (self::$sessions as $key => $session) {
			$sessionName = $this->getSessionName($key);
			$id = $this->request->getCookieParams()[$sessionName] ?? null;
			if ($id && ($session['options']['use_cookies'] ?? true)) {
				self::$sessions[$key]['id'] = $id;
			}
		}
	}

	private function start(string $sessionName, array $options): void
	{
		$defaultOptions = [
			'use_strict_mode' => true,
			'use_cookies' => true,
			'use_only_cookies' => true,
			'cookie_httponly' => true,
		];
		$options = array_merge($defaultOptions, $options);
		$options['name'] = $sessionName;
		session_start($options);
	}

	private function getSessionName(string $key): string
	{
		return $key . '_session';
	}

	/**
	 * Register a session driver.
	 * 
	 * @see https://www.php.net/manual/function.session-start.php
	 * @param class-string $driver The class name of the session driver.
	 * @param mixed[] $options The options to pass to the session driver.
	 */
	public static function register(string $name, string $driver, array $options = []): void
	{
		self::$sessions[self::hashName($name)] = [
			'driver' => $driver,
			'options' => $options
		];
	}

	/**
	 * Get a session driver definition.
	 * 
	 * @param string $name The name of the session driver.
	 * @return ?array{driver: class-string, options: mixed[]} The session driver definition.
	 */
	public static function definition(string $name): ?array
	{
		$key = self::hashName($name);
		$driver = self::$sessions[$key] ?? null;
		if ($driver === null) {
			return null;
		}
		unset($driver['instance']);
		unset($driver['id']);
		return $driver;
	}

	/**
	 * Generate a hash to avoid exposing the session driver name.
	 *
	 * @param string $name The name of the session driver.
	 * @return string
	 */
	private static function hashName(string $name): string
	{
		return hash('xxh32', $name);
	}
}
