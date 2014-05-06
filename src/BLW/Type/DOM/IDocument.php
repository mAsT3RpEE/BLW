<?php
/**
 * IDocument.php | Apr 2, 2014
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
 * @package BLW\DOM
 * @version GIT 0.2.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type\DOM;

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
 * Replacement / Improvement of PHP DOMDocument class.
 *
 * <h4>Note</h4>
 *
 * <p>Classes must also extend <code>DOMDocument</code> of
 * implement each of its functions.</p>
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +---------------+
 * | DOMDOCUMENT                                       |<------| DOMDocument   |
 * +---------------------------------------------------+       +---------------+
 * | __construct():                                    |<------| SERIALIZABLE  |
 * |                                                   |       | ============= |
 * | $version:   string                                |       | Serializable  |
 * | $encoding:  string                                |       +---------------+
 * | $element:   string                                |<------| Iterable      |
 * +---------------------------------------------------+       +---------------+
 * | filterXPath(): IContainer                         |
 * |                                                   |
 * | $Query:  String (XPath)                           |
 * +---------------------------------------------------|
 * | filter(): filterXPath()                           |
 * |                                                   |
 * | $Selector:  string (CSS Selector)                 |
 * +---------------------------------------------------+
 * | __toString(): string                              |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\DOM
 * @api BLW
 * @since 0.1.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
interface IDocument extends \BLW\Type\ISerializable, \BLW\Type\IIterable
{

    /**
     * Constructor
     *
     * @link http://www.php.net/manual/en/domdocument.construct.php DOMDocument::__construct()
     *
     * @param string $version
     *            [optional] The version number of the document as part of the XML declaration.
     * @param string $encoding
     *            [optional] The encoding of the document as part of the XML declaration.
     * @param sring $element
     *            [optional] Class used to create elements in the document. Must extend <code>DOMElement</code>.
     */
    public function __construct($version = '1.0', $encoding = 'utf-8', $element = '\\BLW\\Model\\DOMElement');

    /**
     * Filters the document for elements matching the xpath query.
     *
     * @api BLW
     * @since 0.1.0
     *
     * @param string $Query
     *            XPath.
     * @return \BLW\Type\IContainer Returns an instance of <code>IContainer</code> containing all matched elements.
     */
    public function filterXPath($Query);

    /**
     * Filters the document for elements matching the css selector.
     *
     * @api BLW
     * @since 0.1.0
     * @link https://github.com/symfony/CssSelector/blob/master/CssSelector.php CssSelector
     * @uses \BLW\Type\DOM\IDocument::filterXPath() IDocument::filterXPath()
     *
     * @param string $Selector
     *            CSS Selector.
     * @return \BLW\Type\IContainer Returns an instance of <code>IContainer</code> containing all matched elements.
     */
    public function filter($Selector);

    /**
     * All objects must have a string representation.
     *
     * @api BLW
     * @since 0.2.0
     * @link http://www.php.net/manual/en/language.oop5.magic.php#object.tostring __toString()
     *
     * @return string $this
     */
    public function __toString();
}

return true;
