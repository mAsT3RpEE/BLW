<?php

define('BLW_PLATFORM',		'standalone');
define('BLW_PLUGIN_DIR',	dirname(__DIR__) . '/build');
define('BLW_PLUGIN_URL',	'http:://localhost/BLW/build');
define('BLW_LIB_PHAR',		'phar://' . dirname(__DIR__) . '/build/BLW.phar');
define('BLW_ASSETS_DIR', 	BLW_PLUGIN_DIR . '/assets');
define('BLW_ASSETS_URL', 	BLW_PLUGIN_URL . '/assets');

include dirname(__DIR__) . '/inc/common.php';