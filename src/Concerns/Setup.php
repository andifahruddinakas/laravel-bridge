<?php

namespace Akas\Bridge\Concerns;

use PDO;
use Illuminate\Database\DatabaseServiceProvider;

trait Setup
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
}
