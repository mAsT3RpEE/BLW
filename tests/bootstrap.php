<?php
/**
 * bootstrap.php | Apr 4, 2014
 *
 * @filesource
 * @license MIT
 * @copyright Copyright (c) 2013-2018, mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define('BLW', 'test');
define('BLW_DIR', dirname(dirname(__FILE__)));

require_once BLW_DIR . str_replace('/', DIRECTORY_SEPARATOR, '/vendor/autoload.php');

error_reporting(E_ALL);