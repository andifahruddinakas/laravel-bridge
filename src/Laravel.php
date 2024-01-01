<?php

namespace Akas\LaravelBridge;

use Exception;
use BadMethodCallException;
use Illuminate\Http\Request;
use Illuminate\Log\LogManager;
use Illuminate\Support\Fluent;
use Illuminate\Events\Dispatcher;
use Illuminate\Cache\CacheManager;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\View;
use Illuminate\Filesystem\Filesystem;
use Psr\Container\ContainerInterface;
use Illuminate\Support\Facades\Facade;
use Akas\LaravelBridge\Concerns\SetupLaravel;
use Illuminate\Container\Container as LaravelContainer;
use Akas\LaravelBridge\Exceptions\EntryNotFoundException;

/**
 * @mixin LaravelContainer
 */
class Laravel implements ContainerInterface
{
    use SetupLaravel;
    // use SetupTracy;

    /**
     * @var static
     */
    public static $instance;

    /**
     * @var array
     */
    public $aliases = [
        'View' => View::class,
    ];

    /**
     * @var App
     */
    private $app;

    /**
     * @var string
     */
    public static array $config;

    /**
     * @var bool
     */
    private $bootstrapped = false;

    public function __construct()
    {
        $this->app = new App(self::$config['basePath']);
    }

    public function __call($method, $arguments)
    {
        if (method_exists($this->app, $method)) {
            return call_user_func_array([$this->app, $method], $arguments);
        }

        throw new BadMethodCallException("Undefined method '$method'");
    }

    public function bootstrap()
    {
        $this->bootstrapped = true;

        $this->app->singleton('request', function () {
            return Request::capture();
        });

        $this->app->singleton('config', Fluent::class);

        $this->app->singleton('files', Filesystem::class);

        $this->app->singleton('filesystem', function () {
            return new \Illuminate\Filesystem\FilesystemManager($this->app);
        });

        $this->app->singleton('cache', function () {
            return new CacheManager($this->app);
        });

        $this->app->singleton('events', Dispatcher::class);

        $this->app->singleton('encrypter', function () {
            return new Encrypter($this->app->config['app.key'], $this->app->config['app.cipher']);
        });

        $this->app->singleton('log', function () {
            $log = new LogManager($this->app);
            $log->driver()->getLogger()->pushHandler(new \Monolog\Handler\StreamHandler(storage_path('logs/laravel.log'), \Monolog\Logger::DEBUG));

            return $log;
        });

        $this->app->singleton('session', function () {
            $session = new \Illuminate\Session\SessionManager($this->app);
            $session->driver('file')->start();

            return $session;
        });


        Facade::setFacadeApplication($this->app);

        foreach ($this->aliases as $alias => $class) {
            if (!class_exists($alias)) {
                class_alias($class, $alias);
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isBootstrapped()
    {
        return $this->bootstrapped;
    }

    /**
     * {@inheritDoc}
     */
    public function has($id)
    {
        return $this->app->bound($id);
    }

    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        try {
            return $this->app->make($id);
        } catch (Exception $e) {
            if ($this->has($id)) {
                throw $e;
            }

            throw new EntryNotFoundException("Entry '$id' is not found");
        }
    }

    /**
     * getApp.
     *
     * @method getApp
     *
     * @return LaravelContainer
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->app->make('request');
    }

    /**
     * @return Dispatcher
     */
    public function getEvents()
    {
        return $this->app->make('events');
    }

    /**
     * @return Fluent
     */
    public function getConfig()
    {
        return $this->app->make('config');
    }

    /**
     * @param bool $is
     *
     * @return static
     */
    public function setupRunningInConsole($is = true)
    {
        $this->app['runningInConsole'] = $is;

        return $this;
    }

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        if (!static::$instance->isBootstrapped()) {
            static::$instance->bootstrap();
        }

        return static::$instance;
    }

    /**
     * @return static
     */
    public static function run(array $config = [])
    {
        if ($config) {
            static::$config = $config;
        }

        $instance = static::getInstance()->setupConfig();

        if ($config['database']) {
            $instance->setupDatabase($config['database']);
        }

        return $instance;
    }

    /**
     * Flash instance
     */
    public static function flashInstance()
    {
        $instance = static::getInstance();

        $instance->flush();
        $instance->bootstrapped = false;
    }
}
