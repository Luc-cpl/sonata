<?php

use Sonata\Sessions\PHPSession;

it('should start a session', function () {
    expect(app()->get(PHPSession::class)->started())->toBeFalse();
    app()->get(PHPSession::class)->start();
    expect(app()->get(PHPSession::class)->started())->toBeTrue();
});

it('should commit a session', function () {
    app()->get(PHPSession::class)->start();
    app()->get(PHPSession::class)->flash('message', 'Hello, World!');
    app()->get(PHPSession::class)->commit();

    expect(app()->get(PHPSession::class)->started())->toBeFalse();
    expect($_SESSION['app.sonata'])->toBe(['flash' => ['message' => 'Hello, World!']]);
});

it('should get a flash message', function () {
    session_start();
    $_SESSION['app.sonata'] = ['flash' => ['message' => 'Hello, World!']];
    session_write_close();

    app()->get(PHPSession::class)->start();
    app()->get(PHPSession::class)->flash('message', 'Hello, World 2!');
    expect(app()->get(PHPSession::class)->getFlash('message'))->toBe('Hello, World!');

    app()->get(PHPSession::class)->commit();
    app()->get(PHPSession::class)->start();
    expect(app()->get(PHPSession::class)->getFlash('message'))->toBe('Hello, World 2!');
});

it('should set a session variable', function () {
    app()->get(PHPSession::class)->start();
    app()->get(PHPSession::class)->set('message', 'Hello, World!');
    expect(app()->get(PHPSession::class)->get('message'))->toBe('Hello, World!');
    app()->get(PHPSession::class)->commit();
    expect($_SESSION['app.sonata'])->toBe(['message' => 'Hello, World!']);
});

it('should get a session variable', function () {
    session_start();
    $_SESSION['app.sonata'] = ['message' => 'Hello, World!'];
    session_write_close();

    app()->get(PHPSession::class)->start();
    expect(app()->get(PHPSession::class)->get('message'))->toBe('Hello, World!');
});

it('should check if a session variable exists', function () {
    session_start();
    $_SESSION['app.sonata'] = ['message' => 'Hello, World!'];
    session_write_close();

    app()->get(PHPSession::class)->start();
    expect(app()->get(PHPSession::class)->has('message'))->toBeTrue();
    expect(app()->get(PHPSession::class)->has('message2'))->toBeFalse();
});

it('should remove a session variable', function () {
    $_SESSION['app.sonata'] = ['message' => 'Hello, World!'];

    app()->get(PHPSession::class)->start();
    app()->get(PHPSession::class)->remove('message');
    expect(app()->get(PHPSession::class)->has('message'))->toBeFalse();
});

it('should destroy a session', function () {
    session_start();
    $_SESSION['app.sonata'] = ['message' => 'Hello, World!'];
    $_SESSION['another'] = 'Hello, World!';
    session_write_close();

    app()->get(PHPSession::class)->start();
    // Ensure we do not commit the flash message
    app()->get(PHPSession::class)->flash('message', 'Hello, World!');
    app()->get(PHPSession::class)->destroy();

    expect(app()->get(PHPSession::class)->started())->toBeFalse();
    expect($_SESSION)->toBe(['another' => 'Hello, World!']);
});

it('should get all session variables', function () {
    session_start();
    $_SESSION['app.sonata'] = [
        'message' => 'Hello, World!',
        'message2' => 'Hello, World 2!',
    ];
    session_write_close();

    app()->get(PHPSession::class)->start();

    expect(app()->get(PHPSession::class)->all())->toBe([
        'message' => 'Hello, World!',
        'message2' => 'Hello, World 2!',
    ]);
});

it('should throw an exception if the session is not started', function ($call, $params) {
    app()->get(PHPSession::class)->$call(...$params);
})->with([
    ['get', ['message']],
    ['getFlash', ['message']],
    ['has', ['message']],
    ['remove', ['message']],
    ['destroy', []],
    ['all', []],
    ['set', ['message', 'Hello, World!']],
    ['flash', ['message', 'Hello, World!']],
    ['commit', []],
])->expectException(RuntimeException::class);

it('should get the session id', function () {
    app()->get(PHPSession::class)->start();
    expect(app()->get(PHPSession::class)->getId())->toBe(session_id());
    expect(strlen(app()->get(PHPSession::class)->getId()))->toBe(26);
});