<?php
/**
 * IHeader.php | Mar 08, 2014
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
namespace BLW\Type\MIME;

use BLW\Type\IContainer;


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
 * Standard interface for MIME headers.
 *
 * <h3>RFC822</h3>
 *
 * <pre>
 * entity-headers           := [ content CRLF ]
 *                             [ encoding CRLF ]
 *                             [ id CRLF ]
 *                             [ description CRLF ]
 *                             [ disposition CRLF ]
 *                             *( MIME-extension-field CRLF )
 *
 * content                  := "Content-Type" ":" type "/" subtype
 *                             *(";" parameter)
 *                             ; Matching of media type and subtype
 *                             ; is ALWAYS case-insensitive.
 *
 * encoding                 := "Content-Transfer-Encoding" ":" mechanism
 *
 * id                       := "Content-ID" ":" msg-id
 *
 * description              := "Content-Description" ":" *text
 *
 * content-disposition      := "Content-Disposition" ":" disposition-type
 *                             *(";" parameter)
 *
 * MIME-extension-field     := <Any RFC 822 header field which
 *                              begins with the string "Content-">
 *                              http://www.ietf.org/rfc/rfc822.txt
 *
 * type                     := discrete-type / composite-type
 *
 * subtype                  := extension-token / iana-token
 *
 * parameter                := attribute "=" value
 *
 * mechanism                := "7bit" / "8bit" / "binary" /
 *                             "quoted-printable" / "base64" /
 *                             ietf-token / x-token
 *
 * disposition-type         := "attachment" / "inline" / token
 *
 * discrete-type            := "text" / "image" / "audio" / "video" /
 *                             "application" / extension-token
 *
 * composite-type           := "message" / "multipart" / extension-token
 *
 * extension-token          := ietf-token / x-token
 *
 * iana-token               := <A publicly-defined extension token. Tokens
 *                              of this form must be registered with IANA
 *                              as specified in RFC 2048.>
 *                              http://www.mhonarc.org/~ehood/MIME/2048/rfc2048.html
 *
 * ietf-token               := <An extension token defined by a
 *                              standards-track RFC and registered with IANA.>
 *
 * x-token                  := <The two characters "X-" or "x-" followed, with
 *                              no intervening white space, by any token>
 *
 * attribute                := token
 *                          ; Matching of attributes
 *                          ; is ALWAYS case-insensitive.
 *
 * value                    := token / quoted-string
 *
 * token                    := 1*<any (US-ASCII) CHAR except SPACE, CTLs,
 *                                or tspecials>
 *
 * tspecials                := "(" / ")" / "<" / ">" / "@" /
 *                             "," / ";" / ":" / "\" / <">
 *                             "/" / "[" / "]" / "?" / "="
 *                             ; Must be in quoted-string,
 *                             ; to use within parameter values
 * </pre>
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+
 * | MIME/HEADER                                       |
 * +---------------------------------------------------+
 * | RAW                                               |
 * | ENCODED                                           |
 * +---------------------------------------------------+
 * | _CRLF:   string                                   |
 * | _Type:   string                                   |
 * | _Value:  string                                   |
 * +---------------------------------------------------+
 * | parseParameter(): string                          |
 * |                                                   |
 * | $Atribute:  string                                |
 * | $Value:     string|int                            |
 * +---------------------------------------------------+
 * | parseAddressList(): string                        |
 * |                                                   |
 * | $List:  IContainer(IEmailAddress)                 |
 * +---------------------------------------------------+
 * | getType(): string                                 |
 * +---------------------------------------------------+
 * | getValue(): string                                |
 * +---------------------------------------------------+
 * | __toString(): string                              |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property string $_CRLF [protected] "\r\n"
 * @property string $_Type [protected] Type of header.
 * @property string $_Value [protected] Text of header.
 */
interface IHeader
{

    const SPACE           = '\x20';                                     // Regex for space
    const CTLS            = '[\x01-\x1f]';                              // US-ASCII control caracters
    const TSPECIALS       = '[\x22\x28\x29\x2c\x2f\x3a-\x40\x5b\x5d]';  // tspecials
    const TOKEN           = '[\x21\x23-\x27\x2b\x2d\x2e\x30-\x39\x41-\x5a\x5f\x61-\x7a]+';                      // US-ASCII CHAR except SPACE, CTLs, tspecials
    const EXTENTION_TOKEN = '[xX]\x2d[\x21\x23-\x27\x2b\x2d\x2e\x30-\x39\x41-\x5a\x5f\x61-\x7a]+';              // x-token
    const QVALUE          = '(?:\x3b\x20?q\x3d[\x21\x23-\x27\x2b\x2d\x2e\x30-\x39\x41-\x5a\x5f\x61-\x7a]+)';    // ";" "q" "=" qvalue

    /**
     * Returns the header type.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return string $_Type
     */
    public function getType();

    /**
     * Returns the RAW body of header
     *
     * @return string $_Value
     */
    public function getValue();

    /**
     * Creates parameter from attribute and value
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param string $Attribute
     * @param string|int $Value
     * @return string Formatted parameter.
     */
    public function parseParameter($Attribute, $Value);

    /**
     * Compiles container containing email addresses into rfc email address list.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param \BLT\Type\IContainer $Container
     *            Container to extract email addresses from.
     * @return string Formated address list. Returns <code>FALSE</code> on error.
     */
    public function parseAddressList(IContainer $Container);

    /**
     * All objects must have a string representation.
     *
     * @api BLW
     * @since 0.1.0
     *
     * @return string $this
     */
    public function __toString();
}
