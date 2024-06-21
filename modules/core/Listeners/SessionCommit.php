<?php

namespace Sonata\Listeners;

use Orkestra\App;
use Orkestra\Services\Hooks\Interfaces\ListenerInterface;
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
        $session = $this->app->get(SessionInterface::class);
        if ($session->started()) {
            $session->commit();
        }
    }
}
