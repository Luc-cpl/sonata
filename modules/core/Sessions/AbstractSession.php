<?php

namespace Sonata\Sessions;

use Sonata\Interfaces\SessionInterface;
use RuntimeException;

abstract class AbstractSession implements SessionInterface
{
    protected ?string $guard = null;

    protected int $ttl = 0;

    protected bool $addCookie = false;

	/**
	 * @var array<string, mixed>
	 */
	protected array $data = [];

    /**
     * @var array<string, mixed>
     */
    protected array $flash = [];

    public function guardedBy(string $name): self
    {
        $clone = clone $this;
        $clone->guard = $name;
        return $clone;
    }

    public function ttl(int $ttl): void
    {
        $this->ttl = $ttl;
    }

    public function useCookie(bool $use): void
    {
        $this->addCookie = $use;
    }

    public function setUserId(int|string $userId): void
    {
        $this->set('user_id', $userId);
    }

    public function getUserId(): int|string|null
    {
        /** @var int|string|null */
        return $this->get('user_id');
    }

    public function removeUserId(): void
    {
        $this->remove('user_id');
    }

    public function set(string $key, mixed $value): void
    {
        $this->ensureStarted();
        $this->data[$key] = $value;
    }

    public function flash(string $key, mixed $value): void
    {
        $this->ensureStarted();
        $this->flash[$key] = $value;
    }

    public function all(): array
    {
        $this->ensureStarted();
        return $this->data;
    }

    public function get(string $key): mixed
    {
        $this->ensureStarted();
        return $this->data[$key] ?? null;
    }

    public function getFlash(string $key): mixed
    {
        $this->ensureStarted();
        /** @var string[] */
        $flashData = $this->get('flash') ?? [];
        return $flashData[$key] ?? null;
    }

    public function has(string $key): bool
    {
        $this->ensureStarted();
        return isset($this->data[$key]);
    }

    public function remove(string $key): void
    {
        $this->ensureStarted();
        unset($this->data[$key]);
    }

    public function destroy(): void
    {
        $this->ensureStarted();
        $this->flash = [];
        $this->data = [];
        $this->commit();
    }

    protected function ensureStarted(): void
    {
        if (!$this->started()) {
            throw new RuntimeException('The session is not started');
        }
    }
}
