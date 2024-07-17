<?php

namespace Sonata\Sessions;

use Orkestra\App;
use Orkestra\Interfaces\ProviderInterface;
use Sonata\Sessions\Interfaces\SessionInterface;
use Sonata\Sessions\Handlers\RepositorySessionHandler;
use Sonata\Sessions\Repositories\DoctrineSessionRepository;
use Sonata\Sessions\Entities\Session as SessionEntity;
use Sonata\Authorization\Authorization;
use Sonata\Doctrine\EntityRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ServerRequestInterface;
use SessionHandler;

class SessionsProvider implements ProviderInterface
{
    public function register(App $app): void
    {
        $app->bind(SessionInterface::class, Session::class);

        $app->bind(DoctrineSessionRepository::class, DoctrineSessionRepository::class)->constructor(
            manager: fn (App $app) => $app->make(EntityManagerInterface::class)
        );

        $app->bind(SessionDrivers::class, SessionDrivers::class)->constructor(
            request: fn (App $app) => $app->has(ServerRequestInterface::class) ? $app->get(ServerRequestInterface::class) : null,
        );

        $app->bind('sessions.handler.doctrine', RepositorySessionHandler::class)->constructor(
            repository: fn (App $app) => $app->get(DoctrineSessionRepository::class),
            request: fn (App $app) => $app->has(ServerRequestInterface::class) ? $app->get(ServerRequestInterface::class) : null,
            authorization: fn (App $app) => $app->config()->has('sonata.default_guard') ? $app->get(Authorization::class) : null,
        );

        SessionDrivers::register('php', SessionHandler::class, []);
        SessionDrivers::register('doctrine', 'sessions.handler.doctrine', []);
        EntityRegistry::register(SessionEntity::class);
    }

    public function boot(App $app): void
    {
        //
    }
}
