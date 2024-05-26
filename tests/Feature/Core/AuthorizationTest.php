<?php

use Sonata\AuthDrivers\SessionDriver;
use Sonata\Authorization;
use Sonata\Interfaces\RepositoryInterface;
use Sonata\Interfaces\SessionInterface;
use Sonata\Interfaces\UserRepositoryInterface;
use Sonata\Providers\SessionProvider;
use Sonata\Testing\Doctrine;
use Tests\Entities\DoctrineUser as User;

beforeEach(function () {
	/**
	 * We do not need to use doctrine provider for this test
	 * but it will help to check if we are correctly retrieving
	 * the subject from the repository without mocking it.
	 */
	doctrineTest();
	app()->provider(SessionProvider::class);
	app()->config()->set('sonata.auth_guards', [
		'web' => [
			'driver'     => SessionDriver::class,
			'repository' => UserRepositoryInterface::class,
		],
		// Check if we can have multiple guards with same driver
		'web2' => [
			'driver'     => SessionDriver::class,
			'repository' => UserRepositoryInterface::class,
		],
	]);
});

it('should authenticate a subject with default guard', function () {
	$session = app()->get(SessionInterface::class);
	$subject = Doctrine::factory(User::class)[0];
	app()->get(Authorization::class)->authenticate($subject);

	expect(app()->get(Authorization::class)->subject())->toBe($subject);
	expect(app()->get(Authorization::class)->guard('web')->subject())->toBe($subject);
	expect(app()->get(Authorization::class)->guard('web2')->subject())->toBeNull();
	expect($session->get('guards.web.subject'))->toBe($subject->id);
});

it('can check if a subject is authenticated with default guard', function () {
	$subject = Doctrine::factory(User::class)[0];
	$_SESSION ??= [];
	$_SESSION['app.sonata.guards.web.subject'] = $subject->id;

	expect(app()->get(Authorization::class)->check())->toBeTrue();
	expect(app()->get(Authorization::class)->guard('web')->check())->toBeTrue();
	expect(app()->get(Authorization::class)->guard('web2')->check())->toBeFalse();
});

it('can logout a subject with default guard', function () {
	$subject = Doctrine::factory(User::class)[0];
	$_SESSION ??= [];
	$_SESSION['app.sonata.guards.web.subject'] = $subject->id;
	$_SESSION['app.sonata.guards.web2.subject'] = $subject->id;

	app()->get(Authorization::class)->revoke();

	expect(app()->get(Authorization::class)->check())->toBeFalse();
	expect(app()->get(Authorization::class)->guard('web')->check())->toBeFalse();
	expect(app()->get(Authorization::class)->guard('web2')->check())->toBeTrue();
});

it('should change the current guard in use', function () {
	$subject = Doctrine::factory(User::class)[0];
	$_SESSION ??= [];
	$_SESSION['app.sonata.guards.web.subject'] = null;
	$_SESSION['app.sonata.guards.web2.subject'] = $subject->id;

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
			'driver'     => User::class,
			'repository' => UserRepositoryInterface::class,
		],
	]);

	app()->get(Authorization::class)->guard('web');
})->throws(InvalidArgumentException::class, 'The guard driver must implement Sonata\Interfaces\AuthDriverInterface');

it('should throw an exception if the guard repository does not implement the UserRepositoryInterface', function () {
	app()->config()->set('sonata.auth_guards', [
		'web' => [
			'driver'     => SessionDriver::class,
			'repository' => User::class,
		],
	]);

	app()->get(Authorization::class)->guard('web');
})->throws(InvalidArgumentException::class, 'The guard repository must implement ' . RepositoryInterface::class);