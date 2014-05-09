<?php
/**
 * Mail.php | Jan 22, 2013
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

use BLW\Type\Mail\ITransport;
use BLW\Type\Mail\IMessage;
use BLW\Model\ErrorException;
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
 * mail() invoker transport.
 *
 * @package BLW\Mail
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class Mail extends \BLW\Type\Mail\ATransport
{

    /**
     * Does the actual work of sending the message.
     *
     * @param \BLW\Model\MIME\Message $Message
     *            Message to send.
     * @return int ITransport::RESULT_FLAGS
     */
    public function doSend(IMessage $Message)
    {
        $MimeMessage = $Message->createMimeMessage();
        $To = array();
        
        // Gather recipients
        iterator_apply($this->parseRecipients($Message), function ($i) use(&$To)
        {
            
            // Is current entry an email address?
            if (($Address = $i->current()) instanceof IEmailAddress) {
                
                // Add it to recipients
                $To[] = strval($Address);
            }
        });
        
        $To = implode(', ', $To);
        
        // Try to send message
        if (@mail($To, $Message->getSubject(), strval($MimeMessage->getBody()), strval($MimeMessage->getHeader()))) {
            
            // Success
            return ITransport::SUCCESS;
        }         

        // Error
        else
            throw new ErrorException(error_get_last());
            
            // Failure
        return ITransport::ERROR;
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
