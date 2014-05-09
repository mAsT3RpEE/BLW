<?php
/**
 * IMailable.php | Mar 08, 2014
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
 * +---------------------------------------------------+
 * | MAILABLE                                          |
 * +---------------------------------------------------+
 * | sendWith(): IMailer::Status                       |
 * |                                                   |
 * | $Transport:  IMailer                              |
 * +---------------------------------------------------+
 * | createMimeMessage(): IMimeMessage                 |
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
interface IMailable extends \BLW\Type\IFactory
{

    /**
     * Send object via a mail transport.
     *
     * <h3>Introduction</h3>
     *
     * <p><code>sendWidth()</code> should call <code>createMimeMail()</code>
     * factory method which should create an <code>Mime\IMessage</code> object
     * which can be sent via transport</code>
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param \BLW\Type\Mail\ITransport $Transport
     *            Transport to use to send message.
     * @param int $flags
     *            Transport flags.
     * @return int Returns a status of <code>IMailer::send()</code>.
     */
    public function sendWith(ITransport $Transport, $flags = ITransport::SEND_FLAGS);

    /**
     * Generate a Mail\MimeMessage object from current class.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return \BLW\Model\MIMEMessage Generated message.
     */
    public function createMimeMessage();
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
