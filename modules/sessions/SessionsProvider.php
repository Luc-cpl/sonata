<?php

namespace Sonata\Sessions;

use Orkestra\App;
use Orkestra\Interfaces\ProviderInterface;
use Sonata\Sessions\Interfaces\SessionInterface;
use SessionHandler;

class SessionsProvider implements ProviderInterface
{
    public function register(App $app): void
    {
        $app->bind(SessionInterface::class, Session::class);
        SessionDrivers::register('php', SessionHandler::class, []);
    }

    public function boot(App $app): void
    {
        //
    }
}
