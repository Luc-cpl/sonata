<?php

use Orkestra\Providers\CommandsProvider;
use Symfony\Component\Console\Application;

it('should insert maestro console command', function () {
    doctrineTest();
    app()->provider(CommandsProvider::class);
    $console = app()->get(Application::class);
    $commands = $console->all();

    expect($commands)->toHaveKey('orm:info');
    expect($commands)->toHaveKey('migrations:migrate');
});
