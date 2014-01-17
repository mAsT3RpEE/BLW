<?php

// Try PHAR file
if(is_file(dirname(__DIR__) . '/build/BLW.phar')) {
    define('BLW_PLUGIN_DIR', dirname(__DIR__) . '/build');
    require_once 'phar://' . BLW_PLUGIN_DIR . '/BLW.phar/src/bootstrap.php';
}

// Source library testing
else {
    define('BLW_PLATFORM',    'standalone');
    define('BLW_PLUGIN_DIR',  dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app');
    define('BLW_PLUGIN_URL',  'http:://localhost/BLW/app');
    define('BLW_LIB_PHAR',    dirname(__DIR__));
    require_once BLW_LIB_PHAR . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'bootstrap.php';
}

// Server
date_default_timezone_set('UTC');

// Database setup
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Config/PHPUNIT.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/php-activerecord/php-activerecord/test/helpers/DatabaseLoader.php';

\ActiveRecord\Config::initialize(function($Config)
{
    // Setup connections
    $Tempfile = str_replace('\\','/',tempnam(sys_get_temp_dir(), 'db_'));
    $Config->set_model_directory(realpath(__DIR__ . '/Config'));
    $Config->set_connections(array(
         'mysql'  => getenv('PHPAR_MYSQL')  ?: 'mysql://test:test@127.0.0.1/test'
        ,'pgsql'  => getenv('PHPAR_PGSQL')  ?: 'pgsql://test:test@127.0.0.1/test'
        ,'sqlite' => getenv('PHPAR_SQLITE') ?: sprintf(
             'sqlite://%s(%s)'
            ,strpos($Tempfile, ':/')? 'windows':'unix'
            ,str_replace(':', urlencode(':'), $Tempfile))
        )
    );

    $Config->set_default_connection('sqlite');

    // Parse argv
    for ($i=0; $i<count($GLOBALS['argv']); ++$i) {
        if ($GLOBALS['argv'][$i] == '--adapter') {
            $Config->set_default_connection($GLOBALS['argv'][$i+1]);
            break;
        }
    }

    try {
        $Connection = \ActiveRecord\ConnectionManager::get_connection();
        $Loader     = new \DatabaseLoader($Connection);
    }

    catch (\ActiveRecord\DatabaseException $e) {
        print sprintf(
             'sqlite://%s(%s)'
            ,strpos($Tempfile, ':/')? 'windows':'unix'
            ,str_replace(':', urlencode(':'), $Tempfile)
        );
        trigger_error($Config->get_default_connection() . ' failed to connect. '. $e->getMessage(), E_USER_ERROR);
    }

    unset($Loader, $Connection);

    register_shutdown_function(function()
    {
        \ActiveRecord\ConnectionManager::drop_connection('mysql');
        \ActiveRecord\ConnectionManager::drop_connection('pgsql');
        \ActiveRecord\ConnectionManager::drop_connection('sqlite');

        $Dir = sys_get_temp_dir();

        foreach (scandir($Dir) as $File) {
            $Absolute = $Dir . DIRECTORY_SEPARATOR . $File;

            if (preg_match('/db_[\w]{3,6}[.]tmp/', $File)) {
            	@unlink($Absolute);
            }
        }
    });
});

error_reporting(E_ALL ^ E_STRICT);