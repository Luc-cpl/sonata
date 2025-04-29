<?php

use Orkestra\Providers\HttpProvider;
use Sonata\Sessions\SessionDrivers;
use Sonata\Sessions\SessionsProvider;

beforeEach(function () {
    app()->provider(HttpProvider::class);
    app()->provider(SessionsProvider::class);
});

it('should isolate session data', function () {
    SessionDrivers::register('test', SessionHandler::class);
    $sessions = app()->get(SessionDrivers::class);

    $sessions->use('php');
    $_SESSION['user_id'] = 1;

    $sessions->use('test');
    $_SESSION['user_id_2'] = 2;

    expect($sessions->current())->toBe('test');
    expect($_SESSION['user_id'] ?? null)->toBeNull();
    expect($_SESSION['user_id_2'])->toBe(2);

    $sessions->use('php');
    expect($sessions->current())->toBe('php');
    expect($_SESSION['user_id'])->toBe(1);
    expect($_SESSION['user_id_2'] ?? null)->toBeNull();
});

it('should throw an exception if driver is not registered', function () {
    $sessions = app()->get(SessionDrivers::class);
    $sessions->use('non-existent');
})->throws(RuntimeException::class, 'Session driver "non-existent" not registered.');

it('should reactivate the current session if is closed', function () {
    $sessions = app()->get(SessionDrivers::class);
    $sessions->use('php');

    expect($sessions->current())->toBe('php');
    expect(session_status())->toBe(PHP_SESSION_ACTIVE);

    session_write_close();
    expect(session_status())->toBe(PHP_SESSION_NONE);

    $sessions->use('php');
    expect(session_status())->toBe(PHP_SESSION_ACTIVE);
});

it('should initialize the session driver with a Bearer token', function () {
    $key = hash('xxh32', 'php');
    $id = session_create_id($key);
    $_SERVER['HTTP_AUTHORIZATION'] = "Bearer {$id}";

    SessionDrivers::register('php', SessionHandler::class, [
        'use_only_cookies' => false,
    ]);

    $sessions = app()->get(SessionDrivers::class);
    $sessions->use('php');

    expect(session_id())->toBe($id);
});

it('should initialize a session driver with a cookie', function () {
    $key = hash('xxh32', 'php');
    $id = session_create_id($key);
    $_COOKIE["{$key}_session"] = $id;

    $sessions = app()->get(SessionDrivers::class);
    $sessions->use('php');
    expect(session_id())->toBe($id);
});

it('should not initialize a session driver with a Bearer token if the driver not supports it', function () {
    $key = hash('xxh32', 'php');
    $id = session_create_id($key);
    $_SERVER['HTTP_AUTHORIZATION'] = "Bearer {$id}";

    $sessions = app()->get(SessionDrivers::class);
    $sessions->use('php');

    expect(session_id())->not()->toBe($id);
});

it('should not initialize a session driver with a cookie if the driver not supports it', function () {
    $key = hash('xxh32', 'php');
    $id = session_create_id($key);
    $_COOKIE["{$key}_session"] = $id;

    SessionDrivers::register('php', SessionHandler::class, ['use_cookies' => false ]);
    $sessions = app()->get(SessionDrivers::class);
    $sessions->use('php');

    expect(session_id())->not()->toBe($id);
});

it('should get the session definition', function () {
    SessionDrivers::register('test', SessionHandler::class, ['use_cookies' => false ]);
    $phpDefinition = SessionDrivers::definition('php');
    $testDefinition = SessionDrivers::definition('test');

    expect($phpDefinition)->toBe([
        'handler' => SessionHandler::class,
        'options' => [],
    ]);

    expect($testDefinition)->toBe([
        'handler' => SessionHandler::class,
        'options' => ['use_cookies' => false ],
    ]);

    expect(SessionDrivers::definition('non-existent'))->toBeNull();

    // Should not expose instance and id after session start
    $sessions = app()->get(SessionDrivers::class);
    $sessions->use('php');

    $phpDefinition = SessionDrivers::definition('php');
    expect($phpDefinition)->toBe([
        'handler' => SessionHandler::class,
        'options' => [],
    ]);
});
