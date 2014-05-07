<?php
/**
 * ITransport.php | Mar 08, 2014
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
 * @package BLW\Mail
 * @version GIT 0.2.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type\Mail;

use BLW\Type\IMediator;
use BLW\Type\IEmailAddress;

use BLW\Model\GenericContainer;
use BLW\Model\Event\Generic as GenericEvent;


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
 * Standard interface for objects that handle email Addresses.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +------------+
 * | TRANSPORT                                         |<------| MEDIATOR   |
 * +---------------------------------------------------+       +------------+
 * | SUCCESS                                           |
 * | ERROR                                             |
 * | NO_CONNECTION                                     |
 * | INVALID_RECIPIENT                                 |
 * +---------------------------------------------------+
 * | parseRecipients(): IContainer                     |
 * |                                                   |
 * | $Message:  IMessage                               |
 * +---------------------------------------------------+
 * | send(): ITransport::doSend()                      |
 * |                                                   |
 * | $Message:  IMessage                               |
 * | $flags:    ITransport::MAIL_FLAGS                 |
 * +---------------------------------------------------+
 * | doSend(): ITransport::RESULT_FLAGS                |
 * |                                                   |
 * | $Message:  IMessage                               |
 * +---------------------------------------------------+
 * | __toString(): string                              |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\Core
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
interface ITransport extends \BLW\Type\IMediatable
{
    // MAIL FLAGS
    const MAIL_FLAGS = 0x0000;

    // RESULT FLAGS
    const SUCCESS           = 0x002;
    const ERROR             = 0x004;
    const NO_CONNECTION     = 0x008;
    const INVALID_RECIPIENT = 0x010;

    /**
     * Parse an instance of IMessage for receipients
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param \BLW\Model\Mail\IMessage $Message
     *            Message to parse.
     * @return \BLW\Type\IContainer Parsed receipients.
     */
    public static function parseRecipients(IMessage $Message);

    /**
     * Prepares a message for transport inform mediators and calls doSend().
     *
     * @api BLW
     * @since 1.0.0
     * @uses \BLW\Model\Mail\Transport\ITransport::doSend()
     *
     * @param \BLW\Model\MIME\Message $Message
     *            Message to send.
     * @param int $flags
     *            ITransport::MAIL_FLAGS
     * @return int ITransport::doSend()
     */
    public function send(IMessage $Message, $flags = ITransport::MAIL_FLAGS);

    /**
     * Does the actual work of sending the message.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param \BLW\Model\MIME\Message $Message
     *            Message to send.
     * @return int ITransport::RESULT_FLAGS
     */
    public function doSend(IMessage $Message);

    /**
     * All objects must have a string representation.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return string $this
     */
    public function __toString();
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd