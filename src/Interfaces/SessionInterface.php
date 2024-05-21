<?php

namespace Sonata\Interfaces;

interface SessionInterface
{
	/**
	 * Starts the session.
	 * Should follows the PHP session_start() function signature.
	 *
	 * @see https://www.php.net/manual/en/function.session-start.php
	 * @see https://www.php.net/manual/en/session.configuration.php
	 * @param array<string, mixed> $params
	 */
	public function start(array $params = []): bool;

	/**
	 * Checks if the session is started.
	 */
	public function started(): bool;

	/**
	 * Commits the session.
	 */
	public function commit(): void;

	/**
	 * Sets a session variable.
	 */
	public function set(string $key, mixed $value): void;

	/**
	 * Sets a session variable that will be available only in next request.
	 */
	public function flash(string $key, mixed $value): void;

	/**
	 * Retrieves all session variables.
	 */
	public function all(): array;

	/**
	 * Retrieves a session variable.
	 */
	public function get(string $key): mixed;

	/**
	 * Retrieves a flash session variable from the previous request.
	 */
	public function getFlash(string $key): mixed;

	/**
	 * Checks if a session variable exists.
	 */
	public function has(string $key): bool;

	/**
	 * Removes a session variable.
	 */
	public function remove(string $key): void;

	/**
	 * Destroys the session.
	 */
	public function destroy(): void;
}