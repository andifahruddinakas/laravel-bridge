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
}