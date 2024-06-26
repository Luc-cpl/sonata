<?php

namespace Sonata\Sessions;

use Orkestra\App;
use Sonata\Interfaces\SessionInterface;
use RuntimeException;

class PHPSession implements SessionInterface
{
    private bool $avoidCommit = false;

    private bool $started = false;

    private ?string $guard = null;

    /**
     * @var array<string, mixed>
     */
    private array $flash = [];

    public function __construct(
        private App $app
    ) {
        //
    }

    public function guardedBy(string $name): self
    {
        $clone = clone $this;
        $clone->guard = $name;
        return $clone;
    }

    public function getId(): string
    {
        return session_id() ?: '';
    }

    public function start(array $params = []): bool
    {
        /**
         * In this case another part of the application
         * has already started the session.
         */
        if (!$this->started && $this->started()) {
            $this->avoidCommit = true;
        }

        if ($this->started()) {
            return true;
        }

        $this->started = true;

        return session_start($params);
    }

    public function started(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    public function commit(): void
    {
        $this->ensureStarted();
        if (!empty($this->flash)) {
            $this->set('flash', $this->flash);
        }

        if ($this->avoidCommit) {
            /**
             * In this case another part of the application
             * has already started the session.
             */
            return;
        }
        session_commit();
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
        $key = $this->getPrefix() . $key;
        $_SESSION[$key] = $value;
    }

    public function flash(string $key, mixed $value): void
    {
        $this->ensureStarted();
        $this->flash[$key] = $value;
    }

    public function all(): array
    {
        $this->ensureStarted();
        $prefix = $this->getPrefix();
        $response = [];
        foreach ($_SESSION as $key => $value) {
            if (str_starts_with($key, $prefix)) {
                $key = str_replace($prefix, '', $key);
                $response[$key] = $value;
            }
        }
        return $response;
    }

    public function get(string $key): mixed
    {
        $this->ensureStarted();
        $key = $this->getPrefix() . $key;
        return $_SESSION[$key] ?? null;
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
        $key = $this->getPrefix() . $key;
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        $this->ensureStarted();
        $key = $this->getPrefix() . $key;
        unset($_SESSION[$key]);
    }

    public function destroy(): void
    {
        $this->ensureStarted();
        $prefix = $this->getPrefix();
        $this->flash = [];
        $_SESSION = array_filter($_SESSION, fn ($key) => !str_starts_with($key, $prefix), ARRAY_FILTER_USE_KEY);
        $this->commit();
    }

    private function ensureStarted(): void
    {
        if (!$this->started()) {
            throw new RuntimeException('The session is not started');
        }
    }

    private function getPrefix(): string
    {
        $prefix = $this->app->slug() . '.sonata.';
        return $this->guard ? $prefix . $this->guard . '.' : $prefix;
    }
}
