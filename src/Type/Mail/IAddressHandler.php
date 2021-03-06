<?php
/**
 * IAddressHandler.php | Mar 08, 2014
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
 * @version GIT: 0.2.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type\Mail;

use BLW\Type\IEmailAddress;

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
 * | ADDRESSHANDLER                                    |
 * +---------------------------------------------------+
 * | _To:                  IContainer(IEmailAddress)   |
 * | _From:                IContainer(IEmailAddress)   |
 * | _ReplyTo:             IContainer(IEmailAddress)   |
 * | _CC:                  IContainer(IEmailAddress)   |
 * | _BCC:                 IContainer(IEmailAddress)   |
 * | _Subject:             ITitle                      |
 * | _Attatchments:        IContainer(IFile)           |
 * | _InlineAttatchments:  IContainer(IFile)           |
 * |                                                   |
 * | #To:                  getTo()                     |
 * |                       addTo()                     |
 * | #From:                getFrom()                   |
 * |                       addFrom()                   |
 * | #ReplyTo:             getReplyTo()                |
 * |                       addReplyTo()                |
 * | #CC:                  getCC()                     |
 * | #BCC:                 getBCC()                    |
 * +---------------------------------------------------+
 * | getTo(): _To->getIterator()                       |
 * +---------------------------------------------------+
 * | addTo(): IDataMapper::Status                      |
 * |                                                   |
 * | $Address:  string|IEmailAddress                   |
 * | $Name:     string|IFullName                       |
 * +---------------------------------------------------+
 * | getFrom(): _From->getIterator()                   |
 * +---------------------------------------------------+
 * | addFrom(): IDataMapper::Status                    |
 * |                                                   |
 * | $Address:  string|IEmailAddress                   |
 * | $Name:     string|IFullName                       |
 * +---------------------------------------------------+
 * | getReplyTo(): _ReplyTo->getIterator()             |
 * +---------------------------------------------------+
 * | addTo(): IDataMapper::Status                      |
 * |                                                   |
 * | $Address:  string|IEmailAddress                   |
 * | $Name:     string|IFullName                       |
 * +---------------------------------------------------+
 * | getCC(): _CC->getIterator()                       |
 * +---------------------------------------------------+
 * | addCC(): IDataMapper::Status                      |
 * |                                                   |
 * | $Address:  string|IEmailAddress                   |
 * | $Name:     string|IFullName                       |
 * +---------------------------------------------------+
 * | getBCC(): _BCC->getItarator()                     |
 * +---------------------------------------------------+
 * | addTo(): IDataMapper::Status                      |
 * |                                                   |
 * | $Address:  string|IEmailAddress                   |
 * | $Name:     string|IFullName                       |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\Core
 * @api     BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property \BLW\Type\IContainer $_To [protected] Storage for `To` addresses.
 * @property \BLW\Type\IContainer $_From [protected] Storage for `From` addresses.
 * @property \BLW\Type\IContainer $_ReplyTo [protected] Storage for `ReplyTo` addresses.
 * @property \BLW\Type\IContainer $_CC [protected] Storage for `CC` addresses.
 * @property \BLW\Type\IContainer $_BCC [protected] Storage for `BCC` addresses.
 * @property \BLW\Type\IContainer $To [dynamic] Invokes getTo() and addTo()
 * @property \BLW\Type\IContainer $From [dynamic] Invokes getFrom() and addFrom()
 * @property \BLW\Type\IContainer $ReplyTo [dynamic] Invokes getReplyTo() and addReplyTo()
 * @property \BLW\Type\IContainer $CC [dynamic] Invokes getCC() and addCC()
 * @property \BLW\Type\IContainer $BCC [dynamic] Invokes getBCC() and addBCC()
 */
interface IAddressHandler
{

    /**
     * Get to email addresses.
     *
     * @return \BLW\Type\IContainer Returns a container of <code>IEmailAddress</code>.
     */
    public function getTo();

    /**
     * Add to email address.
     *
     * @param \BLW\Type\IEmailAddress $EmailAddress
     *            Address to add.
     * @return integer Returns a <code>IDataMapper</code> status code.
     */
    public function addTo(IEmailAddress $EmailAddress);

    /**
     * Get from email addresses.
     *
     * @return \BLW\Type\IContainer Returns a container of <code>IEmailAddress</code>.
     */
    public function getFrom();

    /**
     * Add from email address.
     *
     * @param \BLW\Type\IEmailAddress $EmailAddress
     *            Address to add.
     * @return integer Returns a <code>IDataMapper</code> status code.
     */
    public function addFrom(IEmailAddress $EmailAddress);

    /**
     * Get reply-to email addresses.
     *
     * @return \BLW\Type\IContainer Returns a container of <code>IEmailAddress</code>.
     */
    public function getReplyTo();

    /**
     * Add reply-to email address.
     *
     * @param \BLW\Type\IEmailAddress $EmailAddress
     *            Address to add.
     * @return integer Returns a <code>IDataMapper</code> status code.
     */
    public function addReplyTo(IEmailAddress $EmailAddress);

    /**
     * Get cc email addresses.
     *
     * @return \BLW\Type\IContainer Returns a container of <code>IEmailAddress</code>.
     */
    public function getCC();

    /**
     * Add cc email address.
     *
     * @param \BLW\Type\IEmailAddress $EmailAddress
     *            Address to add.
     * @return integer Returns a <code>IDataMapper</code> status code.
     */
    public function addCC(IEmailAddress $EmailAddress);

    /**
     * Get to email addresses.
     *
     * @return \BLW\Type\IContainer Returns a container of <code>IEmailAddress</code>.
     */
    public function getBCC();

    /**
     * Add to email address.
     *
     * @param \BLW\Type\IEmailAddress $EmailAddress
     *            Address to add.
     * @return integer Returns a <code>IDataMapper</code> status code.
     */
    public function addBCC(IEmailAddress $EmailAddress);
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
