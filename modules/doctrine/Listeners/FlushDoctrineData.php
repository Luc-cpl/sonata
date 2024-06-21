<?php

namespace Sonata\Doctrine\Listeners;

use Orkestra\App;
use Orkestra\Services\Hooks\Interfaces\ListenerInterface;
use Doctrine\ORM\EntityManagerInterface;

class FlushDoctrineData implements ListenerInterface
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
        $entityManager = $this->app->get(EntityManagerInterface::class);
        $entityManager->flush();
    }
}
