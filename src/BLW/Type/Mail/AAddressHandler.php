<?php
/**
 * AAddressHandler.php | Mar 08, 2014
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

use BLW\Type\IDataMapper;
use BLW\Type\IEmailAddress;


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
 * Standard abstract class for objects that handle email Addresses.
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
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property \BLW\Type\IContainer $To [dynamic] Invokes getTo() and addTo()
 * @property \BLW\Type\IContainer $From [dynamic] Invokes getFrom() and addFrom()
 * @property \BLW\Type\IContainer $ReplyTo [dynamic] Invokes getReplyTo() and addReplyTo()
 * @property \BLW\Type\IContainer $CC [dynamic] Invokes getCC() and addCC()
 * @property \BLW\Type\IContainer $BCC [dynamic] Invokes getBCC() and addBCC()
 */
abstract class AAddressHandler
{

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
}

return true;
