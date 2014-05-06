<?php
/**
 * Message.php | Jan 20, 2013
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
namespace BLW\Model\Mail\MIME;

use ReflectionMethod;

use BLW\Type\MIME\IMessage;

use BLW\Model\GenericContainer;
use BLW\Model\GenericEmailAddress;
use BLW\Model\InvalidArgumentException;
use BLW\Model\MIME\MIMEVersion;
use BLW\Model\MIME\Date;
use BLW\Model\MIME\Subject;
use BLW\Model\MIME\Section;
use BLW\Model\MIME\Head\RFC822 as Head;
use BLW\Model\MIME\Body\RFC822 as Body;


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
 * RFC822 MIME formated Email Message.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +------------------+
 * | Message                                           |<------| FACTORY          |
 * +---------------------------------------------------+       | ================ |
 * | _Head                                             |       | createMessage    |
 * | _Body                                             |       | createFromString |
 * +---------------------------------------------------+       +------------------+
 * | createMessage(): Mail\IMessage                    |
 * +---------------------------------------------------+
 * | createFromString(): IMessage                      |
 * +---------------------------------------------------+
 * | getHeader(): IHead                                |
 * +---------------------------------------------------+
 * | getBody(): IBody                                  |
 * +---------------------------------------------------+
 * | __toString(): string                              |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\Mail
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class Message extends \BLW\Type\MIME\AMessage
{

#############################################################################################
# Factory Trait
#############################################################################################

    /**
     * Return an array of factory methods associated with the class.
     *
     * @return \ReflectionMethod[] Array of factory methods.
     */
    public static function getFactoryMethods()
    {
        return array(
            new ReflectionMethod(get_called_class(), 'createMessage'),
            new ReflectionMethod(get_called_class(), 'createFromString')
        );
    }

    /**
     * Generate a \BLW\Mail\GenericMessage object from current class.
     *
     * @api BLW
     * @since 1.0.0
     * @todo Everything.
     *
     * @return \BLW\Type\Mail\IMessage Generated message.
     */
    public function createMessage()
    {
        throw new \RuntimeException('Not supported yet');
    }

    /**
     * Parse an address header into its corresponding Mime\Header.
     *
     * @uses \BLW\Model\GenericContainer GenericContainer.
     *
     * @param string $Header
     *            Header Class.
     * @param array $List
     *            Array of email addresses from <code>imap_rfc822_parse_headers</code>.
     * @return \BLW\Model\MIME\IHeader Generated Header.
     */
    private static function _parseAddressList($Header, $List)
    {
        // Validate header
        $Class = '\\BLW\\Model\\MIME\\' . ucwords(strtolower($Header));

        // Does header class exist?
        if (class_exists($Class)) {

            // Parse addresses
            $AddressList = new GenericContainer(IMessage::EMAIL);

            foreach ($List as $Mail) {

                $Email = GenericEmailAddress::createEmailString(array(
                    'Local' => isset($Mail->mailbox) ? $Mail->mailbox : '',
                    'Domain' => isset($Mail->host) ? $Mail->host : ''
                ));

                $Personal = isset($Mail->personal) ? $Mail->personal : '';
                $AddressList[] = new GenericEmailAddress($Email, $Personal);
            }

            // Create to MIME header
            return new $Class($AddressList);
        }
    }

    /**
     * Convert a string to a MimeMessage
     *
     * @todo Parse mime body.
     * @link http://www.php.net/manual/en/function.imap-rfc822-parse-headers.php imap_rfc822_parse_headers()
     * @link http://www.php.net/manual/en/function.imap-rfc822-parse-addrlist.php imap_rfc833_parse_addrlist()
     *
     * @param string $String
     *            String version of message.
     * @return \BLW\Model\MIMEMessage Genereted message.
     */
    public static function createFromString($String)
    {
        $DefaultHost = @$_SERVER['HTTP_HOST'] ?  : @$_SERVER['SERVER_NAME'] ?  : @$_SERVER['SERVER_ADDR'] ?  : '0.0.0.0';
        $Message     = new Message('1.0');

        // Is $string valid?
        if (is_string($String) ?  : is_callable(array(
            $String,
            '__toString'
        ))) {

            // Parse Headers
            foreach (imap_rfc822_parse_headers($String, $DefaultHost) as $header => $value)
                switch ($header) {

                    case 'to':
                    case 'from':
                    case 'replyto':
                    case 'cc':
                    case 'bcc':

                        // Add to header
                        $this->getHeader()->append($this->_parseAddressList($header, $value));

                    case 'date':

                        // Add to header
                        $this->getHeader()->append(new Date(new \DateTime($value)));

                    case 'subject':

                        // Add to header
                        $this->getHeader()->append(new Subject($value));
                }

            // Parse body
            throw new \RuntimeException('Not supported yet');
        }

        // Invalid $String
        else
            throw new InvalidArgumentException(0);

        // Done
        return $Message;
    }

#############################################################################################
# MimeMessage Trait
#############################################################################################

    /**
     * Constructor
     *
     * @param string $Version
     *            Mime version.
     * @param string $Section
     *            Content-Type of body (multipart/mixed, multipart/alternative).
     */
    public function __construct($Version, $Section = 'multipart/mixed')
    {
        // Header
        $this->_Head = new Head(new MIMEVersion($Version), new Section($Section));

        // Body
        $this->_Body = new Body($this->_Head->getSection());
    }

#############################################################################################

}

return true;
