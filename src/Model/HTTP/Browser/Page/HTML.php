<?php
/**
 * HTML.php | Apr 16, 2014
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
namespace BLW\Model\HTTP\Browser\Page;

use BLW\Type\IURI;
use BLW\Type\IWrapper;
use BLW\Type\IMediator;
use BLW\Type\DOM\IDocument;
use BLW\Type\MIME\IHead;

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
 * Class for HTML DOM Pages
 *
 * @package BLW\HTTP
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class HTML extends \BLW\Type\HTTP\Browser\APage
{

    /**
     * Constructor
     *
     * @param \BLW\Type\DOM\IDocument $Component
     *            DOM Document loaded with HTML.
     * @param \BLW\Type\IURI $Base
     *            Base URI which relative URI's are resolved against.
     * @param IHead $RequestHead
     *            Request headers of page.
     * @param IHead $ResponseHead
     *            Response headers of page.
     * @param \BLW\Type\IMediator $Mediator
     *            [optional] Meiator of Page.
     * @param integer $flags
     *            [optional] IWrapper::FLAGS
     */
    public function __construct(IDocument $Component, IURI $Base, IHead $RequestHead, IHead $ResponseHead, IMediator $Mediator = null, $flags = IWrapper::WRAPPER_FLAGS)
    {
        // Properties
        $this->_Component    = &$Component;
        $this->_Base         = $Base;
        $this->_RequestHead  = $RequestHead;
        $this->_ResponseHead = $ResponseHead;

        // Mediator
        $this->_MediatorID = 'Browser';

        if ($Mediator) {
            $this->setMediator($Mediator);
        }
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
