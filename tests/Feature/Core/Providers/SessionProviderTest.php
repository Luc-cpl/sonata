<?php

use Orkestra\Providers\HooksProvider;
use Sonata\Interfaces\SessionInterface;
use Sonata\Providers\SessionProvider;

beforeEach(function () {
    app()->provider(SessionProvider::class);
    app()->config()->set('sonata.default_guard', 'web');
    app()->config()->set('sonata.auth_guards', []);
});

it('should auto start a session when retrieve from interface', function () {
    session_write_close();
    expect(app()->get(SessionInterface::class)->started())->toBeTrue();
});

it('should commit the session interface on `{app}.http.router.response.before` hook', function () {
    session_write_close();
    app()->provider(HooksProvider::class);
    $session = app()->get(SessionInterface::class);

    expect(app()->get(SessionInterface::class)->started())->toBeTrue();

    app()->hookCall('http.router.response.before');

    expect($session->started())->toBeFalse();
});

it('should throw an exception if the session implementation is not a valid class', function () {
    app()->config()->set('sonata.session', 'InvalidClass');
    app()->boot();
})->throws(InvalidArgumentException::class, 'The session implementation must be a valid class');

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
