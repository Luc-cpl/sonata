<?php

use League\Route\Http\Exception\UnauthorizedException;
use Sonata\AuthDrivers\SessionDriver;
use Sonata\Interfaces\RepositoryInterface;
use Sonata\Middleware\AuthorizationMiddleware;
use Sonata\Providers\SessionProvider;
use Sonata\Testing\Auth;
use Sonata\Testing\Doctrine;
use Tests\Entities\DoctrineSubject;

beforeEach(function () {
    app()->provider(SessionProvider::class);
    app()->config()->set('sonata.default_guard', 'web');
    app()->config()->set('sonata.auth_guards', [
        'web' => [
            'driver'     => SessionDriver::class,
            'repository' => RepositoryInterface::class,
        ],
        'web2' => [
            'driver'     => SessionDriver::class,
            'repository' => RepositoryInterface::class,
        ],
    ]);
    doctrineTest();
});

afterEach(function () {
    Auth::clear();
});

it('should allow the access for a logged in subject', function () {
    $subject = Doctrine::factory(DoctrineSubject::class)[0];
    Auth::actingAs($subject);

    $middleware = middleware(AuthorizationMiddleware::class);
    $response = $middleware->process();
    expect($response->getStatusCode())->toBe(200);
});

it('should throw an exception for a guest subject', function () {
    $middleware = middleware(AuthorizationMiddleware::class);
    $middleware->process();
})->expectException(UnauthorizedException::class);

it('should allow the access for a logged in subject with a specific guard', function () {
    $subject = Doctrine::factory(DoctrineSubject::class)[0];
    Auth::actingAs($subject, 'web2');

    $middleware = middleware(AuthorizationMiddleware::class, ['guard' => 'web2']);
    $response = $middleware->process();
    expect($response->getStatusCode())->toBe(200);

    $middleware = middleware(AuthorizationMiddleware::class, ['guard' => 'web']);
    $middleware->process();
})->expectException(UnauthorizedException::class);
