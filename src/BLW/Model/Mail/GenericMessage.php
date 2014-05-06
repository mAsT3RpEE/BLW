<?php
/**
 * GenericMessage.php | Mar 08, 2014
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
namespace BLW\Model\Mail;

use DOMDocument;
use ReflectionMethod;

use BLW\Type\IDataMapper;
use BLW\Type\IMediator;
use BLW\Type\IObject;
use BLW\Type\IMediatable;
use BLW\Type\IEvent;
use BLW\Type\IContainer;
use BLW\Type\IEmailAddress;
use BLW\Type\IFile;
use BLW\Type\Mail\ITransport;
use BLW\Type\Mail\IMessage;

use BLW\Model\GenericContainer;
use BLW\Model\FileException;
use BLW\Model\InvalidArgumentException;
use BLW\Model\ClassException;
use BLW\Model\MIME\To;
use BLW\Model\MIME\From;
use BLW\Model\MIME\Date;
use BLW\Model\MIME\Subject;
use BLW\Model\MIME\Section;
use BLW\Model\MIME\ReplyTo;
use BLW\Model\MIME\CC;
use BLW\Model\MIME\BCC;
use BLW\Model\MIME\Part\QuotedPrintable;
use BLW\Model\MIME\Part\InlineAttachment;
use BLW\Model\MIME\Part\Attachment;
use BLW\Model\Mail\MIME\Message as MIMEMessage;


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
 * Standard interface for objects that handle email Addresses.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +-------------------+       +--------------+
 * | MESSAGE                                           |<------| OBJECT            |<--+---| SERIALIZABLE |
 * +---------------------------------------------------+       +-------------------+   |   | ============ |
 * | _Subject:           string                        |       | FACTORY           |   |   | Serializable |
 * | _Attachments:       IContainer(IFile)             |       | ================= |   |   +--------------+
 * | _InlineAttachments: IContainer(IFile)             |       | createMIMEMessage |   +---| DATAMAPABLE  |
 * | #Subject:           getSubject()                  |       +-------------------+   |   +--------------+
 * | #Attatchemts:       getAttachments()              |       | ADDRESSHANDLER    |   +---| ITERABLE     |
 * | #InlineAttachments: getInlineAttachments()        |       +-------------------+       +--------------+
 * +---------------------------------------------------+       | MAILABLE          |
 * | __construct():                                    |       +-------------------+
 * |                                                   |
 * | $To:      IContainer|null                         |
 * | $From:    IContainer|null                         |
 * | $ReplyTo: IContainer|null                         |
 * | $CC:      IContainer|null                         |
 * | $BCC:     IContainer|null                         |
 * | $Subject: IContainer|null                         |
 * +---------------------------------------------------+
 * | getAttachments(): _Attatcments->getIterator()     |
 * +---------------------------------------------------+
 * | addAttachment(): IDataMapper::Status              |
 * |                                                   |
 * | $File: IFile                                      |
 * +---------------------------------------------------+
 * | getInlineAttatc...() _InlineAt...->getIterator()  |
 * +---------------------------------------------------+
 * | InlineAttachment(): string                        |
 * |                                                   |
 * | $File: IFile                                      |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\Mail
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property string $Subject [dynamic] Invokes getSubject() and setSubject().
 * @property string $HTML [dynamic] Invokes getHTML() and setHTML().
 * @property string $Text [dynamic] Invokes getText() and setText().
 */
class GenericMessage extends \BLW\Type\AObject implements \BLW\Type\Mail\IMessage
{

#############################################################################################
# Mediatable Trait
#############################################################################################

    /**
     * Pointer to current mediator.
     *
     * @var \BLW\Type\IMediatable $_Mediator
     */
    protected $_Mediator = null;

    /**
     * Current mediator id of object.
     *
     * @var string $_MediatorID
     */
    protected $_MediatorID = null;

#############################################################################################
# AddressHandler Trait
#############################################################################################

