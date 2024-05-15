<?php

namespace Sonata\Providers;

use Orkestra\App;
use Orkestra\Interfaces\ProviderInterface;
use Sonata\Entities\Abstracts\AbstractUser;

abstract class AbstractProvider implements ProviderInterface
{
	public function register(App $app): void
	{
		$app->config()->set('validation', [
			'sonata.user_entity' => function ($value) {
				if (!class_exists($value) || !is_subclass_of($value, AbstractUser::class)) {
					return 'The user entity must exist and extend Sonata\Entities\Abstracts\AbstractUser';
				}
				return true;
			},
		]);

		$app->config()->set('definition', [
			'sonata.user_entity' => ['User entity class (defaults to App\Entities\User)', fn () => 'App\Entities\User'],
		]);
	}

	public function boot(App $app): void
	{
		//
	}
}