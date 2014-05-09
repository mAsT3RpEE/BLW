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

if (! defined('BLW')) {

    /**
     * BLW Version.
     *
     * <h4>Note</h4>
     *
     * <p>Used to check for inclution of autoload.php</p>
     *
     * <hr>
     *
     * @var string
     */
    define('BLW', '0.2.0');
}

/**
 * PHP configuration / initialization
 */
if (version_compare(PHP_VERSION, '5.3.3', '<')) {
    throw new RuntimeException('PHP 5.3.3 or higher required for BLW Library to run');
}

/**
 * Global constants
 */
if (! defined('BLW_PHAR')) {

    /**
     * Constant pointing to path of BLW.phar file or root build directory.
     *
     * @var string
     */
    define('BLW_PHAR', dirname(dirname(__FILE__)));
}

// @codeCoverageIgnoreStart
if (! defined('BLW_DIR')) {

    if (preg_match('!\x2ephar$!i', BLW_PHAR)) {

        /**
         * Constant pointing to BLW Plugin dir where BLW.phar and config are located.
         *
         * @var string
         */
        define('BLW_DIR', dirname(BLW_PHAR) . DIRECTORY_SEPARATOR);

    } else {

        /**
         * Constant pointing to BLW Plugin dir where BLW.phar and config are located.
         *
         * @var string
         */
        define('BLW_DIR', BLW_PHAR . DIRECTORY_SEPARATOR);
    }
}

// @codeCoverageIgnoreEnd


/**
 * Autoloader
 */
require_once BLW_PHAR . '/vendor/autoload.php';

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
