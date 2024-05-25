<?php

use Orkestra\Providers\HooksProvider;
use Sonata\Interfaces\UserRepositoryInterface;
use Sonata\Testing\Doctrine;
use Tests\Entities\DoctrineUser as User;


beforeEach(function () {
	doctrineTest();
});

it('should be able to create a user', function () {
	$repository = app()->get(UserRepositoryInterface::class);

	$user = factory()->make(User::class,  password: 'password');
	$repository->add($user);
	Doctrine::flush();

	expect($user->password)->not->toBe('password');
	expect(password_verify('password', $user->password))->toBeTrue();

	$foundUser = Doctrine::find(User::class, $user->id);

	expect($user->id)->toBeInt();
	expect($foundUser->id)->toBe($user->id);
	expect($foundUser->email)->toBe($user->email);
	expect($foundUser->password)->toBe($user->password);
});

it('should be able to update a user', function () {
	$user = Doctrine::factory(User::class)[0];

	$user->set(email: 'test@email.com');

	/**
	 * The managed entity should be updated
	 * when the entity manager is flushed.
	 */
	Doctrine::flush();
	$foundUser = Doctrine::find(User::class, $user->id);

	expect($foundUser->email)->toBe('test@email.com');
});

describe('listeners', function () {
	beforeEach(function () {
		app()->provider(HooksProvider::class);
	});

	it('persists data on "http.router.response.before" hook', function () {
		$repository = app()->get(UserRepositoryInterface::class);
		$user = factory()->make(User::class);

		$repository->add($user);

		expect(isset($user->id))->toBeFalse();

		app()->hookCall('http.router.response.before');

		expect(isset($user->id))->toBeTrue();
	});
});

it('should be able to delete a user', function () {
	$user = Doctrine::factory(User::class)[0];
	$id = $user->id;

	$repository = app()->get(UserRepositoryInterface::class);
	$repository->remove($user);

	Doctrine::flush();

	expect($id)->toBeInt();
	expect(isset($user->id))->toBeFalse();
	expect(Doctrine::find(User::class, $id))->toBeNull();
});

it('should be able to find a user by email', function () {
	$users = Doctrine::factory(User::class, 10);

	$repository = app()->get(UserRepositoryInterface::class);
	$foundUser = $repository->whereEmail($users[0]->email)->first();

	expect($foundUser->email)->toBe($users[0]->email);

	$usersEmails = array_map(fn ($user) => $user->email, $users);
	$usersEmails = array_slice($usersEmails, 0, 5);

	$foundUsers = $repository->whereEmail($usersEmails)->getIterator();
	$foundUsers = iterator_to_array($foundUsers);
	$foundUsers = array_map(fn ($user) => $user->email, $foundUsers);

	sort($usersEmails);
	sort($foundUsers);

	expect(count($foundUsers))->toBe(5);
	expect($foundUsers)->toBe($usersEmails);
});

it('should be able to find a user by id', function () {
	$users = Doctrine::factory(User::class, 10);

	$repository = app()->get(UserRepositoryInterface::class);
	$foundUser = $repository->whereId($users[0]->id)->first();

	expect($foundUser->id)->toBe($users[0]->id);

	$usersIds = array_map(fn ($user) => $user->id, $users);
	$usersIds = array_slice($usersIds, 0, 5);

	$foundUsers = $repository->whereId($usersIds)->getIterator();
	$foundUsers = iterator_to_array($foundUsers);
	$foundUsers = array_map(fn ($user) => $user->id, $foundUsers);

	sort($usersIds);
	sort($foundUsers);

	expect(count($foundUsers))->toBe(5);
	expect($foundUsers)->toBe($usersIds);
});

it('should be able to paginate users', function () {
	$users = Doctrine::factory(User::class, 10);

	$repository = app()->get(UserRepositoryInterface::class);
	$foundUsers = $repository->slice(0, 5)->getIterator();
	$foundUsers = iterator_to_array($foundUsers);
	$foundUsers = array_map(fn ($user) => $user->email, $foundUsers);

	expect(count($foundUsers))->toBe(5);

	$usersEmails = array_map(fn ($user) => $user->email, $users);
	$usersEmails = array_slice($usersEmails, 0, 5);

	sort($usersEmails);
	sort($foundUsers);

	expect($foundUsers)->toBe($usersEmails);
});

it('should be able to count users', function () {
	Doctrine::factory(User::class, 10);
	$repository = app()->get(UserRepositoryInterface::class);

	$count = $repository->count();
	expect($count)->toBe(10);
});

it('should be able slice and count users', function () {
	Doctrine::factory(User::class, 10);
	$repository = app()->get(UserRepositoryInterface::class);

	$count = count($repository->slice(0, 5)->getIterator());
	expect($count)->toBe(5);

	$count = count($repository->slice(8, 5)->getIterator());
	expect($count)->toBe(2);

	$count = $repository->slice(0, 5)->count();
	expect($count)->toBe(5);

	$count = $repository->slice(8, 5)->count();
	expect($count)->toBe(2);
});