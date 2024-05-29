<?php

use Orkestra\Providers\CommandsProvider;
use Symfony\Component\Console\Application;

it('should insert maestro console command', function () {
    doctrineTest();
    app()->provider(CommandsProvider::class);
    $console = app()->get(Application::class);
    $commands = $console->all();
    /**
     * The diff only appears when the migration
     * is configured to work with the ORM.
     */
    expect($commands)->toHaveKey('orm:info');
    expect($commands)->toHaveKey('migrations:diff');
});
