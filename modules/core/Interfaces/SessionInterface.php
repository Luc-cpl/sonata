<?php

namespace Sonata\Interfaces;

interface SessionInterface
{
    /**
     * Add a guard key to the session.
     *
     * @return $this
     */
    public function guardedBy(string $name): self;

    /**
     * Sets the session name.
     */
    public function ttl(int $ttl): void;

    /**
     * If the session should add a cookie with the session id.
     */
    public function useCookie(bool $use): void;

    /**
     * Retrieves the session id.
     */
    public function getId(): string;

    /**
     * Starts the session.
     */
    public function start(): void;

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
     * Sets the user id.
     */
    public function setUserId(int|string $userId): void;

    /**
     * Retrieves the user id.
     */
    public function getUserId(): int|string|null;

    /**
     * Removes the user id.
     */
    public function removeUserId(): void;

    /**
     * Sets a session variable that will be available only in next request.
     */
    public function flash(string $key, mixed $value): void;

    /**
     * Retrieves all session variables.
     *
     * @return array<string, mixed>
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
