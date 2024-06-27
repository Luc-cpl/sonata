<?php

use Sonata\Authorization;
use Sonata\Interfaces\Repository\IdentifiableInterface;
use Sonata\Interfaces\SessionInterface;
use Sonata\SessionProvider;
use Tests\TestRepository;

beforeEach(function () {
    app()->provider(SessionProvider::class);
    app()->config()->set('sonata.default_guard', 'web');
    app()->config()->set('sonata.auth_guards', fn () => [
        'web' => [
            'driver'     => 'default',
            'repository' => TestRepository::class,
        ],
        'web2' => [
            'driver'     => 'default',
            'repository' => TestRepository::class,
        ],
    ]);

    // Add a test user to the repository
    app()->bind(TestRepository::class, TestRepository::class)->constructor(data: [
        1 => (object) ['id' => 1],
    ]);
});

it('should authenticate a user with default guard', function () {
    $user = (object) ['id' => 1];
    app()->get(Authorization::class)->authenticate($user);

    expect(app()->get(Authorization::class)->user()->id)->toBe(1);
    expect(app()->get(Authorization::class)->guard('web')->user()->id)->toBe(1);
    expect(app()->get(Authorization::class)->guard('web2')->user())->toBeNull();
});

it('can check if a user is authenticated with default guard', function () {
    session_start();
    $_SESSION['app.sonata.web'] = ['user_id' => 1];
    session_write_close();

    expect(app()->get(Authorization::class)->check())->toBeTrue();
    expect(app()->get(Authorization::class)->guard('web')->check())->toBeTrue();
    expect(app()->get(Authorization::class)->guard('web2')->check())->toBeFalse();
});

it('can logout a user with default guard', function () {
    session_start();
    $_SESSION['app.sonata.web'] = ['user_id' => 1];
    $_SESSION['app.sonata.web2'] = ['user_id' => 1];
    session_write_close();

    app()->get(Authorization::class)->revoke();

    expect(app()->get(Authorization::class)->check())->toBeFalse();
    expect(app()->get(Authorization::class)->guard('web')->check())->toBeFalse();
    expect(app()->get(Authorization::class)->guard('web2')->check())->toBeTrue();
});

return;
it('should change the current guard in use', function () {
    $_SESSION['app.sonata.web'] = ['user_id' => null];
    $_SESSION['app.sonata.web2'] = ['user_id' => 1];

    app()->get(Authorization::class)->guard('web2');

    expect(app()->get(Authorization::class)->check())->toBeTrue();
    expect(app()->get(Authorization::class)->guard('web')->check())->toBeFalse();
    expect(app()->get(Authorization::class)->guard('web2')->check())->toBeTrue();
});

it('should throw an exception if the guard does not exist', function () {
    app()->get(Authorization::class)->guard('web3');
})->throws(InvalidArgumentException::class, 'The guard "web3" does not exist');

it('should throw an exception if the guard repository does not implement the IdentifiableInterface', function () {
    app()->config()->set('sonata.auth_guards', [
        'web' => [
            'driver'     => 'default',
            'repository' => SessionInterface::class,
        ],
    ]);

    app()->get(Authorization::class)->guard('web');
})->throws(InvalidArgumentException::class, 'The guard repository must implement ' . IdentifiableInterface::class);
