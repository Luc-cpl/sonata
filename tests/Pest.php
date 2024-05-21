<?php

use Orkestra\Testing\AbstractTestCase;
use Sonata\Testing\Doctrine;
use Tests\Entities\DoctrineUser;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(AbstractTestCase::class)->in(__DIR__);

/*
|--------------------------------------------------------------------------
| Global Functions
|--------------------------------------------------------------------------
|
| Here you may define all of your global functions. These functions are
| loaded before the test suite is run, giving you a chance to define
| them before any of your tests have executed.
|
*/

function doctrineTest() {
	Doctrine::init();
	app()->config()->set('sonata.user_entity', DoctrineUser::class);
	app()->config()->set('doctrine.entities', fn () => [app()->config()->get('root') . '/tests/Entities']);
}
