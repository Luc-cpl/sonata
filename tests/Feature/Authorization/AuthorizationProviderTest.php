<?php

use Sonata\Authorization\AuthorizationProvider;
use Tests\TestRepository;
use Sonata\Sessions\SessionsProvider;

beforeEach(function () {
    app()->provider(SessionsProvider::class);
    app()->provider(AuthorizationProvider::class);
    app()->config()->set('sonata.default_guard', 'web');
    app()->config()->set('sonata.auth_guards', [
        'web' => [
            'driver' => 'php',
            'repository' => TestRepository::class,
        ],
    ]);
});

it('should throw an exception if the default guard key is not a string', function () {
    app()->config()->set('sonata.default_guard', 123);
    app()->boot();
})->throws(InvalidArgumentException::class, 'The default guard key must be a string');

it('should throw an exception if the auth guards config is not an array', function () {
    app()->config()->set('sonata.auth_guards', 'Invalid');
    app()->boot();
})->throws(InvalidArgumentException::class, 'The auth guards config must be an array');

it('should throw an exception if the auth guard config is not an array with keys "driver" and "repository"', function () {
    app()->config()->set('sonata.auth_guards', ['web' => 'Invalid']);
    app()->boot();
})->throws(InvalidArgumentException::class, 'The auth guard config must be an array with keys "driver" and "repository"');
