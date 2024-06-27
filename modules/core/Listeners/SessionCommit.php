<?php

namespace Sonata\Listeners;

use Orkestra\App;
use Orkestra\Services\Hooks\Interfaces\ListenerInterface;
use Sonata\Authorization;
use Sonata\Interfaces\SessionInterface;
use Sonata\SessionDrivers;

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
        $drivers = $this->app->get(SessionDrivers::class);
        $auth = $this->app->get(Authorization::class);
        $sessions = $drivers->getAll();

        /** @var array<string, array{driver: class-string, repository: class-string}> */
        $guards = $this->app->config()->get('sonata.auth_guards');
        $guards = array_keys($guards);
        foreach ($guards as $guard) {
            $driver = $auth->guard($guard);
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
