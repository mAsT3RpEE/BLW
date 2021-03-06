<?php
/**
 * AMessage.php | Jan 20, 2013
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
 * @version GIT: 0.2.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type\MIME;

use DateTime;
use BLW\Model\InvalidArgumentException;
use BLW\Model\GenericURI;
use BLW\Model\MIME\Generic as GenericHeader;
use BLW\Model\MIME\Accept;
use BLW\Model\MIME\AcceptCharset;
use BLW\Model\MIME\AcceptEncoding;
use BLW\Model\MIME\AcceptLanguage;
use BLW\Model\MIME\AcceptRanges;
use BLW\Model\MIME\Age;
use BLW\Model\MIME\Allow;
use BLW\Model\MIME\CacheControl;
use BLW\Model\MIME\Connection;
use BLW\Model\MIME\ContentBase;
use BLW\Model\MIME\ContentDescription;
use BLW\Model\MIME\ContentDisposition;
use BLW\Model\MIME\ContentEncoding;
use BLW\Model\MIME\ContentID;
use BLW\Model\MIME\ContentLanguage;
use BLW\Model\MIME\ContentLength;
use BLW\Model\MIME\ContentLocation;
use BLW\Model\MIME\ContentMD5;
use BLW\Model\MIME\ContentRange;
use BLW\Model\MIME\ContentTransferEncoding;
use BLW\Model\MIME\Date;
use BLW\Model\MIME\Expires;
use BLW\Model\MIME\IfModifiedSince;
use BLW\Model\MIME\LastModified;
use BLW\Model\MIME\Location;
use BLW\Model\MIME\MessageID;
use BLW\Model\MIME\MIMEVersion;
use BLW\Model\MIME\Pragma;
use BLW\Model\MIME\Range;
use BLW\Model\MIME\Referer;
use BLW\Model\MIME\Subject;
use BLW\Model\MIME\Trailer;
use BLW\Model\MIME\Vary;
use BLW\Model\MIME\Via;

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
 * Interface for MIME formated Message.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +------------------+
 * | MESSAGE                                           |<------| FACTORY          |
 * +---------------------------------------------------+       | ================ |
 * | _Head:  IHead                                     |       | createFromString |
 * | _Body:  IBody                                     |       | createHeader     |
 * +---------------------------------------------------+       +------------------+
 * | createFromString(): IMessage                      |
 * |                                                   |
 * | $String:  string                                  |
 * +---------------------------------------------------+
 * | createHeader() IHeader                            |
 * |                                                   |
 * | $Type:   string                                   |
 * | $Value:  string                                   |
 * +---------------------------------------------------+
 * | getHeader(): _Head                                |
 * +---------------------------------------------------+
 * | getBody(): _Body                                  |
 * +---------------------------------------------------+
 * | normalizeHeader(): string                         |
 * |                                                   |
 * | $Type:  string                                    |
 * +---------------------------------------------------+
 * | __toString(): string                              |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
abstract class AMessage implements \BLW\Type\MIME\IMessage
{

#############################################################################################
# MimeMessage Trait
#############################################################################################

    /**
     * Mime head.
     *
     * @var \BLW\Type\MIME\IHead $_Head
     */
    protected $_Head = null;

    /**
     * Mime body.
     *
     * @var \BLW\Type\MIME\IBody $_Body
     */
    protected $_Body = null;

#############################################################################################