    /**
     * Storage for `To` addresses.
     *
     * @var \BLW\Type\IContainer $_To
     */
    protected $_To = null;

    /**
     * Storage for `From` addresses.
     *
     * @var \BLW\Type\IContainer $_From
     */
    protected $_From = null;

    /**
     * Storage for `ReplyTo` addresses.
     *
     * @var \BLW\Type\IContainer $_ReplyTo
     */
    protected $_ReplyTo = null;

    /**
     * Storage for `CC` addresses.
     *
     * @var \BLW\Type\IContainer $_CC
     */
    protected $_CC = null;

    /**
     * Storage for `BCC` addresses.
     *
     * @var \BLW\Type\IContainer $_BCC
     */
    protected $_BCC = null;

#############################################################################################
# Messsage Trait
#############################################################################################

    /**
     * Title of message.
     *
     * @var string $_Subject
     */
    protected $_Subject = '';

    /**
     * Message body (HTML).
     *
     * @var string $_HTML
     */
    protected $_HTML = null;

    /**
     * Message body (Plain Text).
     *
     * @var string $_Text
     */
    protected $_Text = '';

    /**
     * Message attachments.
     *
     * @var \BLW\Type\IContainer $_Attachments
     */
    protected $_Attachments = null;

    /**
     * Message attachments.
     *
     * @var \BLW\Type\IContainer $_InlineAttachments
     */
    protected $_InlineAttachments = null;

#############################################################################################




#############################################################################################
# Mediatable Trait
#############################################################################################

    /**
     * Get the current mediator of the object.
     *
     * @return \BLW\Type\IMediator Returns <code>null</code> if no mediator is set.
     */
    final public function getMediator()
    {
        return $this->_Mediator;
    }

    /**
     * Set $Mediator dynamic property.
     *
     * @param \BLW\Type\IMediator $Mediator
     *            New mediator.
     * @return int Returns a <code>IDataMapper</code> status code.
     */
    final public function setMediator($Mediator)
    {
        // Is mediator valid
        if ($Mediator instanceof IMediator) {
            $this->_Mediator = $Mediator;
            return IDataMapper::UPDATED;
        }

        // Invalid mediator
        return IDataMapper::INVALID;
    }

    /**
     * Clear $Mediator dynamic property.
     *
     * @return int Returns a <code>IDataMapper</code> status code.
     */
    final public function clearMediator()
    {
        // Clear Mediator
        $this->_Mediator = null;

        // Done
        return IDataMapper::UPDATED;
    }

    /**
     * Generates a unique id used to identify object in mediator.
     *
     * @return string Hash of object.
     */
    public function getMediatorID()
    {
        if (! $this->_MediatorID)
            $this->_MediatorID = spl_object_hash($this);

        return $this->_MediatorID;
    }

    /**
     * Registers a function to execute on a mediator event.
     *
     * Registers a function to execute on a mediator event.
     *
     * <h4>Format:</h4>
     *
     * <pre>mixed function (\BLW\Type\IEvent $Event)</pre>
     *
     * <hr>
     *
     * @param string $EventName
     *            Event ID to attach to.
     * @param callable $Callback
     *            Function to call.
     * @param int $Priority
     *            Priotory of $Callback. (Higher priority = Higher Importance)
     */
    final public function _on($EventName, $Callback, $Priority = 0)
    {
        // Parameter validation
        if (is_scalar($EventName) ?  : is_callable(array(
            $EventName,
            '__toString'
        ))) {

            if (is_callable($Callback)) {

                // Register event
                $Mediator = $this->getMediator();
                $ID = $this->getMediatorID();

                if ($Mediator instanceof IMediator)
                    $Mediator->register("$ID.$EventName", $Callback, @intval($Priority));

                else
                    trigger_error(sprintf('Mediator not set with %s::setMediator()', get_class($this)), E_USER_NOTICE);
            }

            else
                throw new InvalidArgumentException(2);
        }

        else
            throw new InvalidArgumentException(1);
    }

