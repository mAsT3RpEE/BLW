<?php
/**
 * IOutput.php | Mar 30, 2014
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
 * @package BLW\Command
 * @version GIT 0.2.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type\Command;

use BLW\Type\IMediatable;


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

/**
 * Interface for command output objects passed to ICommand::run()
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +------------+
 * | COMMAND\OUTPUT                                    |<------| MEDIATABLE |
 * +---------------------------------------------------+       +------------+
 * | STDOUT                                            |
 * | STDERR                                            |
 * +---------------------------------------------------+
 * | _OutStream:  IStream                              |
 * | _ErrStream:  IStream                              |
 * | #stdOut      _OutStream                           |
 * | #stdErr      _ErrStream                           |
 * +---------------------------------------------------+
 * | setMediatorID: IDataMapper::STATUS                |
 * |                                                   |
 * | $ID:  string                                      |
 * +---------------------------------------------------+
 * | write(): fwrite(_OutStream->fp)                   |
 * |          fwrite(_ErrStream->fp)                   |
 * |                                                   |
 * | $string:  string                                  |
 * | $flags:   IOutput::WRITE_FLAGS                    |
 * +---------------------------------------------------+
 * | overwrite(): write($string)                       |
 * |                                                   |
 * | $string:  string                                  |
 * | $flags:   IOutput::WRITE_FLAGS                    |
 * +---------------------------------------------------+
 * | __toString():  string                             |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\Command
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property \BLW\Type\IStream $_OutStream [protected] Output stream.
 * @property \BLW\Type\IStream $_ErrStream [protected] Error stream.
 * @property \BLW\Type\IStream $stdOut [readonly] $_Outstream
 * @property \BLW\Type\IStream $stdErr [readonly] $_Errstream
 */
interface IOutput extends \BLW\Type\IMediatable
{
    // WRITE FLAGS
    const STDOUT      = 0x002;
    const STDERR      = 0x004;
    const WRITE_FLAGS = 0x002;

    /**
     * Used to change mediator id of Output to attatch its actions to its command at runtime.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param string $ID
     *            Unique id used to identify object in mediator.
     * @return int Returns a <code>DataMapper</code> status code.
     */
    public function setMediatorID($ID);

    /**
     * Write to Output stream.
     *
     * <h4>Note</h4>
     *
     * <p>Function is also responsible for dispatching onOutput hook.</p>
     *
     * <hr>
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param string $string
     *            Data to write.
     * @param int $flags
     *            Write flags.
     * @return int Bytes written. <code>FALSE</code> on Error.
     */
    public function write($string, $flags = IOutput::WRITE_FLAGS);

    /**
     * Rewrite to Output stream (replace last write).
     *
     * @api BLW
     * @since 1.0.0
     * @see \BLW\Type\Command\IOutput::write() IOutput::write()
     *
     * @param string $string
     *            Data to write.
     * @param int $flags
     *            Write flags.
     * @return int Bytes written. <code>FALSE</code> on Error.
     */
    public function overwrite($string, $flags = IOutput::WRITE_FLAGS);

    /**
     * All objects must have a string representation.
     *
     * @api BLW
     * @since 1.0.1
     * @link http://www.php.net/manual/en/language.oop5.magic.php#object.tostring __toString()
     *
     * @return string $this
     */
    public function __toString();
}

return true;