#############################################################################################
# Factory Trait
#############################################################################################

    /**
     * Create a mime header from type and value.
     *
     * @see \BLW\Type\MIME\IHeader IHeader.
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Type</code> is empty or not a string.
     *
     * @param string $Type
     *            Header type.
     * @param string $Value
     *            Header value.
     * @return \BLW\Type\MIME\IHeader Genereted header.
     */
    public static function createHeader($Type, $Value)
    {
        $Type = trim(@substr($Type, 0, 64));

        // Validate $Type
        if (empty($Type)) {
            throw new InvalidArgumentException(0);

        // Is $Value scalar?
        } elseif (! is_scalar($Value) && ! is_callable(array(
            $Value,
            '__toString'
        ))) {
            throw new InvalidArgumentException(1);

        } else {

            // Normalize Type
            $Type = self::normalizeHeaderType($Type);

            switch (strval($Type)) {
                case 'Accept':
                    return new Accept($Value);
                case 'Accept-Charset':
                    return new AcceptCharset($Value);
                case 'Accept-Encoding':
                    return new AcceptEncoding($Value);
                case 'Accept-Language':
                    return new AcceptLanguage($Value);
                case 'Accept-Ranges':
                    return new AcceptRanges($Value);
                case 'Age':
                    return new Age($Value);
                case 'Allow':
                    return new Allow($Value);
                case 'Cache-Control':
                    return new CacheControl($Value);
                case 'Connection':
                    return new Connection($Value);
                case 'Content-Base':
                    return new ContentBase(new GenericURI($Value));
                case 'Content-Description':
                    return new ContentDescription($Value);
                case 'Content-Disposition':
                    return new ContentDisposition($Value);
                case 'Content-Encoding':
                    return new ContentEncoding($Value);
                case 'Content-ID':
                    return new ContentID($Value);
                case 'Content-Language':
                    return new ContentLanguage($Value);
                case 'Content-Length':
                    return new ContentLength($Value);
                case 'Content-Location':
                    return new ContentLocation(new GenericURI($Value));
                case 'Content-MD5':
                    return new ContentMD5($Value);
                case 'Content-Range':
                    return new ContentRange($Value);
                case 'Content-Transfer-Encoding':
                    return new ContentTransferEncoding($Value);
                case 'Date':
                    return new Date(new DateTime($Value));
                case 'Expires':
                    return new Expires(new DateTime($Value));
                case 'If-Modified-Since':
                    return new IfModifiedSince(new DateTime($Value));
                case 'Last-Modified':
                    return new LastModified(new DateTime($Value));
                case 'Location':
                    return new Location(new GenericURI($Value));
                case 'Message-ID':
                    return new MessageID($Value);
                case 'MIME-Version':
                    return new MIMEVersion($Value);
                case 'Pragma':
                    return new Pragma($Value);
                case 'Range':
                    return new Range($Value);
                case 'Referer':
                    return new Referer(new GenericURI($Value));
                case 'Subject':
                    return new Subject($Value);
                case 'Trailer':
                    return new Trailer($Value);
                case 'Vary':
                    return new Vary($Value);
                case 'Via':
                    return new Via($Value);
                // Default, Generic header
                default:
                    return new GenericHeader($Type, $Value);
            }
        }
    }

#############################################################################################
# MimeMessage Trait
#############################################################################################

    /**
     * Return the current mime header portion.
     *
     * @return \BLW\Type\MIME\IHead $_Head
     */
    public function getHeader()
    {
        return $this->_Head;
    }

    /**
     * Return the current mime body portion.
     *
     * @return \BLW\Type\MIME\IBody $_Body
     */
    public function getBody()
    {
        return $this->_Body;
    }

    /**
     * <h3>Example</h3>
     *
     * <pre>
     * content-type => Content-Type
     * CONTENT-TYPE => Content-Type
     * CoNTenT-md5  => Content-MD5
     * Mime-Message => MIME-Message
     * </pre>
     *
     * <hr>
     *
     * @param string $Type
     *            Header name / type / label
     * @return string Normalized header
     */
    public static function normalizeHeaderType($Type)
    {
        return preg_replace_callback('!\w+!', function ($m) {

            return $m[0] == 'id' || $m[0] == 'md5' || $m[0] == 'mime'
                ? strtoupper($m[0])
                : ucwords($m[0]);

        }, strtolower(trim($Type)));
    }

    /**
     * Parses string / array containing RFC MIME Headers followed by a body.
     *
     * @param string $String
     *            String to parse
     * @param array $Header
     *            Parsed headers with type as key and values as an array
     * @param array $Body
     *            Parsed body as an array of lines alternating with their delimeter (either \n or \r\n)
     */
    public static function parseParts($String, &$Header = null, &$Body = null)
    {
        $Header = array();
        $Body   = array();

        // Split messege up into lines
        $lines = is_array($String)
            ? $String
            : preg_split('!(\x0d?\x0a)!', @substr($String, 0), - 1, PREG_SPLIT_DELIM_CAPTURE);

        $len   = count($lines);

        for ($i = 0; $i < $len; $i += 2):

            $current = $lines[$i];

            // Current line is empty?
            if (empty($current)) {

                // If more content? Add to body
                if ($i < $len - 1) {
                    $Body = array_slice($lines, $i + 2);
                }

                // Break
                break;
            }

            // No? Parse header
            elseif (strpos($current, ':')) {

                // Split along ':'
                $Parts = explode(':', $current, 2);
                $Type  = self::normalizeHeaderType($Parts[0]);
                $Value = isset($Parts[1])
                    ? trim($Parts[1])
                    : '';

                // Add header to headers

                // Header doesnt exist? Create.
                if (! isset($Header[$Type])) {
                    $Header[$Type] = array(
                        $Value
                    );

                // Header already exists? Add.
                } else {
                    $Header[$Type][] = $Value;
                }
            }

        endfor;
    }

    /**
     * All objects must have a string representation.
     *
     * @return string $this
     */
    public function __toString()
    {
        return $this->getHeader() . $this->getBody();
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
