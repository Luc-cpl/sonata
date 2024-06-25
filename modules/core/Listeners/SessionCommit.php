<?php

namespace Sonata\Listeners;

use Orkestra\App;
use Orkestra\Services\Hooks\Interfaces\ListenerInterface;
use Sonata\Authorization;
use Sonata\Interfaces\SessionInterface;

class SessionCommit implements ListenerInterface
{
    public function __construct(
        private App $app
    ) {
        //
    }

    /**
     * @return string|string[]
     */
    public function hook(): string|array
    {
        return '{app}.http.router.response.before';
    }

    /**
     * @return void
     */
    public function handle()
    {
        $sessions = [$this->app->get(SessionInterface::class)];

        $auth = $this->app->get(Authorization::class);

        /** @var array<string, array{driver: class-string, repository: class-string}> */
        $guards = $this->app->config()->get('sonata.auth_guards');
        foreach ($guards as $guard) {
            $driver = $auth->guard($guard['driver']);
            $sessions[] = $driver->session();
        }

        $sessionsIds = [];

        foreach ($sessions as $session) {
            if (!isset($sessionsIds[$session->getId()]) && $session->started()) {
                $session->commit();
            }
            $sessionsIds[$session->getId()] = true;
        }
    }
}