    /**
     * Activates a mediator event.
     *
     * @param string $EventName
     *            Event ID to activate.
     * @param \BLW\Type\IEvent $Event
     *            Event object associated with the event.
     */
    final public function _do($EventName, IEvent $Event = null)
    {
        // Parameter validation
        if (is_scalar($EventName) ?  : is_callable(array(
            $EventName,
            '__toString'
        ))) {

            // Trigger event
            $Mediator = $this->getMediator();
            $ID       = $this->getMediatorID();

            if ($Mediator instanceof IMediator)
                $Mediator->trigger("$ID.$EventName", $Event);

            else
                trigger_error(sprintf('Mediator not set with %s::setMediator()', get_class($this)), E_USER_NOTICE);
        }

        else
            throw new InvalidArgumentException(1);
    }

#############################################################################################
# AddressHandler Trait
#############################################################################################

    /**
     * Get to email addresses.
     *
     * @return \BLW\Type\IContainer Returns a container of <code>IEmailAddress</code>.
     */
    public function getTo()
    {
        return $this->_To;
    }

    /**
     * Add to email address.
     *
     * @param \BLW\Type\IEmailAddress $EmailAddress
     *            Address to add.
     * @return int Returns a <code>IDataMapper</code> status code.
     */
    public function addTo(IEmailAddress $EmailAddress)
    {
        // Is address valid?
        if ($EmailAddress->isValid()) {

            // Add email address
            $this->_To->append($EmailAddress);

            // Done
            return IDataMapper::UPDATED;
        }

        // Done
        return IDataMapper::INVALID;
    }

    /**
     * Get from email addresses.
     *
     * @return \BLW\Type\IContainer Returns a container of <code>IEmailAddress</code>.
     */
    public function getFrom()
    {
        return $this->_From;
    }

    /**
     * Add from email address.
     *
     * @param \BLW\Type\IEmailAddress $EmailAddress
     *            Address to add.
     * @return int Returns a <code>IDataMapper</code> status code.
     */
    public function addFrom(IEmailAddress $EmailAddress)
    {
        // Is address valid?
        if ($EmailAddress->isValid()) {

            // Add email address
            $this->_From->append($EmailAddress);

            // Done
            return IDataMapper::UPDATED;
        }

        // Done
        return IDataMapper::INVALID;
    }

    /**
     * Get reply-to email addresses.
     *
     * @return \BLW\Type\IContainer Returns a container of <code>IEmailAddress</code>.
     */
    public function getReplyTo()
    {
        return $this->_ReplyTo;
    }

    /**
     * Add reply-to email address.
     *
     * @param \BLW\Type\IEmailAddress $EmailAddress
     *            Address to add.
     * @return int Returns a <code>IDataMapper</code> status code.
     */
    public function addReplyTo(IEmailAddress $EmailAddress)
    {
        // Is address valid?
        if ($EmailAddress->isValid()) {

            // Add email address
            $this->_ReplyTo->append($EmailAddress);

            // Done
            return IDataMapper::UPDATED;
        }

        // Done
        return IDataMapper::INVALID;
    }

    /**
     * Get cc email addresses.
     *
     * @return \BLW\Type\IContainer Returns a container of <code>IEmailAddress</code>.
     */
    public function getCC()
    {
        return $this->_CC;
    }

    /**
     * Add cc email address.
     *
     * @param \BLW\Type\IEmailAddress $EmailAddress
     *            Address to add.
     * @return int Returns a <code>IDataMapper</code> status code.
     */
    public function addCC(IEmailAddress $EmailAddress)
    {
        // Is address valid?
        if ($EmailAddress->isValid()) {

            // Add email address
            $this->_CC->append($EmailAddress);

            // Done
            return IDataMapper::UPDATED;
        }

        // Done
        return IDataMapper::INVALID;
    }

