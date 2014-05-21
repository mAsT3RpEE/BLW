<?php
/**
 * IEmailAddress.php | Jan 26, 2014
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
 * @version GIT: 0.2.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type;

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
 * Interface for all email addresses.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +--------------------+
 * | EmailAddress                                      |<------| SERIALIZABLE       |
 * +---------------------------------------------------+       | ================== |
 * | [Personal]:       string                          |       | Serializable       |
 * | [Local]:          string                          |       +--------------------+
 * | [Domain]:         string                          |<------| ITERABLE           |
 * | [TLD]:            string                          |       +--------------------+
 * | [LocalAtom]:      string                          |<------| FACTORY            |
 * | [LocalQuoted]:    string                          |       +--------------------+
 * | [LocalObs]:       string                          |<------| ArrayAccess        |
 * | [DomainAtom]:     string                          |       +--------------------+
 * | [DomainLiteral]:  string                          |<------| Countable          |
 * | [DomainObs]:      string                          |       +--------------------+
 * +---------------------------------------------------+<------| IteratorAggregate  |
 * | createEmailString():                              |       +--------------------+
 * |                                                   |
 * | $Parts:  array                                    |
 * +---------------------------------------------------+
 * | __construct():                                    |
 * |                                                   |
 * | $Address:   string                                |
 * | $Personal:  string                                |
 * +---------------------------------------------------+
 * | getRegex(): string                                |
 * +---------------------------------------------------+
 * | buildParts(): array                               |
 * +---------------------------------------------------+
 * | isValid(): bool                                   |
 * +---------------------------------------------------+
 * | __tostring(): string                              |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\Core
 * @api     BLW
 * @since 0.1.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property array $_Storage Email parts
 */
interface IEmailAddress extends \BLW\Type\ISerializable, \BLW\Type\IIterable, \BLW\Type\IFactory, \IteratorAggregate, \ArrayAccess, \Countable
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
     * Get the number of public properties in the ArrayObject
     *
     * @api BLW
     * @since   1.0.0
     * @link http://www.php.net/manual/en/countable.count.php Countable::count()
     *
     * @return integer The number of public properties in the ArrayObject.
     */
    public function count();

    /**
     * Create a address string from individual EmailAddress components.
     *
     * @api BLW
     * @since 0.1.0
     *
     * @param array $Parts
     *            Parts generated by <code>IEmailAddress::parse()</code>.
     * @return string Generated URI. Returns empty string on failure.
     */
    public static function createEmailString(array $Parts);

    /**
     * Create a new iterator from an ArrayObject instance
     *
     * @api BLW
     * @since   1.0.0
     * @link http://www.php.net/manual/en/iteratoraggregate.getiterator.php IteratorAggregate::getIterator()
     *
     * @return \RecursiveArrayIterator An instance implementing <code>Iterator</code>.
     */
    public function getIterator();

    /**
     * Returns an email address regex.
     *
     * @api BLW
     * @since 0.1.0
     *
     * @param string $Name
     *            Name of regex:
     *
     * <ul>
     * <li><b>addr-spec</b>: Full email address regex</li>
     * <li><b>local-part</b>: mailbox</li>
     * <li><b>domain</b>: host</li>
     * <li><b>dot-atom</b>: Text separated by `.` / `-` / `_`</li>
     * <li><b>quoted-string</b>: String enclosed in double quotes (")</li>
     * <li><b>obs-local-part</b>: see rfc2882</li>
     * <li><b>dotmain-literal</b>: see rfc2882</li>
     * <li><b>obs-domain</b>: see rfc2882</li>
     * <li><b>atom</b>: see rfc2882</li>
     * <li><b>word</b>: see rfc2882</li>
     * <li><b>comment</b>: see rfc2882</li>
     * </ul>
     *
     * @return string PCRE regex.
     */
    public static function getRegex($Name = 'addr-spec');

    /**
     * Parse email address into various components.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param string $Address
     *            Email address to parse.
     * @param string $Personal
     *            Owner of email address
     * @return array Parsed parts:
     *
     * <ul>
     * <li><b>Personal</b>:</li>
     * <li><b>Local</b>:</li>
     * <li><b>Domain</b>:</li>
     * <li><b>TLD</b>:</li>
     * <li><b>LocalAtom</b>:</li>
     * <li><b>LocalQuoted</b>:</li>
     * <li><b>LocalObs</b>:</li>
     * <li><b>DomainAtom</b>:</li>
     * <li><b>DomainLiteral</b>:</li>
     * <li><b>DomainObs</b>:</li>
     * </ul>
     */
    public function parse($Address, $Personal = '');

    /**
     * Validates an email address.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @return boolean Returns <code>TRUE</code> if email is valid. <code>FALSE</code> otherwise.
     */
    public function isValid();

    /**
     * All objects must have a string representation.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @return string $this
     */
    public function __toString();
}
