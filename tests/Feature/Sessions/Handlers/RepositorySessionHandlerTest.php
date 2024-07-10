<?php

use Doctrine\ORM\EntityManagerInterface;
use Sonata\Authorization\Authorization;
use Sonata\Authorization\AuthorizationProvider;
use Sonata\Repositories\Interfaces\RepositoryInterface;
use Sonata\Sessions\Entities\Session;
use Sonata\Sessions\Handlers\RepositorySessionHandler;
use Sonata\Sessions\Repositories\DoctrineSessionRepository;
use Sonata\Sessions\SessionDrivers;
use Sonata\Sessions\SessionsProvider;
use Tests\Entities\DoctrineUser;

beforeEach(function () {
	doctrineTest();
	app()->provider(SessionsProvider::class);
});

it('should return true from close method', function () {
	/** @var RepositorySessionHandler */
	$handler = app()->get('sessions.handler.doctrine');
	expect($handler->close())->toBeTrue();
});

it('should return true from open method', function () {
	/** @var RepositorySessionHandler */
	$handler = app()->get('sessions.handler.doctrine');
	expect($handler->open('', ''))->toBeTrue();
});

it('should delete a session', function () {
	/** @var RepositorySessionHandler */
	$handler = app()->get('sessions.handler.doctrine');
	$repository = app()->get(DoctrineSessionRepository::class);
	$session = factory()->create(Session::class);
	expect($repository->count())->toBe(1);
	$handler->destroy($session->id);
	expect($repository->count())->toBe(0);
});

it('should garbage collect sessions', function () {
	/** @var RepositorySessionHandler */
	$handler = app()->get('sessions.handler.doctrine');
	$repository = app()->get(DoctrineSessionRepository::class);
	factory()->times(3)->create(Session::class, updatedAt: new DateTime('-1 hour'));
	factory()->times(2)->create(Session::class, updatedAt: new DateTime('-2 hours'));

	$count = $handler->gc(3600);
	expect($count)->toBe(2);
	expect($repository->count())->toBe(3);
});

it('should garbage collect sessions by driver', function () {
	/** @var RepositorySessionHandler */
	$handler = app()->get('sessions.handler.doctrine');
	$repository = app()->get(DoctrineSessionRepository::class);
	$drivers = app()->get(SessionDrivers::class);
	$drivers->use('php');
	factory()->times(3)->create(Session::class, updatedAt: new DateTime('-1 hour'), driver: 'php');
	factory()->times(2)->create(Session::class, updatedAt: new DateTime('-2 hours'), driver: 'test');
	factory()->times(2)->create(Session::class, updatedAt: new DateTime('-2 hours'), driver: 'php');

	$count = $handler->gc(3600);
	expect($count)->toBe(2);
	expect($repository->count())->toBe(5);
});

it('should read a session', function () {
	/** @var RepositorySessionHandler */
	$handler = app()->get('sessions.handler.doctrine');
	$session = factory()->create(Session::class, data: 'test');
	$data = $handler->read($session->id);
	expect($data)->toBe('test');

	$data = $handler->read('invalid');
	expect($data)->toBe('');
});

it('should write a session', function () {
	/** @var RepositorySessionHandler */
	$handler = app()->get('sessions.handler.doctrine');
	$session = factory()->create(Session::class);
	$handler->write($session->id, 'test');
	$session = app()->get(DoctrineSessionRepository::class)->get($session->id);
	expect($session->data)->toBe('test');

	$handler->write('new-session', 'test');
	$session = app()->get(DoctrineSessionRepository::class)->get('new-session');
	expect($session->data)->toBe('test');
});

it('should write a session with a driver', function () {
	/** @var RepositorySessionHandler */
	$handler = app()->get('sessions.handler.doctrine');
	$drivers = app()->get(SessionDrivers::class);
	$drivers->use('php');
	$handler->write('new-session', 'test');
	$session = app()->get(DoctrineSessionRepository::class)->get('new-session');
	expect($session->driver)->toBe('php');
});

it('should save a session with the user id', function () {
	app()->provider(AuthorizationProvider::class);

	app()->config()->set('sonata.default_guard', 'web');
	app()->config()->set('sonata.auth_guards', [
		'web' => [
			'driver' => 'doctrine',
			'repository' => RepositoryInterface::class,
		],
	]);

	/** @var RepositorySessionHandler */
	$handler = app()->get('sessions.handler.doctrine');
	$authorization = app()->get(Authorization::class);
	$user = factory()->create(DoctrineUser::class);
	app()->get(EntityManagerInterface::class)->flush();

	$authorization->guard('web')->authenticate($user);
	$session = factory()->create(Session::class);
	$handler->write($session->id, 'test');
	$session = app()->get(DoctrineSessionRepository::class)->get($session->id);
	expect($session->userId)->toBe(1);

	$authorization->guard('web')->revoke();
	$handler->write($session->id, 'test');
	$session = app()->get(DoctrineSessionRepository::class)->get($session->id);
	expect($session->userId)->toBeNull();
});