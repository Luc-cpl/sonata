<?php

use Symfony\Component\Console\Application;

it('should insert maestro console command', function () {
	doctrineTest();
	$console = app()->get(Application::class);
	$commands = $console->all();

	expect($commands)->toHaveKey('orm:info');
});