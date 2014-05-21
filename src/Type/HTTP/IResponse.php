<?php
/**
 * IResponse.php | Apr 10, 2014
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
 * @package BLW\HTTP
 * @version GIT: 0.2.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type\HTTP;

use BLW\Type\MIME\IMessage;

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
 * Interface for HTTP Reqponse objects.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +-----------------+       +------------------+
 * | RESPONSE                                          |<------| MIME\MESSAGE    |<------| FACTORY          |
 * +---------------------------------------------------+       +-----------------+       | ================ |
 * | [###]                                             |       | ArrayAccess     |       | createFromString |
 * +---------------------------------------------------+       +-----------------+       +------------------+
 * | _RequestURI:  URI                                 |
 * | _URI:         URI                                 |
 * | _Protocol:    string                              |
 * | _Version:     string                              |
 * | _Status:      int                                 |
 * | _Codes:       string[]                            |
 * | _Storage:     array                               |
 * | #RequestURI:  getRequestURI()                     |
 * |               setRequestURI()                     |
 * | #URI:         getURI()                            |
 * |               setURI()                            |
 * | #Protocol:    _Protocol                           |
 * | #Version:     _Version                            |
 * | #Status:      _Status                             |
 * | #Header:      getHeader()                         |
 * | #Body:        getBody()                           |
 * +---------------------------------------------------+
 * | getCodeString(): string                           |
 * |                                                   |
 * | $Status:  int                                     |
 * +---------------------------------------------------+
 * | isValidCode() bool                                |
 * |                                                   |
 * | $Code:  int                                       |
 * +---------------------------------------------------+
 * | getRequestURI(): _RequestURI                      |
 * +---------------------------------------------------+
 * | setRequestURI(): IDataMapper::STATUS              |
 * |                                                   |
 * | $RequestURI:  IURI                                |
 * +---------------------------------------------------+
 * | getURI(): _URI                                    |
 * +---------------------------------------------------+
 * | setURI(): IDataMapper::STATUS                     |
 * |                                                   |
 * | $URI:  IURI                                       |
 * +---------------------------------------------------+
 * | setStorage(): IDataMapper::STATUS                 |
 * |                                                   |
 * | $Storage:  array                                  |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\HTTP
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property \BLW\Type\IURI $_RequestURI [protected] Original request URI.
 * @property \BLW\Type\IURI $_URI [protected] Current / Last URI of response.
 * @property string $_Protocol [protected] Message protocol (https://tools.ietf.org/html/rfc2616#page-17).
 * @property string $_Version [protected] Protocol version (https://tools.ietf.org/html/rfc2616#page-17).
 * @property integer $_Status [protected] Status code of request.
 * @property array $_Storage [protected] information about request used by client.
 * @property \BLW\Type\IURI $RequestURI [dynamic] Invokes getRequestURI() and setRequestURI().
 * @property \BLW\Type\IURI $URI [dynamic] Invokes getURI() and setURI().
 * @property string $Protocol [readonly] $_Protocol
 * @property string $Version [readonly] $_Version
 * @property integer $Status [readonly] $_Status
 * @property \BLW\Type\MIME\IHead $Header [readonly] Invokes getHeader().
 * @property \BLW\Type\MIME\IBody $Body [readonly] Invokes getBody().
 */
interface IResponse extends \BLW\Type\MIME\IMessage, \ArrayAccess, \Countable
{

    /**
     * Returns whether the requested index exists
     *
     * @api BLW
     * @since   1.0.0
     * @link http://www.php.net/manual/en/arrayaccess.offsetexists.php ArrayAccess::offsetExists()
     *
     * @param mixed $index
     *            The index being checked.
     * @return boolean <code>TRUE</code> if the requested index exists, <code>FALSE</code> otherwise.
     */
    public function offsetExists($index);

    /**
     * Returns the value at the specified index
     *
     * @api BLW
     * @since   1.0.0
     * @link http://www.php.net/manual/en/arrayaccess.offsetget.php ArrayAccess::offsetGet()
     *
     * @param mixed $index
     *            The index with the value.
     * @return mixed The value at the specified index or <code>FALSE</code>.
     */
    public function offsetGet($index);

    /**
     * Sets the value at the specified index to newval
     *
     * @api BLW
     * @since   1.0.0
     * @link http://www.php.net/manual/en/arrayaccess.offsetset.php ArrayAccess::offsetSet()
     *
     * @param mixed $index
     *            The index being set.
     * @param mixed $newval
     *            The new value for the index.
     * @return void
     */
    public function offsetSet($index, $newval);

    /**
     * Unsets the value at the specified index
     *
     * @api BLW
     * @since   1.0.0
     * @link http://www.php.net/manual/en/arrayaccess.offsetunset.php ArrayAccess::offsetUnset()
     *
     * @param mixed $index
     *            The index being unset.
     * @return void
     */
    public function offsetUnset($index);

    /**
     * Get the number of public objects in the ObjectStorage
     *
     * @api BLW
     * @since   1.0.0
     * @link http://www.php.net/manual/en/countable.count.php Countable::count()
     *
     * @return integer The number of objects in storage.
     */
    public function count();

    /**
     * Returns the text of a HTTP status code.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param integer $Code
     *            Code to interprate.
     * @return string Code interpratation.
     */
    public static function getCodeString($Code);

    /**
     * Tests if a code is valid or not.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param integer $Code
     *            Code to test
     * @return boolean <code>TRUE</code> if valid. <code>FALSE</code> otherwise.
     */
    public static function isValidCode($Code);

    /**
     * Retrieve the request URI that produced this response.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @return \BLW\Type\IURI $_RequestURI
     */
    public function getRequestURI();

    /**
     * Sets the request URI that produced the response.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param \BLW\Type\IURI $RequestURI
     *            New request URI
     * @return integer Returns a <code>DataMapper</code> status code.
     */
    public function setRequestURI($RequestURI);

    /**
     * Retrieve the last / current URI of this response.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @return \BLW\Type\IURI $_URI
     */
    public function getURI();

    /**
     * Sets the last / current URI of this response.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param \BLW\Type\IURI $URI
     *            New request URI
     * @return integer Returns a <code>DataMapper</code> status code.
     */
    public function setURI($URI);

    /**
     * Sets the information about response created by client.
     *
     * <h4>Note</h4>
     *
     * <p>This information is used to create values for
     * <code>ArrayAccess</code></p>
     *
     * <hr>
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param array $Storage
     *            New Storage.
     * @return integer Returns a <code>DataMapper</code> status code.
     */
    public function setStorage(array $Storage);

    /**
     * Resets the client information about response.
     *
     * @api BLW
     * @since   1.0.0
     * @see \BLW\Type\HTTP\IResponse::setStorage() IResponse::setStorage()
     *
     * @return integer Returns a <code>DataMapper</code> status code.
     */
    public function clearStorage();
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
