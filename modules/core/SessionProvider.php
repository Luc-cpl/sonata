<?php

namespace Sonata;

use Orkestra\App;
use Orkestra\Interfaces\ProviderInterface;
use Sonata\Interfaces\AuthGuardInterface;
use Sonata\Interfaces\Repository\IdentifiableInterface;
use Sonata\Interfaces\SessionInterface;
use Sonata\Listeners\SessionCommit;
use Sonata\Middleware\AuthorizationMiddleware;
use Sonata\Sessions\PHPSession;

class SessionProvider implements ProviderInterface
{
    /**
     * @var array<class-string>
     */
    public array $listeners = [
        SessionCommit::class,
    ];

    /**
     * @var array<class-string>
     */
    public array $middleware = [
        'auth' => AuthorizationMiddleware::class,
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

                    if (!class_exists($config['driver']) && !interface_exists($config['driver'])) {
                        return 'The guard driver must be a class or interface';
                    }

                    $driverImplementations = class_implements($config['driver']);
                    if (!in_array(SessionInterface::class, $driverImplementations) && $config['driver'] !== SessionInterface::class) {
                        return 'The guard driver must implement ' . SessionInterface::class;
                    }

                    if (!class_exists($config['repository']) && !interface_exists($config['repository'])) {
                        return 'The guard repository must be a class or interface';
                    }

                    $repositoryImplementations = class_implements($config['repository']);
                    if (!in_array(IdentifiableInterface::class, $repositoryImplementations)) {
                        return 'The guard repository must implement ' . IdentifiableInterface::class;
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

        $app->bind(SessionInterface::class, function (App $app) {
            /** @var class-string */
            $class = $app->config()->get('sonata.session');
            /** @var SessionInterface */
            $session = $app->get($class);
            return $session;
        });

        $app->bind(AuthGuardInterface::class, AuthGuard::class);
    }

    public function boot(App $app): void
    {
        //
    }
}
