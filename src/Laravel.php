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
use Illuminate\Support\Facades\DB;

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
        // 'App' => App::class,
        // 'Arr' => Arr::class,
        // 'Artisan' => Artisan::class,
        // 'Auth' => Auth::class,
        // 'Blade' => Blade::class,
        // 'Broadcast' => Broadcast::class,
        // 'Bus' => Bus::class,
        // 'Cache' => Cache::class,
        // 'Config' => Config::class,
        // 'Cookie' => Cookie::class,
        // 'Crypt' => Crypt::class,
        // 'Date' => Date::class,
        // 'DB' => DB::class,
        // 'Eloquent' => Model::class,
        // 'Event' => Event::class,
        // 'File' => File::class,
        // 'Gate' => Gate::class,
        // 'Hash' => Hash::class,
        // 'Http' => Http::class,
        // 'Js' => Js::class,
        // 'Lang' => Lang::class,
        // 'Log' => Log::class,
        // 'Mail' => Mail::class,
        // 'Notification' => Notification::class,
        // 'Number' => Number::class,
        // 'Password' => Password::class,
        // 'Process' => Process::class,
        // 'Queue' => Queue::class,
        // 'RateLimiter' => RateLimiter::class,
        // 'Redirect' => Redirect::class,
        // 'Request' => Request::class,
        // 'Response' => Response::class,
        // 'Route' => Route::class,
        // 'Schema' => Schema::class,
        // 'Session' => Session::class,
        // 'Storage' => Storage::class,
        // 'Str' => Str::class,
        // 'URL' => URL::class,
        // 'Validator' => Validator::class,
        'View' => View::class,
        // 'Vite' => Vite::class,
    ];

    /**
     * @var App
     */
    private $app;

    /**
     * @var bool
     */
    private $bootstrapped = false;

    public function __construct()
    {
        $this->app = new App(FCPATH);
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
     * Flash instance
     */
    public static function flashInstance()
    {
        $instance = static::getInstance();

        $instance->flush();
        $instance->bootstrapped = false;
    }
}
