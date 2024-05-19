<?php

use Orkestra\Providers\CommandsProvider;
use Sonata\Providers\DoctrineProvider;
use Symfony\Component\Console\Application;

beforeEach(function () {
	app()->provider(CommandsProvider::class);
});

it('should insert maestro console command', function () {
	$console = app()->get(Application::class);
	$commands = $console->all();

	expect($commands)->toHaveKey('orm:info');
});