    /**
     * Get to email addresses.
     *
     * @return \BLW\Type\IContainer Returns a container of <code>IEmailAddress</code>.
     */
    public function getBCC()
    {
        return $this->_BCC;
    }

    /**
     * Add to email address.
     *
     * @param \BLW\Type\IEmailAddress $EmailAddress
     *            Address to add.
     * @return int Returns a <code>IDataMapper</code> status code.
     */
    public function addBCC(IEmailAddress $EmailAddress)
    {
        // Is address valid?
        if ($EmailAddress->isValid()) {

            // Add email address
            $this->_BCC->append($EmailAddress);

            // Done
            return IDataMapper::UPDATED;
        }

        // Done
        return IDataMapper::INVALID;
    }

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
            new ReflectionMethod(get_called_class(), 'createMimeMessage')
        );
    }

    /**
     * Generate a Mail\MimeMessage object from current class.
     *
     * @return \BLW\Model\MIMEMessage Generated message.
     */
    public function createMimeMessage()
    {
        // Validate Message
        if (! count($this->_To)) {
            throw new ClassException($this->_Status |= IMessage::NO_RECEIPIENT, 'Message has no receipient');
            return null;
        }

        elseif (! count($this->_From)) {
            throw new ClassException($this->_Status |= IMessage::NO_SENDER, 'Message has no sender');
            return null;
        }

        elseif (empty($this->_Subject)) {
            throw new ClassException($this->_Status |= IMessage::NO_SUBJECT, 'Message has no subject / title');
            return null;
        }

        elseif (empty($this->_Text)) {
            throw new ClassException($this->_Status |= IMessage::NO_TITLE, 'Message has no body');
            return null;
        }

        // Create new MIME Message
        $MimeMessage = new MIMEMessage('1.0');

        // Add headers
        $MimeMessage->getHeader()->append(new To($this->_To));
        $MimeMessage->getHeader()->append(new From($this->_From));

        if (count($this->_ReplyTo))
            $MimeMessage->getHeader()->append(new ReplyTo($this->_ReplyTo));
        if (count($this->_CC))
            $MimeMessage->getHeader()->append(new CC($this->_CC));
        if (count($this->_BCC))
            $MimeMessage->getHeader()->append(new BCC($this->_BCC));

        $MimeMessage->getHeader()->append(new Date());
        $MimeMessage->getHeader()->append(new Subject($this->_Subject));

        // Are there inline attachments
        if (count($this->_InlineAttachments)) {

            // Create Multipart section
            $MimeMessage->getBody()->addSection(new Section('multipart/related'));

            // Add Body
            $MimeMessage->getBody()->addSection(new Section('multipart/alternative'));

            // 1. Text
            $MimeMessage->getBody()->addPart(new QuotedPrintable('text/html', $this->_Text, 'utf-8'));

            // 2. HTML
            if (($Document = $this->_HTML) instanceof \DOMDocument) {
                $MimeMessage->getBody()->addPart(new QuotedPrintable('text/html', $Document->saveHTML(), 'utf-8'));
            }

            // End multipart/alternative
            $MimeMessage->getBody()->endSection();

            // Inline Attachments
            foreach ($this->_InlineAttachments as $File) {

                try {
                    $MimeMessage->getBody()->addPart(new InlineAttachment($File));
                }

                catch (FileException $e) {
                    throw new FileException($File->getPathname(), null, 0, $e);
                }
            }

            // End multipart/related
            $MimeMessage->getBody()->endSection();
        }

        else {

            // Add Body
            $MimeMessage->getBody()->addSection(new Section('multipart/alternative'));

            // 1. Text
            $MimeMessage->getBody()->addPart(new QuotedPrintable('text/html', $this->_Text, 'utf-8'));

            // 2. HTML
            if (($Document = $this->_HTML) instanceof \DOMDocument) {
                $MimeMessage->getBody()->addPart(new QuotedPrintable('text/html', $Document->saveHTML(), 'utf-8'));
            }

            // End multipart/alternative
            $MimeMessage->getBody()->endSection();
        }

        // Add atatchments
        foreach ($this->_Attachments as $File) {

            try {
                $MimeMessage->getBody()->addPart(new Attachment($File));
            }

            catch (FileException $e) {
                throw new FileException($File->getPathname(), null, 0, $e);
            }
        }

        // Done
        return $MimeMessage;
    }

