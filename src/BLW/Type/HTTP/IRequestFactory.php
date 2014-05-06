<?php
/**
 * IRequestFactory.php | Apr 14, 2014
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
 * @version GIT 0.2.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type\HTTP;

use BLW\Type\IURI;
use BLW\Type\IContainer;


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
 * Interface for HTTP Request factory objects.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +---------------+
 * | REQUESTFACTORY                                    |<------| FACTORY       |
 * +---------------------------------------------------+       | ============= |
 * | _RequestClass: string                             |       | createGET     |
 * +---------------------------------------------------+       | createHEAD    |
 * | createGET(): IRequest                             |       | createPOST    |
 * |                                                   |       +---------------+
 * | $URI:      IURI                                   |
 * | $BaseURI:  IURI                                   |
 * | $Headers:  array|\Traversable                     |
 * +---------------------------------------------------+
 * | createHEAD(): IRequest                            |
 * |                                                   |
 * | $URI:      IURI                                   |
 * | $BaseURI:  IURI                                   |
 * | $Headers:  array|\Traversable                     |
 * +---------------------------------------------------+
 * | createPOST(): IRequest                            |
 * |                                                   |
 * | $URI:      IURI                                   |
 * | $BaseURI:  IURI                                   |
 * | $Data:     array|\Traversable                     |
 * | $Headers:  array|\Traversable                     |
 * +---------------------------------------------------+
 * </pre>
 *
 * @package BLW\HTTP
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property string $_RequestClass [protected] Class to use to create <code>IRequest</code> objects.
 */
interface IRequestFactory extends \BLW\Type\IFactory
{

    /**
     * Creates HTTP GET Requests.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param \BLW\Type\IURI $URI
     *            Address of request.
     * @param \BLW\Type\IURI $BaseURI
     *            Base URL of request used to resolve relative addresses.
     * @param array|Traversable $Headers
     *            Headers to add to request.
     * @return \BLW\Type\HTTP\IRequest Built request.
     */
    public function createGET(IURI $URI, IURI $BaseURI, $Headers = array());

    /**
     * Creates HTTP HEAD Requests.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param \BLW\Type\IURI $URI
     *            Address of request.
     * @param \BLW\Type\IURI $BaseURI
     *            Base URL of request used to resolve relative addresses.
     * @param array|Traversable $Headers
     *            Headers to add to request.
     * @return \BLW\Type\HTTP\IRequest Built request.
     */
    public function createHEAD(IURI $URI, IURI $BaseURI, $Headers = array());

    /**
     * Creates HTTP POST Requests.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param \BLW\Type\IURI $URI
     *            Address of request.
     * @param \BLW\Type\IURI $BaseURI
     *            Base URL of request used to resolve relative addresses.
     * @param array|Traversable $Data
     *            Post data to send with keys as field names and values as field values.
     * @param array|Traversable $Headers
     *            Headers to add to request.
     * @return \BLW\Type\HTTP\IRequest Built request.
     */
    public function createPOST(IURI $URI, IURI $BaseURI, $Data = null, $Headers = array());
}

return true;
