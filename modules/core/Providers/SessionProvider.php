<?php

namespace Sonata\Providers;

use Orkestra\App;
use Orkestra\Interfaces\ProviderInterface;
use Sonata\Interfaces\AuthDriverInterface;
use Sonata\Interfaces\RepositoryInterface;
use Sonata\Interfaces\SessionInterface;
use Sonata\Listeners\SessionCommit;
use Sonata\Sessions\PHPSession;

class SessionProvider implements ProviderInterface
{
    public array $listeners = [
        SessionCommit::class,
    ];

    public function register(App $app): void
    {
        $app->config()->set('validation', [
            'sonata.default_guard' => fn ($value) => is_string($value) ? true : 'The default guard key must be a string',
            'sonata.session'       => fn ($value) => class_exists($value) ? true : 'The session implementation must be a valid class',
            'sonata.auth_guards'   => function ($value) {
                if (!is_array($value)) {
                    return 'The auth guards config must be an array';
                }
                foreach ($value as $config) {
                    if (!is_array($config) || !array_key_exists('driver', $config) || !array_key_exists('repository', $config)) {
                        return 'The auth guard config must be an array with keys "driver" and "repository"';
                    }
                    if (!class_exists($config['driver']) || !is_subclass_of($config['driver'], AuthDriverInterface::class)) {
                        return 'The guard driver must implement ' . AuthDriverInterface::class;
                    }
                    // Allow the default repository, used for testing
                    if ($config['repository'] == RepositoryInterface::class) {
                        continue;
                    }
                    if (!class_exists($config['repository']) || !is_subclass_of($config['repository'], RepositoryInterface::class)) {
                        return 'The guard repository must implement ' . RepositoryInterface::class;
                    }
                }
                return true;
            },
        ]);

        $phpSession = PHPSession::class;
        $app->config()->set('definition', [
            'sonata.session'       => ["Session implementation (defaults to $phpSession)", $phpSession],
            'sonata.default_guard' => ['Default guard key'],
            'sonata.auth_guards'   => ['Auth guards'],
        ]);

        $app->bind(SessionInterface::class, function () use ($app) {
            /** @var SessionInterface */
            $session = $app->get($app->config()->get('sonata.session'));
            $session->start();
            return $session;
        });
    }

    public function boot(App $app): void
    {
        //
    }
}
