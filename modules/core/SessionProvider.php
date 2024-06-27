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
            'sonata.auth_guards'   => function ($value) {
                if (!is_array($value)) {
                    return 'The auth guards config must be an array';
                }
                foreach ($value as $key => $config) {
                    if (!is_string($key)) {
                        return 'The auth guard key must be a string';
                    }

                    if (!is_array($config) || !array_key_exists('driver', $config) || !array_key_exists('repository', $config)) {
                        return 'The auth guard config must be an array with keys "driver" and "repository"';
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

        $app->config()->set('definition', [
            'sonata.default_guard' => ['Default guard key'],
            'sonata.auth_guards'   => ['Auth guards'],
        ]);

        $app->bind(AuthGuardInterface::class, AuthGuard::class);

        SessionDrivers::register('default', PHPSession::class, 1440, true);
    }

    public function boot(App $app): void
    {
        //
    }
}
