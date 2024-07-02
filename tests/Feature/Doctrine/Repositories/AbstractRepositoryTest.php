<?php

use Orkestra\Providers\HooksProvider;
use Sonata\Interfaces\RepositoryInterface;
use Sonata\Testing\Doctrine;
use Tests\Entities\DoctrineUser as User;

beforeEach(function () {
    doctrineTest();
});

it('should be able to create a user', function () {
    $repository = app()->get(RepositoryInterface::class);

    $user = factory()->make(User::class);
    $repository->persist($user);
    Doctrine::flush();

    /** @var User */
    $foundUser = Doctrine::find(User::class, $user->id);

    expect($user->id)->toBeInt();
    expect($foundUser->id)->toBe($user->id);
});

it('should be able to update a user', function () {
    $user = Doctrine::factory(User::class)[0];

    $user->set(value: 'new value');

    /**
     * The managed entity should be updated
     * when the entity manager is flushed.
     */
    Doctrine::flush();

    /** @var User */
    $foundUser = Doctrine::find(User::class, $user->id);

    expect($foundUser->value)->toBe('new value');
});

describe('listeners', function () {
    beforeEach(function () {
        app()->provider(HooksProvider::class);
    });

    it('persists data on "http.router.response.before" hook', function () {
        $repository = app()->get(RepositoryInterface::class);
        $user = factory()->make(user::class);

        $repository->persist($user);

        expect($user->id)->toBeNull();

        app()->hookCall('http.router.response.before');

        expect($user->id)->toBeInt();
    });
});

it('should be able to delete a user', function () {
    $user = Doctrine::factory(user::class)[0];
    $id = $user->id;

    $repository = app()->get(RepositoryInterface::class);
    $repository->delete($user);

    Doctrine::flush();

    expect($id)->toBeInt();
    expect($user->id)->toBeNull();
    expect(Doctrine::find(user::class, $id))->toBeNull();
});

it('should be able to find a user by id', function () {
    $users = Doctrine::factory(user::class, 10);

    $repository = app()->get(RepositoryInterface::class);
    $foundUsers = $repository->get($users[0]->id);

    expect($foundUsers->id)->toBe($users[0]->id);
});

it('should be able to paginate users', function () {
    $users = Doctrine::factory(user::class, 10);

    $repository = app()->get(RepositoryInterface::class);
    $foundUsers = $repository->slice(0, 5)->getIterator();
    $foundUsers = iterator_to_array($foundUsers);
    $foundUsers = array_map(fn ($user) => $user->value, $foundUsers);

    expect(count($foundUsers))->toBe(5);

    $usersEmails = array_map(fn ($user) => $user->value, $users);
    $usersEmails = array_slice($usersEmails, 0, 5);

    sort($usersEmails);
    sort($foundUsers);

    expect($foundUsers)->toBe($usersEmails);
});

it('should be able to count users', function () {
    Doctrine::factory(user::class, 10);
    $repository = app()->get(RepositoryInterface::class);

    $count = $repository->count();
    expect($count)->toBe(10);
});

it('should be able slice and count users', function () {
    Doctrine::factory(user::class, 10);
    $repository = app()->get(RepositoryInterface::class);

    $count = count($repository->slice(0, 5)->getIterator());
    expect($count)->toBe(5);

    $count = count($repository->slice(8, 5)->getIterator());
    expect($count)->toBe(2);

    $count = $repository->slice(0, 5)->count();
    expect($count)->toBe(5);

    $count = $repository->slice(8, 5)->count();
    expect($count)->toBe(2);
});
