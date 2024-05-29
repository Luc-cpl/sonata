<?php

use Sonata\AuthDrivers\SessionDriver;
use Sonata\Authorization;
use Sonata\Interfaces\RepositoryInterface;
use Sonata\Interfaces\SessionInterface;
use Sonata\SessionProvider;
use Tests\TestRepository;

beforeEach(function () {
    app()->provider(SessionProvider::class);
    app()->config()->set('sonata.default_guard', 'web');
    app()->config()->set('sonata.auth_guards', fn () => [
        'web' => [
            'driver'     => SessionDriver::class,
            'repository' => TestRepository::class,
        ],
        'web2' => [
            'driver'     => SessionDriver::class,
            'repository' => TestRepository::class,
        ],
    ]);

    // Add a test subject to the repository
    app()->bind(TestRepository::class, TestRepository::class)->constructor(data: [
        1 => (object) ['id' => 1],
    ]);
});

it('should authenticate a subject with default guard', function () {
    $session = app()->get(SessionInterface::class);
    $subject = (object) ['id' => 1];
    app()->get(Authorization::class)->authenticate($subject);

    expect(app()->get(Authorization::class)->subject()->id)->toBe(1);
    expect(app()->get(Authorization::class)->guard('web')->subject()->id)->toBe(1);
    expect(app()->get(Authorization::class)->guard('web2')->subject())->toBeNull();
    expect($session->get('guards.web.subject'))->toBe($subject->id);
});

it('can check if a subject is authenticated with default guard', function () {
    $_SESSION ??= [];
    $_SESSION['app.sonata.guards.web.subject'] = 1;

    expect(app()->get(Authorization::class)->check())->toBeTrue();
    expect(app()->get(Authorization::class)->guard('web')->check())->toBeTrue();
    expect(app()->get(Authorization::class)->guard('web2')->check())->toBeFalse();
});

it('can logout a subject with default guard', function () {
    $_SESSION ??= [];
    $_SESSION['app.sonata.guards.web.subject'] = 1;
    $_SESSION['app.sonata.guards.web2.subject'] = 1;

    app()->get(Authorization::class)->revoke();

    expect(app()->get(Authorization::class)->check())->toBeFalse();
    expect(app()->get(Authorization::class)->guard('web')->check())->toBeFalse();
    expect(app()->get(Authorization::class)->guard('web2')->check())->toBeTrue();
});

it('should change the current guard in use', function () {
    $_SESSION ??= [];
    $_SESSION['app.sonata.guards.web.subject'] = null;
    $_SESSION['app.sonata.guards.web2.subject'] = 1;

    app()->get(Authorization::class)->guard('web2');

    expect(app()->get(Authorization::class)->check())->toBeTrue();
    expect(app()->get(Authorization::class)->guard('web')->check())->toBeFalse();
    expect(app()->get(Authorization::class)->guard('web2')->check())->toBeTrue();
});

it('should throw an exception if the guard does not exist', function () {
    app()->get(Authorization::class)->guard('web3');
})->throws(InvalidArgumentException::class, 'The guard "web3" does not exist');

it('should throw an exception if the guard driver does not implement the AuthDriverInterface', function () {
    app()->config()->set('sonata.auth_guards', [
        'web' => [
            'driver'     => Authorization::class,
            'repository' => RepositoryInterface::class,
        ],
    ]);

    app()->get(Authorization::class)->guard('web');
})->throws(InvalidArgumentException::class, 'The guard driver must implement Sonata\Interfaces\AuthDriverInterface');

it('should throw an exception if the guard repository does not implement the RepositoryInterface', function () {
    app()->config()->set('sonata.auth_guards', [
        'web' => [
            'driver'     => SessionDriver::class,
            'repository' => SessionDriver::class,
        ],
    ]);

    app()->get(Authorization::class)->guard('web');
})->throws(InvalidArgumentException::class, 'The guard repository must implement ' . RepositoryInterface::class);
