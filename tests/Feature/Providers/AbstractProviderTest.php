<?php

use Sonata\Entities\Abstracts\AbstractUser;
use Sonata\Providers\AbstractProvider;

class Provider extends AbstractProvider
{}

it('should validate sonata.user_entity', function () {
	$this->expectExceptionMessage('The user entity must exist and extend ' . AbstractUser::class);
	$provider = new Provider();
	$provider->register(app());

	app()->config()->set('sonata.user_entity', 'App\Entities\User');
	app()->config()->validate();
})->expectException(InvalidArgumentException::class);