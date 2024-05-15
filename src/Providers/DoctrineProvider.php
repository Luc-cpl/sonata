<?php

namespace Sonata\Providers;

use Orkestra\App;
use Sonata\Listeners\FlushDoctrineData;
use Symfony\Component\Console\Application;
use Doctrine\DBAL\Tools\Console\ConnectionProvider;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\ConnectionFromManagerProvider;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Sonata\Repositories\Doctrine\UserRepository;
use Sonata\Repositories\Interfaces\UserRepositoryInterface;

class DoctrineProvider extends AbstractProvider
{
	public array $listeners = [
		FlushDoctrineData::class,
	];

	public function register(App $app): void
	{
		parent::register($app);

		$app->config()->set('validation', [
			'doctrine.entities' => fn ($value) => is_array($value) ? true : 'The entities config must be an array',
			'doctrine.connection' => fn ($value) => is_array($value) ? true : 'The connection config must be an array',
		]);

		$app->config()->set('definition', [
			'doctrine.entities' => ['Doctrine entities directory (defaults to [app/Entities])', fn () => [$app->config()->get('root') . '/app/Entities']],
			'doctrine.connection' => ['Doctrine configuration (defaults to sqlite)', fn () => [
				'driver' => 'pdo_sqlite',
				'path' => $app->config()->get('root') . '/db.sqlite',
			]],
		]);

		$app->decorate(Application::class, function ($cli) use ($app) {
			$app->call(ConsoleRunner::class . '::addCommands', [$cli]);
			return $cli;
		});

		$app->bind(EntityManagerInterface::class, function () use ($app) {
			/** @var string */
			$env = $app->config()->get('env');

			/** @var string[] */
			$entitiesPaths = $app->config()->get('doctrine.entities');

			/** @var class-string */
			$userEntity = $app->config()->get('sonata.user_entity');

			$entitiesPaths[] = dirname((new \ReflectionClass($userEntity))->getFileName());

			/** @var array<string, mixed> */
			$connectionConfig = $app->config()->get('doctrine.connection');

			$config = ORMSetup::createAttributeMetadataConfiguration(
				paths: $entitiesPaths,
				isDevMode: $env === 'development',
			);

			$connection = DriverManager::getConnection($connectionConfig, $config);

			return new EntityManager($connection, $config);
		});

		$app->bind(EntityManagerProvider::class, SingleManagerProvider::class);
		$app->bind(ConnectionProvider::class, ConnectionFromManagerProvider::class);
		$app->bind(UserRepositoryInterface::class, function (EntityManagerInterface $manager) use ($app) {
			return new UserRepository($manager, $app->config()->get('sonata.user_entity'));
		});
	}
}