<?php
/**
 * IInitializable.php | Feb 19, 2014
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
namespace BLW\Type;

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
 * Interface for all objects that can be initialized by IConfig.
 *
 * <h4>Note</h4>
 *
 * <p>This is generally a bad practice as initializing
 * a class state is something that generally should not
 * be handled withing the class. But you never know if
 * there will be a valid use so we made this.</p>
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+
 * | INITIALIZABLE                                     |
 * +---------------------------------------------------+
 * | initialize(): IConfig                             |
 * |                                                   |
 * | $Settings:  IConfig                               |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\Core
 * @api     BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
interface IInitializable
{

    /**
     * Initializes object.
     *
     * <h4>Warning</h4>
     *
     * <p>This is not a good way to program and should only be used when
     * unavoidable (ie. classes that must perform tests before use like SpaceRocket).</p>
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param \BLW\Type\IConfig $Config
     *            Settings to use during initialization.
     * @return \BLW\Type\IConfig Returns settings actually used during initialization.
     */
    public function initialize(IConfig $Config);
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
