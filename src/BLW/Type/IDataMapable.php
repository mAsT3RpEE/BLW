<?php
/**
 * IDataMapable.php | Feb 10, 2014
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
 * @package BLW\Core
 * @version GIT 0.2.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type;

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
 * Interface for all objects that can be dynamically mapped.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+
 * | DATAMAPABLE                                       |
 * +---------------------------------------------------+
 * | _DataMapper: DataMapper                           |
 * +---------------------------------------------------+
 * | __get(): _DataMapper->offsetGet()                 |
 * |                                                   |
 * | $name:  string                                    |
 * +---------------------------------------------------+
 * | __set(): _DataMapper->offsetSet()                 |
 * |                                                   |
 * | $name:   string                                   |
 * | $value:  mixed                                    |
 * +---------------------------------------------------+
 * | __isset(): _DataMapper->offsetExists()            |
 * |                                                   |
 * | $name:  string                                    |
 * +---------------------------------------------------+
 * | __unset(): _DataMapper->offsetUnset()             |
 * |                                                   |
 * | $name:  string                                    |
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
 * @property $_DataMapper \BLW\Type\IDataMapper [protected] Pointer to DataMapper of the object.
 */
interface IDataMapable
{

    /**
     * Map dynamic properties to DataMapper.
     *
     * <h4>Note:</h4>
     *
     * <p>Raises a <b>Warning</b> if property is not found.</p>
     *
     * <hr>
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param string $name
     *            Label of dynamic property. (case sensitive)
     * @return mixed Returns <code>null</code> if property does not exist.
     */
    public function __get($name);

    /**
     * Map dynamic properties to DataMapper.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param string $name
     *            Label of dynamic property. (case sensitive)
     * @return bool Returns a <code>TRUE</code> if property exists. <code>FALSE</code> otherwise.
     */
    public function __isset($name);

    /**
     * Map dynamic properties to DataMapper.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param string $name
     *            Label of dynamic property. (case sensitive)
     * @param mixed $value
     *            Value of dynamic property.
     * @return bool Returns a <code>IDataMapper</code> status code.
     */
    public function __set($name, $value);

    /**
     * Map dynamic properties from DataMapper.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param string $name
     *            Label of dynamic property. (case sensitive)
     * @return bool Returns a <code>TRUE</code> if property exists. <code>FALSE</code> otherwise.
     */
    public function __unset($name);
}

return true;
