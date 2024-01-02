<?php

if (!function_exists('bridge')) {
    /**
     * Get the available container instance.
     *
     * @param  string  $basePath
     * @return \Akas\Bridge\App
     */
    function bridge($basePath = null, $connections = [])
    {
        return (new Akas\Bridge\App($basePath, connect($connections)));
    }
}

// Connect to database
if (!function_exists('connect')) {
    /**
     * Connect to database
     *
     * @param array $db
     * @return array
     */
    function connect(array $db)
    {
        $connections = [];
        foreach ($db as $key => $options) {
            $dbdriver = array_get($options, 'dbdriver');
            $dbdriver = ($dbdriver === 'mysqli') ? 'mysql' : $dbdriver;
            $connections[$key] = [
                'driver'    => $dbdriver,
                'host'      => array_get($options, 'hostname'),
                'port'      => array_get($options, 'port', 3306),
                'database'  => array_get($options, 'database'),
                'username'  => array_get($options, 'username'),
                'password'  => array_get($options, 'password'),
                'charset'   => array_get($options, 'char_set'),
                'collation' => array_get($options, 'dbcollat'),
                'prefix'    => array_get($options, 'swap_pre'),
                'strict'    => array_get($options, 'stricton'),
                'engine'    => null,
            ];
        }

        return $connections;
    }
}

if (!function_exists('session')) {
    /**
     * Get / set the specified session value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string|null  $key
     * @param  mixed  $default
     * @return mixed|\Illuminate\Session\Store|\Illuminate\Session\SessionManager
     */
    function session($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('session');
        }

        if (is_array($key)) {
            return app('session')->put($key);
        }

        return app('session')->get($key, $default);
    }
}