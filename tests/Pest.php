<?php

use Doctrine\ORM\EntityManagerInterface;
use Orkestra\Testing\AbstractTestCase;
use Sonata\Doctrine\Repositories\AbstractRepository;
use Sonata\Interfaces\RepositoryInterface;
use Sonata\Testing\Doctrine;
use Tests\Entities\DoctrineSubject;

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

function doctrineTest()
{
    Doctrine::init();
    app()->config()->set('doctrine.entities', fn () => [DoctrineSubject::class]);
    app()->bind(RepositoryInterface::class, function (EntityManagerInterface $manager) {
        return new class ($manager) extends AbstractRepository {
            public function __construct(EntityManagerInterface $manager)
            {
                parent::__construct($manager, DoctrineSubject::class);
            }
        };
    });
}