#############################################################################################
# Mailable Trait
#############################################################################################

    /**
     * Send object via a mail transport.
     *
     * <h3>Introduction</h3>
     *
     * <p><code>sendWidth()</code> should call <code>createMimeMail()</code>
     * factory method which should create an <code>IMessage</code> object
     * which can be sent via transport</code>
     *
     * @param \BLW\Type\Mail\ITransport $Transport
     *            Transport to use to send message.
     * @param int $flags
     *            Transport flags.
     * @return int Returns a status of <code>IMailer::send()</code>.
     */
    public function sendWith(ITransport $Transport, $flags = ITransport::MAIL_FLAGS)
    {
        return $Transport->send($this, $flags);
    }

#############################################################################################
    // Messsage Trait
#############################################################################################

    /**
     * Constructor
     *
     * @param \BLW\Type\IContainer $To
     *            Initial to addresses.
     * @param \BLW\Type\IContainer $From
     *            Initial from addresses.
     * @param \BLW\Type\IContainer $ReplyTo
     *            Initial reply-to addresses.
     * @param \BLW\Type\IContainer $CC
     *            Initial cc addresses.
     * @param \BLW\Type\IContainer $BCC
     *            Initial bcc addresses.
     */
    public function __construct(IContainer $To = null, IContainer $From = null, IContainer $ReplyTo = null, IContainer $CC = null, IContainer $BCC = null)
    {
        // Parent constuctor
        parent::__construct();

        // Parameters
        $this->_To      = $To      ?: new GenericContainer(self::EMAIL);
        $this->_From    = $From    ?: new GenericContainer(self::EMAIL);
        $this->_ReplyTo = $ReplyTo ?: new GenericContainer(self::EMAIL);
        $this->_CC      = $CC      ?: new GenericContainer(self::EMAIL);
        $this->_BCC     = $BCC     ?: new GenericContainer(self::EMAIL);

        // Defaults
        $this->_Attachments       = new GenericContainer(self::FILE);
        $this->_InlineAttachments = new GenericContainer(self::FILE);
    }

    /**
     * Returns the HTML body of message.
     *
     * <h4>Note:</h4>
     *
     * <p>Raises <b>E_USER_WARNING</b> if body not set with setHTML().</p>
     *
     * <hr>
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return string HTML
     */
    public function getHTML()
    {
        return $this->_HTML;
    }

    /**
     * Sets the HTML body of message.
     *
     * <h4>Note:</h4>
     *
     * <p>If no text body exists for the Message, this function will create one.</p>
     *
     * <hr>
     *
     * @param \DOMDocument $Document
     *        HTML Content of message
     * @return int Returns a <code>IDataMapper</code> status code.
     */
    public function setHTML($Document)
    {
        // Validate Document
        if ($Document instanceof DOMDocument) {

            // Update message HTML
            $this->_HTML = $Document;

            // Done
            return IDataMapper::UPDATED;
        }

        // Invalid $Document
        return IDataMapper::INVALID;
    }

    /**
     * Return the text body of a message.
     *
     * @return string Message Plain Text.
     */
    public function getText()
    {
        return $this->_Text;
    }

    /**
     * Sets the message body text
     *
     * @param string $Text
     *            Message Plain Text
     * @return int Returns a <code>IDataMapper</code> status code.
     */
    public function setText($Text)
    {
        // Validate $Text
        if (is_string($Text) ?  : is_callable(array(
            $Text,
            '__toString'
        ))) {

            // Update message text
            $this->_Text = strval($Text);

            // Done
            return IDataMapper::UPDATED;
        }

        // Invalid $Text
        return IDataMapper::INVALID;
    }

    /**
     * Return the subject / title of message.
     *
     * @return string Message title.
     */
    public function getSubject()
    {
        return $this->_Subject;
    }

    /**
     * Sets the message body text
     *
     * @todo Properly format message subject.
     * @param string $Subject
     *            Message title.
     * @return int Returns a <code>IDataMapper</code> status code.
     */
    public function setSubject($Subject)
    {
        static $format;

        // Formats string to tilte format
        $format = $format ?: function ($string)
        {
            // Alphanumeric, Unicode, tab, space, !, #, $, %, &, +, -, .
            $Invalid = "[^\w\p{L}\\x9\\x20\\x21\\x23-\\x26\\x2b\\x2d\\x2e]";

            $string  = strval($string); // String value
            $string  = preg_replace("!$Invalid+!", '', $string); // Invalid characters
            $string  = preg_replace('!\s+!', ' ', $string); // Duplicate spaces

            return $string;
        };

        // Validate $Text
        if (is_string($Subject) ?  : is_callable(array(
            $Subject,
            '__toString'
        ))) {

            // Update message text
            $this->_Subject = $format($Subject);

            // Done
            return IDataMapper::UPDATED;
        }

        // Invalid $Text
        return IDataMapper::INVALID;
    }

    /**
     * Return attachments added to message.
     *
     * @return \BLW\Type\IContainer Message attachments
     */
    public function getAttachments()
    {
        return $this->_Attachments;
    }

    /**
     * Add attachment to message.
     *
     * @param \BLW\Type\IFile $File
     *            File to attatch.
     * @return int Returns a <code>IDataMapper</code> status code.
     */
    public function addAttachment(IFile $File)
    {
        // Is file readable?
        if ($File->isReadable() || $File->openFile()) {

            // Add attachment
            $this->_Attachments->append($File);

            // Done
            return IDataMapper::UPDATED;
        }

        // Done
        return IDataMapper::INVALID;
    }

    /**
     * Return inline attachments added to message body.
     *
     * @return \BLW\Type\IContainer Message attachments
     */
    public function getInlineAttachments()
    {
        return $this->_InlineAttachments;
    }

    /**
     * Add inline attachment to message.
     *
     * @throws \BLW\Model\FileException If <code>$File</code> cannot be read.
     *
     * @param \BLW\Type\IFile $File
     *            File to attatch.
     * @return string UniqueID of attachment.
     */
    public function InlineAttachment(IFile $File)
    {
        // Is file readable?
        if ($File->isReadable() || $File->openFile()) {

            // Generate unique id
            $File->UniqueID = uniqid();

            // Add attachment
            $this->_InlineAttachments->append($File);

            // Done
            return "@$File->UniqueID";
        }

        // Throw exception
        else
            throw new FileException(strval($File));

        return '';
    }

    /**
     * Dynamic properties.
     *
     * @param string $name
     *            Label of property to search for.
     * @return mixed Returns <code>null</code> if not found.
     */
    public function __get($name)
    {
        switch ($name) {
            // IMediatable
            case 'Mediator':
                return $this->getMediator();
            case 'MediatorID':
                return $this->getMediatorID();

            // IAddressHandler
            case 'To':
                return $this->_To;
            case 'From':
                return $this->_From;
            case 'ReplyTo':
                return $this->_ReplyTo;
            case 'CC':
                return $this->_CC;
            case 'BCC':
                return $this->_BCC;

            // IMessage
            case 'HTML':
                return $this->getHTML();
            case 'Text':
                return $this->getText();
            case 'Subject':
                return $this->getSubject();

            // IObject
            default:
                return parent::__get($name);
        }

        return null;
    }

    /**
     * Dynamic properties.
     *
     * @param string $name
     *            Label of property to search for.
     * @return bool Returns a <code>TRUE</code> if property exists. <code>FALSE</code> otherwise.
     */
    public function __isset($name)
    {
        switch ($name) {
            // IMediatable
            case 'Mediator':
                return $this->getMediator() !== null;
            case 'MediatorID':
                return $this->getMediatorID() !== null;

            // IAddressHandler
            case 'To':
                return $this->_To !== null;
            case 'From':
                return $this->_From !== null;
            case 'ReplyTo':
                return $this->_ReplyTo !== null;
            case 'CC':
                return $this->_CC !== null;
            case 'BCC':
                return $this->_BCC !== null;

            // IMessage
            case 'HTML':
                return $this->getHTML() !== null;
            case 'Text':
                return $this->getText() !== null;
            case 'Subject':
                return $this->getSubject() !== null;

            // IObject
            default:
                return parent::__isset($name);
        }
    }

    /**
     * Dynamic properties.
     *
     * @param string $name
     *            Label of property to set.
     * @param mixed $value
     *            Value of property.
     * @return bool Returns a <code>IDataMapper</code> status code.
     */
    public function __set($name, $value)
    {
        // Try to set property
        switch ($name) {
            // IMediatable
            case 'Mediator':
                $result = $value instanceof IMediator ? $this->setMediator($value) : IDataMapper::INVALID;
                break;
            case 'MediatorID':
                $result = IDataMapper::READONLY;
                break;

            // IAddressHandler
            case 'To':
                $result = $value instanceof IEmailAddress ? $this->addTo($value) : IDataMapper::INVALID;
                break;
            case 'From':
                $result = $value instanceof IEmailAddress ? $this->addFrom($value) : IDataMapper::INVALID;
                break;
            case 'ReplyTo':
                $result = $value instanceof IEmailAddress ? $this->addReplyTo($value) : IDataMapper::INVALID;
                break;
            case 'CC':
                $result = $value instanceof IEmailAddress ? $this->addCC($value) : IDataMapper::INVALID;
                break;
            case 'BCC':
                $result = $value instanceof IEmailAddress ? $this->addBCC($value) : IDataMapper::INVALID;
                break;

            // IMessage
            case 'HTML':
                $result = $this->setHTML($value);
                break;
            case 'Text':
                $result = $this->setText($value);
                break;
            case 'Subject':
                $result = $this->setSubject($value);
                break;

            // IObject
            default:
                return parent::__set($name, $value);
        }

        // Check results
        switch ($result) {
            // Readonly property
            case IDataMapper::READONLY:
            case IDataMapper::ONESHOT:
                trigger_error(sprintf('Cannot modify readonly property: %s::$%s', get_class($this), $name), E_USER_NOTICE);
                break;

            // Invalid value for property
            case IDataMapper::INVALID:
                trigger_error(sprintf('Invalid value %s for property: %s::$%s', @print_r($value, true), get_class($this), $name), E_USER_NOTICE);
                break;

            // Undefined property property
            case IDataMapper::UNDEFINED:
                trigger_error(sprintf('Tried to modify non-existant property: %s::$%s', get_class($this), $name), E_USER_ERROR);
                break;
        }
    }

    /**
     * Unmap dynamic properties from DataMapper.
     *
     * @param string $name
     *            Label of dynamic property. (case sensitive)
     * @return bool Returns a <code>TRUE</code> if property exists. <code>FALSE</code> otherwise.
     */
    public function __unset($name)
    {
        // Try to unset property
        switch ($name) {
            // IMediatable
            case 'Mediator':
                $this->_Mediator = null;
                break;

            // IMessage
            case 'HTML':
                $this->_HTML = null;
                break;
            case 'Text':
                $this->_Text = '';
                break;
            case 'Subject':
                $this->_Subject = '';
                break;

            // IObject
            default:
                return parent::__unset($name);
        }
    }

#############################################################################################
}

return true;
