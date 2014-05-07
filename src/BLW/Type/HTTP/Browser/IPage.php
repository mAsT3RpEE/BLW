<?php
/**
 * IPage.php | Apr 13, 2014
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
namespace BLW\Type\HTTP\Browser;

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
 * Interface for all HTTP Browser pages
 *
 * <h3>Note to Implementors</h3>
 *
 * <ul>
 * <li><code>IPage</code> objects should have <code>$_MediatorID</code>
 * set to `Browser`</li>
 * </ul>
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +----------------+       +--------------------+
 * | BROWSER\PAGE                                      |<------| WRAPPER        |<--+---| SERIALIZABLE       |
 * +---------------------------------------------------+       | ============== |   |   | ================== |
 * | _Base:          IURI                              |       | DOM\Document   |   |   | Serializable       |
 * | _RequestHead:   MIME\IHead                        |       +----------------+   |   +--------------------+
 * | _ResponseHead:  MIME\IHead                        |<------| MEDIATABLE     |   +---| COMPONENT MAPABLE  |
 * | _Created:       DateTime                          |       +----------------+   |   +--------------------+
 * | _Modified:      DateTime                          |                            +---| ITERABLE           |
 * | #RequestHead:   _RequestHead                      |                                +--------------------+
 * | #ResponseHead:  _ResponseHead                     |
 * | #Base:          _Base                             |
 * | #Created:       getCreated()                      |
 * |                 setCreated()                      |
 * | #Modified:      getModified()                     |
 * |                 setModified()                     |
 * | #Document:      _Component                        |
 * | #File:          _Component                        |
 * | __###():        _Component->###()                 |
 * |                 _Mediator->Trigger()              |
 * +---------------------------------------------------+
 * | getCreated(): DateTime                            |
 * +---------------------------------------------------+
 * | setCreated(): IDataMapper::STATUS                 |
 * |                                                   |
 * | $Date:  DateTime                                  |
 * +---------------------------------------------------+
 * | getModified(): DateTime                           |
 * +---------------------------------------------------+
 * | setModified(): IDataMapper::STATUS                |
 * |                                                   |
 * | $Date:  DateTime                                  |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\HTTP
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property \BLW\Type\IURI $_Base [protected] Base URI of page that relative URL's are resolved against.
 * @property \BLW\Type\MIME\IHead $_RequestHead [protected] Request Headers.
 * @property \BLW\Type\MIME\IHead $_ResponseHead [protected] Response Headers.
 * @property \DateTime $_Created [protected] Date of creation of page.
 * @property \DateTime $_Modified [protected] Date of modification of page.
 * @property \BLW\Type\MIME\IHead $RequestHead [readonly] $_RequestHead
 * @property \BLW\Type\MIME\IHead $ResponseHead [readonly] $_ResponseHead
 * @property \BLW\Type\IURI [readonly] $_ResponseHeader->URI
 * @property \DateTime $Created [dynamic] Invokes getCreated() and setCreated().
 * @property \DateTime $Modified [dynamic] Invokes getModified() and setModified().
 * @property \BLW\Type\DOM\IDocument $Document [readonly] $_Component
 * @property \BLW\Type\IFile $File [readonly] $_Component
 */
interface IPage extends \BLW\Type\IWrapper, \BLW\Type\IMediatable
{

    /**
     * Returns the date of creation of page.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return \DateTime $_Created
     */
    public function getCreated();

    /**
     * Sets the date of creation of the page.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param \DateTime $Created
     *            New date.
     * @return int Returns a <code>IDataMapper</code> status code.
     */
    public function setCreated($Created);

    /**
     * Returns the date of modification of the page.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return \DateTime $_Modified
     */
    public function getModified();

    /**
     * Sets the date of modification of the page.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param \DateTime $Modified
     *            New date.
     * @return int Returns a <code>IDataMapper</code> status code.
     */
    public function setModified($Modified);
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
