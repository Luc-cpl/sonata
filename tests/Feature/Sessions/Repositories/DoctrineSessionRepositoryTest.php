<?php

use Sonata\Repositories\Interfaces\RepositoryInterface;
use Sonata\Sessions\Entities\Session;
use Sonata\Sessions\Repositories\DoctrineSessionRepository;
use Sonata\Sessions\SessionsProvider;
use Tests\Entities\DoctrineUser;

beforeEach(function () {
    doctrineTest();
    app()->provider(SessionsProvider::class);
});

it('should get updated data before a certain time', function () {
    $repository = app()->get(DoctrineSessionRepository::class);
    factory()->times(3)->create(Session::class, updatedAt: new DateTime('-1 hour'));
    factory()->times(2)->create(Session::class, updatedAt: new DateTime('-2 hours'));

    $count = $repository->updatedBefore(new DateTime('-1 hour'))->count();
    expect($count)->toBe(2);
});

it('should persist data in a separate manager instance', function () {
    $repository = app()->get(DoctrineSessionRepository::class);
    $userRepository = app()->get(RepositoryInterface::class);

    $session = factory()->make(Session::class);
    factory()->create(DoctrineUser::class);

    $repository->persist($session);

    expect($userRepository->count())->toBe(0);
    expect($repository->count())->toBe(1);
});

it('should retrieve data by driver', function () {
    $repository = app()->get(DoctrineSessionRepository::class);
    factory()->times(3)->create(Session::class, driver: 'php');
    factory()->times(2)->create(Session::class, driver: 'test');

    $count = $repository->whereDriver('php')->count();
    expect($count)->toBe(3);
});

it('should retrieve data by user ID', function () {
    $repository = app()->get(DoctrineSessionRepository::class);
    factory()->times(3)->create(Session::class, userId: 1);
    factory()->times(2)->create(Session::class, userId: 2);

    $count = $repository->whereUserId(1)->count();
    expect($count)->toBe(3);
});

it('should delete a session', function () {
    $repository = app()->get(DoctrineSessionRepository::class);
    $session = factory()->create(Session::class);
    expect($repository->count())->toBe(1);
    $repository->delete($session);
    expect($repository->count())->toBe(0);
});
