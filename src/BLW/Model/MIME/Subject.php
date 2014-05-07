<?php
/**
 * Subject.php | Mar 12, 2014
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
 * @package BLW\MIME
 * @version GIT 0.2.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model\MIME;

use BLW\Model\InvalidArgumentException;


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
 * Header class for Subject.
 *
 * <h3>RFC822</h3>
 *
 * <pre>
 * subject := "Subject" ":" *text
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class Subject extends \BLW\Type\MIME\AHeader
{
    // All printable characters except NL / CR / "
    const TEXT = '[\p{L}\x08\x20\x21\x23-\x7e]+';

    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Subject</code> is not a string.
     *
     * @param string $Subject
     *            Value of Subject header
     */
    public function __construct($Subject)
    {
        // 1. Header type
        $this->_Type = 'Subject';

        // 2. Header value

        // Validate $Subject
        if (is_string($Subject) ?  : is_callable(array(
            $Subject,
            '__toString'
        ))) {

            // Subject
            $this->_Value = $this->parseSubject($Subject);
        }

        // Invalid $Subject
        else
            throw new InvalidArgumentException(0);
    }

    /**
     * Parse a string for subject.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param string $Test
     *            String to search.
     * @return string Returns empty string in case of error.
     */
    public static function parseSubject($Test)
    {
        // Subject Regex
        $Regex = sprintf('!%s!', self::TEXT);
        $Test  = @strval($Test);

        // Match Regex `text`
        if (preg_match($Regex, $Test, $m))
            return $m[0];

        // Default
        return '';
    }
}
