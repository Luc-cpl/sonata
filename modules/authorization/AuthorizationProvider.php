<?php

namespace Sonata\Authorization;

use Orkestra\App;
use Orkestra\Interfaces\ProviderInterface;
use Sonata\Authorization\Middleware\AuthorizationMiddleware;
use Sonata\Sessions\SessionDrivers;
use Sonata\Authorization\Interfaces\AuthGuardInterface;
use Sonata\Repositories\Interfaces\Partials\IdentifiableRepositoryInterface;
use InvalidArgumentException;

class AuthorizationProvider implements ProviderInterface
{
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
                    if (!in_array(IdentifiableRepositoryInterface::class, $repositoryImplementations)) {
                        return 'The guard repository must implement ' . IdentifiableRepositoryInterface::class;
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
    }

    public function boot(App $app): void
    {
        /** @var array<string, array{driver: string, repository: class-string}> */
        $guards = $app->config()->get('sonata.auth_guards');
		foreach ($guards as $key => &$config) {
			$driver = SessionDrivers::definition($config['driver']);
			if ($driver === null) {
				throw new InvalidArgumentException("The session driver \"{$config['driver']}\" does not exist");
			}
            $config['driver'] = $config['driver'] . '.' . $key;
			SessionDrivers::register($config['driver'], $driver['handler'], $driver['options']);
		}

        $app->config()->set('sonata.auth_guards', $guards);
    }
}
