<?php
/**
 * IMessage.php | Mar 08, 2014
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

use BLW\Type\IObject;
use BLW\Type\IMediatable;
use BLW\Type\IContainer;
use BLW\Type\IFile;


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
 * +---------------------------------------------------+       +-------------------+       +--------------+
 * | MESSAGE                                           |<------| OBJECT            |<--+---| SERIALIZABLE |
 * +---------------------------------------------------+       +-------------------+   |   | ============ |
 * | _Subject:            string                       |       | FACTORY           |   |   | Serializable |
 * | _Attachments:        IContainer(IFile)            |       | ================= |   |   +--------------+
 * | _InlineAttachments:  IContainer(IFile)            |       | createMIMEMessage |   +---| DATAMAPABLE  |
 * | #Subject:            getSubject()                 |       +-------------------+   |   +--------------+
 * | #Attatchemts:        getAttachments()             |       | ADDRESSHANDLER    |   +---| ITERABLE     |
 * | #InlineAttachments:  getInlineAttachments()       |       +-------------------+       +--------------+
 * +---------------------------------------------------+       | MAILABLE          |
 * | __construct():                                    |       +-------------------+
 * |                                                   |
 * | $To:       IContainer|null                        |
 * | $From:     IContainer|null                        |
 * | $ReplyTo:  IContainer|null                        |
 * | $CC:       IContainer|null                        |
 * | $BCC:      IContainer|null                        |
 * | $Subject:  IContainer|null                        |
 * +---------------------------------------------------+
 * | getAttachments(): _Attatcments->getIterator()     |
 * +---------------------------------------------------+
 * | addAttachment(): IDataMapper::Status              |
 * |                                                   |
 * | $File:  IFile                                     |
 * +---------------------------------------------------+
 * | getInlineAttach...() _InlineAt...->getIterator()  |
 * +---------------------------------------------------+
 * | InlineAttachment(): string                        |
 * |                                                   |
 * | $File:  IFile                                     |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\Core
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property string $_Subject [protected] Title of message.
 * @property string $_HTML [protected] Message body (HTML).
 * @property string $_Text [protected] Message body (Plain Text).
 * @property \BLW\Type\IContainer $_Attachments [protected] Message attachments.
 * @property \BLW\Type\IContainer $_InlineAttachments [protected] Message attachments.
 * @property string $Subject [dynamic] Invokes getSubject() and setSubject().
 * @property string $HTML [dynamic] Invokes getHTML() and setHTML().
 * @property string $Text [dynamic] Invokes getText() and setText().
 */
interface IMessage extends \BLW\Type\IObject, \BLW\Type\IMediatable, \BLW\Type\Mail\IAddressHandler, \BLW\Type\Mail\IMailable
{
    // Statuses
    const NO_RECEIPIENT = 0x0002;
    const NO_SENDER     = 0x0002;
    const NO_SUBJECT    = 0x0002;
    const NO_TITLE      = 0x0002;

    // Classes
    const EMAIL = '\\BLW\\Type\\IEmailAddress';
    const FILE  = '\\BLW\\Type\\IFile';

    /**
     * Constructor
     *
     * @api BLW
     * @since 1.0.0
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
    public function __construct(IContainer $To = null, IContainer $From = null, IContainer $ReplyTo = null, IContainer $CC = null, IContainer $BCC = null);

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
    public function getHTML();

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
     * @return int Returns a <code>IDataMapper</code> status code.
     */
    public function setHTML($Document);

    /**
     * Return the text body of a message.
     *
     * @return string Message Plain Text.
     */
    public function getText();

    /**
     * Sets the message body text
     *
     * @param string $Text
     *            Message Plain Text
     * @return int Returns a <code>IDataMapper</code> status code.
     */
    public function setText($Text);

    /**
     * Return the subject / title of message.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return string Message title.
     */
    public function getSubject();

    /**
     * Sets the subect / title of message.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param string $Subject
     *            Message title.
     * @return int Returns a <code>IDataMapper</code> status code.
     */
    public function setSubject($Subject);

    /**
     * Return attachments added to message.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return \BLW\Type\IContainer Message attachments
     */
    public function getAttachments();

    /**
     * Add attachment to message.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param \BLW\Type\IFile $File
     *            File to attatch.
     * @return int Returns a <code>IDataMapper</code> status code.
     */
    public function addAttachment(IFile $File);

    /**
     * Return inline attachments added to message body.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return \BLW\Type\IContainer Message attachments
     */
    public function getInlineAttachments();

    /**
     * Add inline attachment to message.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @throws \BLW\Model\FileException If <code>$File</code> cannot be read.
     *
     * @param \BLW\Type\IFile $File
     *            File to attatch.
     * @return string UniqueID of attachment.
     */
    public function InlineAttachment(IFile $File);
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
