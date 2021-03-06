<?php
/**
 * IRequest.php | Apr 10, 2014
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

use BLW\Type\IURI;

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
 * Interface for HTTP Request objects
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +-----------------+       +------------------+
 * | REQUEST                                           |<------| MIME\MESSAGE    |<------| FACTORY          |
 * +---------------------------------------------------+       +-----------------+       | ================ |
 * | GET                                               |                                 | createFromString |
 * | POST                                              |                                 | createHeader     |
 * | HEAD                                              |                                 +------------------+
 * | OPTIONS                                           |
 * | CONNECT                                           |
 * +---------------------------------------------------+
 * | _Type:     int                                    |
 * | _URI:      IURI                                   |
 * | _Referer:  IURI                                   |
 * | _Config:   IConfig                                |
 * | #Type:     getType()                              |
 * |            setType()                              |
 * | #URI:      getURI()                               |
 * |            setURI()                               |
 * | #Referer:  getReferer()                           |
 * |            setReferer()                           |
 * | #Config:   _Config                                |
 * | #Header:   getHeader()                            |
 * | #Body:     getBody()                              |
 * +---------------------------------------------------+
 * | getType(): _Type                                  |
 * +---------------------------------------------------+
 * | setType(): IDataMapper::STATUS                    |
 * |                                                   |
 * | $Type:  string                                    |
 * +---------------------------------------------------+
 * | getURI(): _URI                                    |
 * +---------------------------------------------------+
 * | setURI(): IDataMapper::STATUS                     |
 * |                                                   |
 * | $URI:  IURI                                       |
 * +---------------------------------------------------+
 * | getReferer(): _Referer                            |
 * +---------------------------------------------------+
 * | setReferer(): IDataMapper::STATUS                 |
 * |                                                   |
 * | $Referer:  IURI                                   |
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
 * @property string $_Type [protected] Request type:
 *
 * <ul>
 * <li><b>IRequest::GET</b>: GET request ('GET')</li>
 * <li><b>IRequest::POST</b>: POST request ('POST')</li>
 * <li><b>IRequest::HEAD</b>: HEAD request ('HEAD')</li>
 * <li><b>IRequest::OPTIONS</b>: OPTIONS request ('OPTIONS')</li>
 * <li><b>IRequest::CONNECT</b>: CONNECT request ('CONNECT')</li>
 * <li><b>IRequest::DELETE</b>: DELETE request ('DELETE')</li>
 * </ul>
 *
 * @property \BLW\Type\IURI $_URI [protected] URL to make request to.
 * @property \BLW\Type\IURI $_Referer [protected] Refering URI.
 * @property \BLW\Type\IConfig $_Config [protected] Request configuration:
 *
 * <ul>
 * <li><b>Timeout</b>: <i>int</i> Time in seconds to wait befor timing out request</li>
 * <li><b>Auth</b>: <i>string</i> Username and password to use for connection formatted as "[username]:[password]"</li>
 * </ul>
 *
 * @property string $Type [dynamic] Invokes getType() and setType().
 * @property \BLW\Type\IURI $URI [dynamic] Invokes getURI() and setURI().
 * @property \BLW\Type\IURI $Referer [dynamic] Invokes getReferer() and setReferer().
 * @property \BLW\Type\IConfig $Config [readonly] $_Config
 * @property \BLW\Type\MIME\IHead $Header [dynamic] Invokes getHeader().
 * @property \BLW\Type\MIME\IBody $Body [dynamic] Invokes getBody().
 */
interface IRequest extends \BLW\Type\MIME\IMessage
{
    // REQUEST CONSTANTS
    const GET     = 'GET';
    const POST    = 'POST';
    const PUT     = 'PUT';
    const HEAD    = 'HEAD';
    const OPTIONS = 'OPTIONS';
    const CONNECT = 'CONNECT';
    const DELETE  = 'DELETE';

    /**
     * Return the type of request.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @return string $_Type
     */
    public function getType();

    /**
     * Set the type of request.
     *
     * <h4>Note</h4>
     *
     * <p>Fails if:</p>
     *
     * <ul>
     * <li>$URI is invalid</li>
     * <li>$URI is not absolute</li>
     * </ul>
     *
     * <hr>
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param string $Type
     *            New Type:
     *
     * <ul>
     * <li><b>GET</b>: <i>'GET'</i></li>
     * <li><b>POST</b>: <i>'POST'</i></li>
     * <li><b>HEAD</b>: <i>'HEAD'</i></li>
     * <li><b>OPTIONS</b>: <i>'OPTIONS'</i></li>
     * <li><b>CONNECT</b>: <i>'CONNECT'</i></li>
     * <li><b>DELETE</b>: <i>'DELETE'</i></li>
     * </ul>
     *
     * @return integer Returns a <code>DataMapper</code> status code.
     */
    public function setType($Type);

    /**
     * Return the request URI.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @return \BLW\Type\IURI $_URI
     */
    public function getURI();

    /**
     * Set the request URI
     *
     * <h4>Note</h4>
     *
     * <p>Fails if:</p>
     *
     * <ul>
     * <li>$URI is invalid</li>
     * <li>$URI is not absolute</li>
     * </ul>
     *
     * <hr>
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param \BLW\Type\IURI $URI
     *            Request URL.
     * @return integer Returns a <code>DataMapper</code> status code.
     */
    public function setURI($URI);

    /**
     * Return the request referer.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @return \BLW\Type\IURI $_Referer
     */
    public function getReferer();

    /**
     * Set the request referer.
     *
     * <h4>Note</h4>
     *
     * <p>Fails if:</p>
     *
     * <ul>
     * <li>$Referer is invalid</li>
     * <li>$Referer is not absolute</li>
     * </ul>
     *
     * <hr>
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param \BLW\Type\IURI $Referer
     *            Request referer URL.
     * @return integer Returns a <code>DataMapper</code> status code.
     */
    public function setReferer($Referer);
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
