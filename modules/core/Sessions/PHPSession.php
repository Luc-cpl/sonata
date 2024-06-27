<?php

namespace Sonata\Sessions;

use Orkestra\App;

class PHPSession extends AbstractSession
{
    private bool $started = false;

    public function __construct(
        private App $app
    ) {
        //
    }

    public function getId(): string
    {
        return session_id() ?: '';
    }

    public function start(): void
    {
        $this->started = true;

        if (session_status() === PHP_SESSION_ACTIVE) {
            $this->data = $_SESSION[$this->getPrefix()] ?? [];
            return;
        }

        session_start([
            'gc_maxlifetime' => $this->ttl,
            'cookie_lifetime' => $this->ttl,
            'use_cookies' => $this->addCookie,
            'use_only_cookies' => $this->addCookie,
        ]);

        $this->data = $_SESSION[$this->getPrefix()] ?? [];
    }

    public function started(): bool
    {
        return $this->started;
    }

    public function commit(): void
    {
        $this->ensureStarted();
        if (!empty($this->flash)) {
            $this->set('flash', $this->flash);
        }

        if (!empty($this->data)) {
            $_SESSION[$this->getPrefix()] = $this->data;
        } else {
            unset($_SESSION[$this->getPrefix()]);
        }

        session_commit();
        $this->started = false;
    }

    private function getPrefix(): string
    {
        $prefix = $this->app->slug() . '.sonata';
        return $this->guard ? "{$prefix}.{$this->guard}" : $prefix;
    }
}
