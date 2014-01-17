#!/usr/bin/env php
<?php
/**
 * run.php | Dec 16, 2013
 *
 * Copyright (c) mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 *
 * @filesource
 * @copyright mAsT3RpEE's Zone
 * @license MIT
 */

// Manual Configuration
define('BLW_PLUGIN_DIR',  __DIR__ . DIRECTORY_SEPARATOR . 'app');
define('BLW_LIB_PHAR',    __DIR__);

// Bootstrap
require_once BLW_LIB_PHAR . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'bootstrap.php';

use \BLW\Control\Console\Symfony as Control;
use \BLW\Frontend\Console\Symfony as View;

// Application
BLW::LoadView('Console.Symfony');
BLW::LoadControl('Console.Symfony');
BLW::GetControl()->doAction(Control::DEFAULT_ACTION);
BLW::GetView()->Render();
exit;