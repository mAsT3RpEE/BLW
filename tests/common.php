<?php
// Try PHAR file
if(is_file(dirname(__DIR__) . '/build/BLW.phar')) {

    define('BLW_PLUGIN_DIR',    dirname(__DIR__) . '/build');
    require_once 'phar://' . BLW_PLUGIN_DIR . '/BLW.phar/inc/common.php';
}

// Source library testing
else {
    define('BLW_PLATFORM',      'standalone');
    define('BLW_PLUGIN_DIR',    dirname(__DIR__) . '/controller');
    define('BLW_PLUGIN_URL',    'http:://localhost/BLW/controller');
    define('BLW_LIB_PHAR',      dirname(__DIR__));

    require_once dirname(__DIR__) . '/inc/common.php';
}