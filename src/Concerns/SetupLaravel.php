<?php

namespace Akas\LaravelBridge\Concerns;

use PDO;
use Illuminate\Config\Repository;
use Illuminate\View\ViewServiceProvider;
use Illuminate\Cache\CacheServiceProvider;
use Illuminate\Session\SessionServiceProvider;
use Illuminate\Database\DatabaseServiceProvider;
use Illuminate\Encryption\EncryptionServiceProvider;
use Illuminate\Pagination\PaginationServiceProvider;
use Illuminate\Translation\TranslationServiceProvider;

trait SetupLaravel
{
    /**
     * setup user define provider
     *
     * @param callable $callable The callable can return the instance of ServiceProvider
     *
     * @return static
     */
    public function setupCallableProvider(callable $callable)
    {
        $serviceProvider = $callable($this->app);
        $serviceProvider->register();

        if (method_exists($serviceProvider, 'boot') === true) {
            $this->call([$serviceProvider, 'boot']);
        }

        return $this;
    }

    /**
     * @param string $configPath
     *
     * @return static
     */
    public function setupConfig()
    {
        $configPath = $this->app->configPath();
        
        $config = new Repository([
            'app'   => require $configPath . '/app.php',
            'cache' => require $configPath . '/cache.php',
            'session' => require $configPath . '/session.php',
            'view'  => require $configPath . '/view.php',
        ]);

        $this->app->instance('config', $config);
        
        $this->setupEncryption($config['app.key'], $config['app.cipher']);
        $this->setupView($config['view.paths'], $config['view.compiled']);
        $this->setupCache($config['cache.default'], $config['cache.stores.file']);
        $this->setupSession(
            $config['session.lottery'],
            $config['session.cookie'],
            $config['session.path'],
            $config['session.domain'],
            $config['session.driver'],
            $config['session.files']
        );



        return $this;
    }

    /**
     * @param array $connections
     * @param string $default
     * @param int $fetch
     *
     * @return static
     */
    public function setupDatabase(array $connections, $default = 'default', $fetch = PDO::FETCH_CLASS)
    {
        return $this->setupCallableProvider(function ($app) use ($connections, $default, $fetch) {
            $app['config']['database.connections'] = $connections;
            $app['config']['database.default'] = $default;
            $app['config']['database.fetch'] = $fetch;

            return new DatabaseServiceProvider($app);
        });
    }

    /**
     * @param string $locale
     *
     * @return static
     */
    public function setupLocale($locale)
    {
        $this->app['config']['app.locale'] = $locale;

        return $this;
    }

    /**
     * @return static
     */
    public function setupPagination()
    {
        return $this->setupCallableProvider(function ($app) {
            return new PaginationServiceProvider($app);
        });
    }

    /**
     * @param string $langPath
     *
     * @return static
     */
    public function setupTranslator($langPath)
    {
        return $this->setupCallableProvider(function ($app) use ($langPath) {
            $app->instance('path.lang', $langPath);

            return new TranslationServiceProvider($app);
        });
    }

    /**
     * @param string|array $viewPath
     * @param string $compiledPath
     *
     * @return static
     */
    public function setupView($viewPath, $compiledPath)
    {
        return $this->setupCallableProvider(function ($app) use ($viewPath, $compiledPath) {
            $app['config']['view.paths'] = is_array($viewPath) ? $viewPath : [$viewPath];
            $app['config']['view.compiled'] = $compiledPath;

            return new ViewServiceProvider($app);
        });
    }

    /**
     * @param string $key
     * @param string $cipher
     * 
     * @return static
     */
    public function setupEncryption($key, $cipher = 'AES-128-CBC')
    {
        return $this->setupCallableProvider(function ($app) use ($key, $cipher) {
            $app['config']['app.key'] = $key;
            $app['config']['app.cipher'] = $cipher;

            return new EncryptionServiceProvider($app);
        });
    }

    /**
     * @param string $default
     * 
     * @return static
     */
    public function setupCache($default, $file)
    {
        return $this->setupCallableProvider(function ($app) use ($default, $file) {
            $app['config']['cache.default'] = $default;
            $app['config']['cache.stores.file.path'] = $file;

            return new CacheServiceProvider($app);
        });
    }

    /**
     * @param string $default
     * 
     * @return static
     */
    public function setupSession($lottery, $cookie, $path, $domain, $driver, $files)
    {
        return $this->setupCallableProvider(function ($app) use ($lottery, $cookie, $path, $domain, $driver, $files) {
            $app['config']['session.lifetime'] = $lottery;
            $app['config']['session.cookie'] = $cookie;
            $app['config']['session.path'] = $path;
            $app['config']['session.domain'] = $domain;
            $app['config']['session.driver'] = $driver;
            $app['config']['session.files'] = $files;

            return new SessionServiceProvider($app);
        });
    }

    /**
     * @param string|mixed $provider
     * @return static
     */
    public function bootProvider($provider)
    {
        $provider = $this->app->getProvider($provider);

        if (method_exists($provider, 'boot')) {
            $this->app->call([$provider, 'boot']);
        }

        return $this;
    }
}
