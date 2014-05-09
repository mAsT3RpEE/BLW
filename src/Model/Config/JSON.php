<?php
/**
 * JSON.php | Apr 27, 2014
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
 * `.json` file configuration.
 *
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class JSON extends \BLW\Type\AConfig
{
    /**
     * Contstructor
     *
     * @link http://www.php.net/manual/en/function.parse-ini-string.php parse_ini_string()
     *
     * @param \BLW\Type\IFile $Config
     *            JSON file to load.
     * @param integer $Options
     *            Bitmask of JSON decode options. (JSON_BIGINT_AS_STRING)
     */
    public function __construct(IFile $Config, $Options = 0)
    {
        // Default data
        $input = array();

        // Is JSON file unreadable? Exception.
        if (!$Config->isReadable()) {
            // Exception
            throw new FileException(strval($Config));

        // JSON is readable?
        } else {

            // Parse json file
            $json = $Config->getContents();
            $json = substr($json, strpos($json, '{'), strrpos($json, '}') + 1);

            if (!empty($json)) {

                // @codeCoverageIgnoreStart

                $input = version_compare(PHP_VERSION, '5.4.0', '>=')
                    ? json_decode($json, true, 20, $Options)
                    : json_decode($json, true, 20);

                // @codeCoverageIgnoreEnd
            }
        }

        // Set up class
        ArrayObject::__construct($input, IConfig::FLAGS, IConfig::ITERATOR);
    }

    /**
     * All objects must have a string representation.
     *
     * @return string $this
     */
    public function __toString()
    {
        return json_encode(iterator_to_array($this), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
