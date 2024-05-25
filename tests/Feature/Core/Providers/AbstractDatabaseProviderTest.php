<?php

use Sonata\Providers\AbstractDatabaseProvider;

class Provider extends AbstractDatabaseProvider
{}

it('should validate sonata.user_entity', function () {
	$provider = new Provider();
	$provider->register(app());

	app()->config()->set('sonata.user_entity', 'App\Entities\User');
	app()->config()->validate();
})->expectException(InvalidArgumentException::class, 'Invalid configuration for "sonata.user_entity": The user entity must exist');