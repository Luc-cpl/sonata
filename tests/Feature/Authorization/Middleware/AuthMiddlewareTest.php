<?php

use League\Route\Http\Exception\UnauthorizedException;
use Orkestra\Providers\HttpProvider;
use Sonata\Authorization\AuthorizationProvider;
use Sonata\Authorization\Middleware\AuthorizationMiddleware;
use Sonata\Sessions\SessionsProvider;
use Sonata\Testing\Auth;
use Sonata\Testing\Doctrine;
use Tests\Entities\DoctrineUser;
use Tests\TestRepository;

beforeEach(function () {
    app()->provider(HttpProvider::class);
    app()->provider(SessionsProvider::class);
    app()->provider(AuthorizationProvider::class);
    app()->config()->set('sonata.default_guard', 'web');
    app()->config()->set('sonata.auth_guards', fn () => [
        'web' => [
            'driver'     => 'php',
            'repository' => TestRepository::class,
        ],
        'web2' => [
            'driver'     => 'php',
            'repository' => TestRepository::class,
        ],
    ]);
    doctrineTest();
});

afterEach(function () {
    Auth::clear();
});

it('should allow the access for a logged in user', function () {
    /** @var DoctrineUser */
    $user = Doctrine::factory(DoctrineUser::class)[0];
    Auth::actingAs($user);

    $middleware = middleware(AuthorizationMiddleware::class);
    $response = $middleware->process();
    expect($response->getStatusCode())->toBe(200);
});

it('should throw an exception for a guest user', function () {
    $middleware = middleware(AuthorizationMiddleware::class);
    $middleware->process();
})->expectException(UnauthorizedException::class);

it('should allow the access for a logged in user with a specific guard', function () {
    /** @var DoctrineUser */
    $user = Doctrine::factory(DoctrineUser::class)[0];
    Auth::actingAs($user, 'web2');

    $middleware = middleware(AuthorizationMiddleware::class, ['guard' => 'web2']);
    $response = $middleware->process();
    expect($response->getStatusCode())->toBe(200);

    $middleware = middleware(AuthorizationMiddleware::class, ['guard' => 'web']);
    $middleware->process();
})->expectException(UnauthorizedException::class);

it('should allow access for guest access', function () {
    $middleware = middleware(AuthorizationMiddleware::class, ['guest' => true]);
    $response = $middleware->process();
    expect($response->getStatusCode())->toBe(200);

    /** @var DoctrineUser */
    $user = Doctrine::factory(DoctrineUser::class)[0];
    Auth::actingAs($user);

    $middleware = middleware(AuthorizationMiddleware::class, ['guest' => true]);
    $middleware->process();
})->expectException(UnauthorizedException::class);
