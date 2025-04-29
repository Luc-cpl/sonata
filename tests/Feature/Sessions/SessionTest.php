<?php

use Sonata\Sessions\Interfaces\SessionInterface;
use Sonata\Sessions\SessionsProvider;

beforeEach(function () {
    app()->provider(SessionsProvider::class);
});

it('should get the session id', function () {
    session_id('test');
    $id = app()->get(SessionInterface::class)->id();
    expect($id)->toBe('test');
});

it('should set and get session data', function () {
    $session = app()->get(SessionInterface::class);
    $_SESSION['user_id'] = 1;
    expect($session->get('user_id'))->toBe(1);
});

it('should remove session data', function () {
    $session = app()->get(SessionInterface::class);
    $_SESSION['user_id'] = 1;
    $session->remove('user_id');
    expect($_SESSION['user_id'] ?? null)->toBeNull();
});

it('should check if session data exists', function () {
    $session = app()->get(SessionInterface::class);
    $_SESSION['user_id'] = 1;
    expect($session->has('user_id'))->toBeTrue();

    $session->remove('user_id');
    expect($session->has('user_id'))->toBeFalse();
});

it('should clear session data', function () {
    $session = app()->get(SessionInterface::class);
    $_SESSION['user_id'] = 1;
    $session->clear();
    expect($_SESSION['user_id'] ?? null)->toBeNull();
});

it('should commit session data', function () {
    session_start();
    $session = app()->get(SessionInterface::class);
    $session->set('user_id', 1);
    $session->commit();

    unset($_SESSION);
    session_start();
    expect($_SESSION['user_id'])->toBe(1);
});
