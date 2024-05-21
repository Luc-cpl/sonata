<?php

use Orkestra\Providers\HooksProvider;
use Sonata\Interfaces\SessionInterface;
use Sonata\Providers\SessionProvider;

beforeEach(function () {
	app()->provider(SessionProvider::class);
});

it('should auto start a session when retrieve from interface', function () {
	session_write_close();
	expect(app()->get(SessionInterface::class)->started())->toBeTrue();
});

it('should commit the session interface on `{app}.http.router.response.before` hook', function () {
	session_write_close();
	app()->provider(HooksProvider::class);
	$session = app()->get(SessionInterface::class);

	expect(app()->get(SessionInterface::class)->started())->toBeTrue();
	
	app()->hookCall('http.router.response.before');

	expect($session->started())->toBeFalse();
});