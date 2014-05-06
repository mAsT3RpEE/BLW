<?php
/**
 * Mock.php | Jan 22, 2013
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
namespace BLW\Model\Mail\Transport;

use BLW\Type\Mail\IMessage;
use BLW\Type\Mail\ITransport;
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
 * Mock transport.
 *
 * @package BLW\Mail
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class Mock extends \BLW\Type\Mail\ATransport
{

    /**
     * Recipients of last message.
     *
     * @var \BLW\Type\IContainer $_Recipients
     */
    protected $_Recipients = '';

    /**
     * Header of last message.
     *
     * @var \BLW\Type\IContainer $_Header
     */
    protected $_Header = '';

    /**
     * Body of last message.
     *
     * @var \BLW\Type\IContainer $_Receipients
     */
    protected $_Body = '';

    /**
     * Does the actual work of sending the message.
     *
     * @param \BLW\Model\MIME\Message $Message
     *            Message to send.
     * @return int ITransport::RESULT_FLAGS
     */
    public function doSend(IMessage $Message)
    {
        // Convert Message to MIME format
        if (is_callable(array(
            $Message,
            'createMimeMessage'
        ))) {

            $MimeMessage = $Message->createMimeMessage();

            $this->_Recipients = $this->parseRecipients($Message);
            $this->_Header = $MimeMessage->getHeader();
            $this->_Body = $MimeMessage->getBody();

            return ITransport::SUCCESS;
        }

        // Invalid $VARIABLE
        return ITransport::ERROR;
    }

    /**
     * Get message receipients.
     *
     * @return \BLW\Type\IContainer Message Receipients.
     */
    function getReceipients()
    {
        return $this->_Recipients;
    }

    /**
     * Get MIME header of message.
     *
     * @return \BLW\Model\MIME\Head MIME head.
     */
    function getMimeHead()
    {
        return $this->_Header;
    }

    /**
     * Get MIME body of message
     *
     * @return \BLW\Model\MIME\Body MIME body.
     */
    function getMimeBody()
    {
        return $this->_Body;
    }
}

return true;
