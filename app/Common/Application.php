<?php
/**
 * Application.php | May 4, 2014
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
 * @package BLW\LIbrary
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace Common;

if (! defined('BLW')) {

    if (strstr($_SERVER['PHP_SELF'], basename(__FILE__))) {
        header("$_SERVER[SERVER_PROTOCOL] 404 Not Found");
        header('Status: 404 Not Found');

        $_SERVER['REDIRECT_STATUS'] = 404;

        echo "<html>\r\n<head><title>404 Not Found</title></head><body bgcolor=\"white\">\r\n<center><h1>404 Not Found</h1></center>\r\n<hr><center>nginx/1.5.9</center>\r\n</body>\r\n</html>\r\n";
        exit();
    }

    return false;
}

use BLW\Type\Command\IInput;
use BLW\Type\Command\IOutput;

use BLW\Model\Config;
use BLW\Model\GenericFile;
use BLW\Model\Mediator\Symfony as Mediator;
use BLW\Model\Command\Callback as CallbackCommand;
use BLW\Model\Command\Input\stdInput;
use BLW\Model\Command\Output\stdOutput;

use Monolog\Logger;


/**
 * Wrapper for BLW Library console commands.
 *
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
abstract class Application
{

    /**
     * Application configuration.
     *
     * @var \BLW\Type\IConfig $CFG
     */
    private static $CFG = array();

    /**
     * Application event handler
     *
     * @var \BLW\Type\IMediator $Mediator
     */
    private static $Mediator = NULL;

    /**
     * Application logger.
     *
     * @var \Psr\Logger\LoggerInterface $Logger
     */
    private static $Logger = NULL;

    /**
     * Configures the application.
     */
    public static function configure()
    {
        // Configuration
        self::$CFG = new Config\Generic(array(
            'SYS' => new Config\YAML(new GenericFile(BLW_DIR . 'config/system.yml'))
        ));

        // Mediator
        self::$Mediator = new Mediator();

        // Logger
        self::$Logger = new Logger('Application', array(
            new \Monolog\Handler\StreamHandler(BLW_DIR . 'temp/BLW.log', Logger::DEBUG)
        ));
    }

    /**
     * Runs the application.
     *
     * @param callback $Callback
     *            Callback to call during running.
     * @param \BLW\Type\Command\IInput $Input
     *            Command input.
     * @param \BLW\Type\Command\IOutput $Output
     *            Command output.
     * @return int <code>NULL</code> or 0 if everything went fine. Error code otherwise.
     */
    public static function run($Callback, IInput $Input = null, IOutput $Output = null)
    {
        // Input and output
        $Input  = $Input  ?: new stdInput();
        $Output = $Output ?: new stdOutput();
        $Start  = microtime(true);

        // Run console
        self::$Logger->info(sprintf('Started console app. %s.', date('c')));

        $Command = new CallbackCommand($Callback, new Config\Generic(array(
            'Description' => 'BLW library console command',
            'Timeout'     => 10,
            'Logger'      => self::$Logger,

        )), self::$Mediator, 'build');

        $return = $Command->run($Input, $Output);

        // Done
        self::$Logger->info(sprintf('Finished console app. Total time: %01.2f seconds.', microtime(true) - $Start));

        return $return;
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
