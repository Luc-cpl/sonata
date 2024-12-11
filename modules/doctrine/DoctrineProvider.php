<?php

namespace Sonata\Doctrine;

use Doctrine\Common\EventManager;
use Orkestra\App;
use Orkestra\Interfaces\ProviderInterface;
use Orkestra\Interfaces\ConfigurationInterface;
use Sonata\Doctrine\Listeners\FlushDoctrineData;
use Sonata\Doctrine\DoctrineListeners\TablePlaceholders;
use Symfony\Component\Console\Application;
use Doctrine\Migrations\Tools\Console\ConsoleRunner as MigrationsConsoleRunner;
use Doctrine\Migrations\Configuration\EntityManager\EntityManagerLoader;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\Configuration\Migration\ConfigurationLoader;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\DBAL\Tools\Console\ConnectionProvider;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\ConnectionFromManagerProvider;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Events;
use ReflectionClass;

class DoctrineProvider implements ProviderInterface
{
    /**
     * @var array<class-string>
     */
    public array $listeners = [
        FlushDoctrineData::class,
    ];

    public function register(App $app): void
    {
        $app->config()->set('validation', [
            'doctrine.migrations'         => fn ($value) => is_array($value) ? true : 'The migrations config must be an array',
            'doctrine.entities'           => fn ($value) => is_array($value) ? true : 'The entities config must be an array',
            'doctrine.prefix'             => fn ($value) => is_string($value) ? true : 'The prefix config must be a string',
            'doctrine.table.placeholders' => fn ($value) => is_array($value) ? true : 'The table placeholders config must be an array',
            'doctrine.connection'         => fn ($value) => is_array($value) ? true : 'The connection config must be an array',
        ]);

        $app->config()->set('definition', [
            'doctrine.migrations'         => ['Doctrine migrations namespace and directories (defaults to ["App\Migrations" => "./migrations"])', fn () => ['App\Migrations' => $app->config()->get('root') . '/migrations']],
            'doctrine.entities'           => ['Doctrine entities directories (defaults to ["app/Entities"])', ['app/Entities']],
            'doctrine.prefix'             => ['Doctrine table prefix (defaults to empty string)', ''],
            'doctrine.table.placeholders' => ['Doctrine table placeholders (defaults to empty array)', []],
            'doctrine.connection'         => ['Doctrine configuration (defaults to sqlite)', fn () => [
                'driver' => 'pdo_sqlite',
                'path'   => $app->config()->get('root') . '/db.sqlite',
            ]],
        ]);

        ListenersRegistry::register(Events::loadClassMetadata, TablePlaceholders::class);

        $app->decorate(Application::class, function (Application $cli, App $app) {
            $app->call(ConsoleRunner::class . '::addCommands', [$cli]);
            $dependencyFactory = $app->call(DependencyFactory::class . '::fromEntityManager');
            $app->call(MigrationsConsoleRunner::class . '::addCommands', [$cli, $dependencyFactory]);
            return $cli;
        });

        $app->bind(TablePlaceholders::class, TablePlaceholders::class)->constructor(
            placeholders: fn () => $app->config()->get('doctrine.table.placeholders'),
        );

        $app->bind(EntityManagerInterface::class, function (App $app, EntityRegistry $entityRegistry, ListenersRegistry $listenersRegistry) {
            /** @var string */
            $env = $app->config()->get('env');

            $entitiesPaths = $entityRegistry->getEntities();
            foreach ($entitiesPaths as &$path) {
                if (class_exists($path)) {
                    $filename = (new ReflectionClass($path))->getFileName();
                    $path = $filename ? dirname($filename) : $path;
                }
            }

            /** @var array<string, mixed> */
            $connectionConfig = $app->config()->get('doctrine.connection');

            $config = ORMSetup::createAttributeMetadataConfiguration(
                paths: $entitiesPaths,
                isDevMode: $env === 'development',
            );

            // @phpstan-ignore-next-line
            $connection = DriverManager::getConnection($connectionConfig, $config);

            $connection->getConfiguration()->setSchemaAssetsFilter(function (string $tableName) use ($app) {
                return str_starts_with($tableName, $app->config()->get('doctrine.prefix'));
            });

            $evm = new EventManager();

            $listeners = $listenersRegistry->getListeners();
            foreach ($listeners as $event => $listener) {
                $evm->addEventListener($event, $app->get($listener));
            }

            return new EntityManager($connection, $config, $evm);
        });

        $app->bind(EntityManagerProvider::class, SingleManagerProvider::class);
        $app->bind(ConnectionProvider::class, ConnectionFromManagerProvider::class);
        $app->bind(EntityManagerLoader::class, ExistingEntityManager::class);
        $app->bind(ConfigurationLoader::class, function (ConfigurationInterface $config) {
            return new ConfigurationArray([
                'table_storage' => [
                    'table_name' => 'migrations',
                    'version_column_name' => 'version',
                    'version_column_length' => 191,
                    'executed_at_column_name' => 'executed_at',
                    'execution_time_column_name' => 'execution_time',
                ],
                'migrations_paths' => $config->get('doctrine.migrations'),
                'all_or_nothing' => true,
            ]);
        });
    }

    public function boot(App $app): void
    {
        /** @var string[] */
        $entities = $app->config()->get('doctrine.entities');
        foreach ($entities as $entity) {
            EntityRegistry::register($entity);
        }
    }
}
