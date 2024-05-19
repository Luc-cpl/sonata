<?php

use Tests\TestCases\DoctrineTestCase;

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

uses(DoctrineTestCase::class)->in(
	__DIR__ . '/Feature/Doctrine/',
	__DIR__ . '/Feature/Providers/DoctrineProviderTest.php',
);
