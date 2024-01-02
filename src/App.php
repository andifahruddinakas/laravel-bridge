<?php

namespace Akas\Bridge;

use Akas\Bridge\Concerns\Setup;
use Laravel\Lumen\Application as Lumen;

class App extends Lumen 
{
    use Setup;

    /**
     * The base path of the application installation.
     *
     * @var string
     */
    protected $basePath;

    /**
     * Create a new Lumen application instance.
     *
     * @param  string|null  $basePath
     * @return void
     */
    public function __construct($basePath = null, array $database = [])
    {
        $this->basePath = $basePath;

        $this->bootstrapContainer();
        // $this->registerErrorHandling();
        // $this->bootstrapRouter();
        $this->setupDatabase($database);
    }

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version()
    {
        return 'Bridge (2.0.0) (Lumen Components 8.*)';
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerSessionBindings()
    {
        $this->singleton('session', function () {
            return $this->loadComponent('session', 'Illuminate\Session\SessionServiceProvider', 'session');
        });
    }

    /**
     * Register the core container aliases.
     *
     * @return void
     */
    protected function registerContainerAliases()
    {
        $this->aliases = [
            \Illuminate\Contracts\Foundation\Application::class => 'app',
            \Illuminate\Contracts\Auth\Factory::class => 'auth',
            \Illuminate\Contracts\Auth\Guard::class => 'auth.driver',
            \Illuminate\Contracts\Cache\Factory::class => 'cache',
            \Illuminate\Contracts\Cache\Repository::class => 'cache.store',
            \Illuminate\Contracts\Config\Repository::class => 'config',
            \Illuminate\Config\Repository::class => 'config',
            \Illuminate\Container\Container::class => 'app',
            \Illuminate\Contracts\Container\Container::class => 'app',
            \Illuminate\Database\ConnectionResolverInterface::class => 'db',
            \Illuminate\Database\DatabaseManager::class => 'db',
            \Illuminate\Contracts\Encryption\Encrypter::class => 'encrypter',
            \Illuminate\Contracts\Events\Dispatcher::class => 'events',
            \Illuminate\Contracts\Filesystem\Factory::class => 'filesystem',
            \Illuminate\Contracts\Filesystem\Filesystem::class => 'filesystem.disk',
            \Illuminate\Contracts\Filesystem\Cloud::class => 'filesystem.cloud',
            \Illuminate\Contracts\Hashing\Hasher::class => 'hash',
            'log' => \Psr\Log\LoggerInterface::class,
            \Illuminate\Contracts\Queue\Factory::class => 'queue',
            \Illuminate\Contracts\Queue\Queue::class => 'queue.connection',
            \Illuminate\Redis\RedisManager::class => 'redis',
            \Illuminate\Contracts\Redis\Factory::class => 'redis',
            \Illuminate\Redis\Connections\Connection::class => 'redis.connection',
            \Illuminate\Contracts\Redis\Connection::class => 'redis.connection',
            'request' => \Illuminate\Http\Request::class,
            \Illuminate\Session\SessionManager::class => 'session',
            \Laravel\Lumen\Routing\Router::class => 'router',
            \Illuminate\Contracts\Translation\Translator::class => 'translator',
            \Laravel\Lumen\Routing\UrlGenerator::class => 'url',
            \Illuminate\Contracts\Validation\Factory::class => 'validator',
            \Illuminate\Contracts\View\Factory::class => 'view',
        ];
    }

    /**
     * The available container bindings and their respective load methods.
     *
     * @var array
     */
    public $availableBindings = [
        'auth' => 'registerAuthBindings',
        'auth.driver' => 'registerAuthBindings',
        \Illuminate\Auth\AuthManager::class => 'registerAuthBindings',
        \Illuminate\Contracts\Auth\Guard::class => 'registerAuthBindings',
        \Illuminate\Contracts\Auth\Access\Gate::class => 'registerAuthBindings',
        \Illuminate\Contracts\Broadcasting\Broadcaster::class => 'registerBroadcastingBindings',
        \Illuminate\Contracts\Broadcasting\Factory::class => 'registerBroadcastingBindings',
        \Illuminate\Contracts\Bus\Dispatcher::class => 'registerBusBindings',
        'cache' => 'registerCacheBindings',
        'cache.store' => 'registerCacheBindings',
        \Illuminate\Contracts\Cache\Factory::class => 'registerCacheBindings',
        \Illuminate\Contracts\Cache\Repository::class => 'registerCacheBindings',
        'composer' => 'registerComposerBindings',
        'config' => 'registerConfigBindings',
        'db' => 'registerDatabaseBindings',
        \Illuminate\Database\Eloquent\Factory::class => 'registerDatabaseBindings',
        'filesystem' => 'registerFilesystemBindings',
        'filesystem.cloud' => 'registerFilesystemBindings',
        'filesystem.disk' => 'registerFilesystemBindings',
        \Illuminate\Contracts\Filesystem\Cloud::class => 'registerFilesystemBindings',
        \Illuminate\Contracts\Filesystem\Filesystem::class => 'registerFilesystemBindings',
        \Illuminate\Contracts\Filesystem\Factory::class => 'registerFilesystemBindings',
        'encrypter' => 'registerEncrypterBindings',
        \Illuminate\Contracts\Encryption\Encrypter::class => 'registerEncrypterBindings',
        'events' => 'registerEventBindings',
        \Illuminate\Contracts\Events\Dispatcher::class => 'registerEventBindings',
        'files' => 'registerFilesBindings',
        'hash' => 'registerHashBindings',
        \Illuminate\Contracts\Hashing\Hasher::class => 'registerHashBindings',
        'log' => 'registerLogBindings',
        \Psr\Log\LoggerInterface::class => 'registerLogBindings',
        'queue' => 'registerQueueBindings',
        'queue.connection' => 'registerQueueBindings',
        \Illuminate\Contracts\Queue\Factory::class => 'registerQueueBindings',
        \Illuminate\Contracts\Queue\Queue::class => 'registerQueueBindings',
        'router' => 'registerRouterBindings',
        \Psr\Http\Message\ServerRequestInterface::class => 'registerPsrRequestBindings',
        \Psr\Http\Message\ResponseInterface::class => 'registerPsrResponseBindings',
        'session' => 'registerSessionBindings',
        'session.store' => 'registerSessionBindings',
        'translator' => 'registerTranslationBindings',
        'url' => 'registerUrlGeneratorBindings',
        'validator' => 'registerValidatorBindings',
        \Illuminate\Contracts\Validation\Factory::class => 'registerValidatorBindings',
        'view' => 'registerViewBindings',
        \Illuminate\Contracts\View\Factory::class => 'registerViewBindings',
    ];
}