<?php

namespace Sonata\Providers;

use Orkestra\App;
use Orkestra\Interfaces\ProviderInterface;

abstract class AbstractDatabaseProvider implements ProviderInterface
{
	public function register(App $app): void
	{
		$app->config()->set('validation', [
			'sonata.user_entity' => function ($value) {
				if (!class_exists($value)) {
					return 'The user entity must exist';
				}
				return true;
			},
		]);

		$app->config()->set('definition', [
			'sonata.user_entity' => ['User entity class'],
		]);
	}

	public function boot(App $app): void
	{
		//
	}
}