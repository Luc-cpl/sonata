<?php

namespace Sonata\Providers;

use Orkestra\App;
use Orkestra\Interfaces\ProviderInterface;
use Sonata\AuthDrivers\SessionDriver;
use Sonata\Interfaces\SessionInterface;
use Sonata\Interfaces\UserRepositoryInterface;
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
			'sonata.auth_guards' => function ($value) {
				if (!is_array($value)) {
					return 'The auth guards config must be an array';
				}
				foreach ($value as $config) {
					if (!is_array($config) || !array_key_exists('driver', $config) || !array_key_exists('repository', $config)) {
						return 'The auth guard config must be an array with keys "driver" and "repository"';
					}
				}
				return true;
			},
		]);

		$phpSession = PHPSession::class;
		$app->config()->set('definition', [
			'sonata.session'       => ["Session implementation (defaults to $phpSession)", $phpSession],
			'sonata.default_guard' => ['Default guard key (defaults to "web")', 'web'],
			'sonata.auth_guards'   => ['Auth guards', [
				'web' => [
					'driver'     => SessionDriver::class,
					'repository' => UserRepositoryInterface::class,
				],
			]],
		]);

		$app->bind(SessionInterface::class, fn () => $app->get($app->config()->get('sonata.session')));
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