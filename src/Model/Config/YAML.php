<?php
/**
 * YAML.php | Apr 27, 2014
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
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;

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
 * `.yaml` / `.yml` file configuration.
 *
 * @link https://github.com/symfony/Yaml Symfony/YML
 *
 * @package BLW\Core
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class YAML extends \BLW\Type\AConfig
{
    /**
     * YAML Encoder
     *
     * @var \Symfony\Component\Yaml\Dumper
     */
    private static $_Encoder = null;

    /**
     * YAML Decoder
     *
     * @var \Symfony\Component\Yaml\Parser
     */
    private static $_Decoder = null;

    /**
     * Contstructor
     *
     * @link http://www.php.net/manual/en/function.parse-ini-string.php parse_ini_string()
     *
     * @param \BLW\Type\IFile $Config
     *            YAML file to load.
     * @param integer $Options
     *            Bitmask of YAML decode options. (YAML_BIGINT_AS_STRING)
     */
    public function __construct(IFile $Config, $Options = 0)
    {
        // Is YAML file unreadable? Exception.
        if (! $Config->isReadable()) {
            // Exception
            throw new FileException(strval($Config));
        }

        // Decoder
        self::$_Decoder = self::$_Decoder ?: new Parser;

        // Parse yaml file
        try {
            $input = self::$_Decoder->parse($Config->getContents(), true) ?: array();

        // @codeCoverageIgnoreStart

        // Error?
        } catch (\Exception $e) {
            $input = array();

            trigger_error(sprintf('Unable to parse yaml file (%s): %s', $Config, $e->getMessage()), E_USER_WARNING);
        }

        // @codeCoverageIgnoreEnd

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
        // Encoder
        self::$_Encoder = self::$_Encoder ?: new Dumper;

        // Encode config
        return self::$_Encoder->dump(iterator_to_array($this), 2);
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
