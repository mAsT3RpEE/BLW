<?php
/**
 * INI.php | Apr 27, 2014
 *
 * @filesource
 * @license MIT
 * @copyright Copyright (c) 2013-2018, mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 *
 * @package BLW\Core
 * @version GIT: 0.2.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model\Config;

use ArrayObject;
use BLW\Type\IFile;
use BLW\Type\IConfig;
use BLW\Model\FileException;

// @codeCoverageIgnoreStart
if (! defined('BLW')) {

    if (strstr($_SERVER['PHP_SELF'], basename(__FILE__))) {
        header("$_SERVER[SERVER_PROTOCOL] 404 Not Found");
        header('Status: 404 Not Found');

        $_SERVER['REDIRECT_STATUS'] = 404;

        echo "<html>\r\n<head><title>404 Not Found</title></head><body bgcolor=\"white\">\r\n<center><h1>404 Not Found</h1></center>\r\n<hr>\r\n<center>nginx/1.5.9</center>\r\n</body>\r\n</html>\r\n";
        exit();
    }

    return false;
}
// @codeCoverageIgnoreEnd


/**
 * `.ini` file configuration.
 *
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class INI extends \BLW\Type\AConfig
{
    /**
     * Contstructor
     *
     * @link http://www.php.net/manual/en/function.parse-ini-string.php parse_ini_string()
     *
     * @param \BLW\Type\IFile $Config
     *            INI file to load.
     * @param boolean $ProcessSections
     *            By setting the process_sections parameter to <code>TRUE</code> (default), you get a multidimensional array, with the section names and settings included.
     * @param integer $ScannerMode
     *           Can either be <code>INI_SCANNER_NORMAL</code> (default) or <code>INI_SCANNER_RAW</code>. If <code>INI_SCANNER_RAW</code> is supplied, then option values will not be parsed.
     */
    public function __construct(IFile $Config, $ProcessSections = true, $ScannerMode = INI_SCANNER_NORMAL)
    {
        // Is INI file unreadable? Exception.
        if (! $Config->isReadable()) {
            // Exception
            throw new FileException(strval($Config));
        }

        // Parse ini file
        $input = parse_ini_string($Config->getContents(), !!$ProcessSections, $ScannerMode) ?: array();

        // Set up class
        ArrayObject::__construct($input ?: array(), IConfig::FLAGS, IConfig::ITERATOR);
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
