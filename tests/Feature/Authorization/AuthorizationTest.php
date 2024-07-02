<?php

use Orkestra\Providers\HttpProvider;
use Sonata\Authorization\Authorization;
use Sonata\Authorization\AuthorizationProvider;
use Sonata\Interfaces\Entity\IdentifiableInterface;
use Sonata\Interfaces\Repository\IdentifiableRepositoryInterface;
use Sonata\Sessions\Interfaces\SessionInterface;
use Sonata\Sessions\SessionDrivers;
use Sonata\Sessions\SessionsProvider;
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
});

it('should authenticate a user with default guard', function () {
    $user = test()->getMockBuilder(IdentifiableInterface::class)->getMock();
    $user->method('getId')->willReturn(1);

    /** @var IdentifiableInterface $user */
    app()->get(Authorization::class)->authenticate($user);

    expect(app()->get(Authorization::class)->user()->getId())->toBe(1);
    expect(app()->get(Authorization::class)->guard('web')->user()->getId())->toBe(1);
    expect(app()->get(Authorization::class)->guard('web2')->user())->toBeNull();
});

it('can get a authenticated user with default guard', function () {
    $user = (object) ['id' => 1];
    app()->get(TestRepository::class)->persist($user);
    app()->get(SessionDrivers::class)->use('php.web');
    $_SESSION['user_id'] = 1;

    expect(app()->get(Authorization::class)->user())->toBe($user);
    expect(app()->get(Authorization::class)->guard('web')->user())->toBe($user);
    expect(app()->get(Authorization::class)->guard('web2')->user())->toBeNull();
});

it('can logout a user with default guard', function () {
    $user = (object) ['id' => 1];
    app()->get(TestRepository::class)->persist($user);
    app()->get(SessionDrivers::class)->use('php.web');
    $_SESSION['user_id'] = 1;

    app()->get(SessionDrivers::class)->use('php.web2');
    $_SESSION['user_id'] = 1;

    app()->get(Authorization::class)->revoke();

    expect(app()->get(Authorization::class)->user())->toBeNull();
    expect(app()->get(Authorization::class)->guard('web')->user())->toBeNull();
    expect(app()->get(Authorization::class)->guard('web2')->user())->toBe($user);
});

it('should throw an exception if the guard does not exist', function () {
    app()->get(Authorization::class)->guard('web3');
})->throws(InvalidArgumentException::class, 'The guard "web3" does not exist');

it('should throw an exception if the guard repository does not implement the IdentifiableRepositoryInterface', function () {
    app()->config()->set('sonata.auth_guards', [
        'web' => [
            'driver'     => 'default',
            'repository' => SessionInterface::class,
        ],
    ]);

    app()->get(Authorization::class)->guard('web');
})->throws(InvalidArgumentException::class, 'The guard repository must implement ' . IdentifiableRepositoryInterface::class);
