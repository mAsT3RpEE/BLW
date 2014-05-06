<?php
/**
 * AEmailAddress.php | Jan 26, 2014
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

use ReflectionMethod;

use BLW\Model\InvalidArgumentException;


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
 * Trait for all email addresses.
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
 * | [LocalAtom]:      string                          |       | ArrayAccess        |
 * | [LocalQuoted]:    string                          |       +--------------------+
 * | [LocalObs]:       string                          |       | Countable          |
 * | [DomainAtom]:     string                          |       +--------------------+
 * | [DomainLiteral]:  string                          |       | IteratorAggregate  |
 * | [DomainObs]:      string                          |       +--------------------+
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
 * @api BLW
 * @since 0.1.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
trait TEmailAddress
{

    use BLW\Type\TSerializable;
    use BLW\Type\TIterable;

#############################################################################################
# ArrayAccess Trait
#############################################################################################

    /**
     * Storage for ArrayAcces operations.
     *
     * @var unknown
     */
    protected $_Storage = array();

#############################################################################################
# EmailAddress Trait
#############################################################################################

    /**
     * Parts of an email address.
     *
     * @var array $_Default
     */
    protected static $_Default = array(
        'Personal'       => '',
        'Local'          => '',
        'Domain'         => '',
        'TLD'            => '',
        'LocalAtom'      => '',
        'LocalQuoted'    => '',
        'LocalObs'       => '',
        'DomainAtom'     => '',
        'DomainLiteral'  => '',
        'DomainObs'      => ''
    );

#############################################################################################




#############################################################################################
# ArrayAccess trait
#############################################################################################

    /**
     * Returns whether the requested index exists
     *
     * @api BLW
     * @since 1.0.0
     * @link http://www.php.net/manual/en/arrayaccess.offsetexists.php ArrayAccess::offsetExists()
     *
     * @param mixed $index
     *            The index being checked.
     * @return bool <code>TRUE</code> if the requested index exists, <code>FALSE</code> otherwise.
     */
    public function offsetExists($index)
    {
        return isset($this->_Storage[$index]);
    }

    /**
     * Returns the value at the specified index
     *
     * @api BLW
     * @since 1.0.0
     * @link http://www.php.net/manual/en/arrayaccess.offsetget.php ArrayAccess::offsetGet()
     *
     * @param mixed $index
     *            The index with the value.
     * @return mixed The value at the specified index or <code>FALSE</code>.
     */
    public function offsetGet($index)
    {
        if (isset($this->_Storage[$index]))
            return $this->_Storage[$index];

        else
            trigger_error(sprintf('Undefined index %s[%s]', get_class($this), @strval($index)), E_USER_NOTICE);

        return null;
    }

    /**
     * Sets the value at the specified index to newval
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetset.php ArrayAccess::offsetSet()
     *
     * @param mixed $index
     *            The index being set.
     * @param mixed $newval
     *            The new value for the index.
     */
    public function offsetSet($index, $newval)
    {
        trigger_error(sprintf('Cannot modify readonly index %s[%s]', get_class($this), @strval($index)), E_USER_NOTICE);
    }

    /**
     * Unsets the value at the specified index
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetunset.php ArrayAccess::offsetUnset()
     *
     * @param mixed $index
     *            The index being unset.
     */
    public function offsetUnset($index)
    {
        trigger_error(sprintf('Cannot modify readonly index %s[%s]', get_class($this), @strval($index)), E_USER_NOTICE);
    }

    /**
     * Get the number of public properties in the ArrayObject
     *
     * @link http://www.php.net/manual/en/countable.count.php Countable::count()
     *
     * @return int The number of public properties in the ArrayObject.
     */
    public function count()
    {
        return count($this->_Storage);
    }

#############################################################################################
# IteratorAggregate trait
#############################################################################################

    /**
     * Create a new iterator from an ArrayObject instance
     *
     * @link http://www.php.net/manual/en/iteratoraggregate.getiterator.php IteratorAggregate::getIterator()
     *
     * @return \RecursiveArrayIterator An instance implementing <code>Iterator</code>.
     */
    public function getIterator()
    {
        return new \RecursiveArrayIterator($this->_Storage);
    }

#############################################################################################
# Factory Trait
#############################################################################################

    /**
     * Return an array of factory methods associated with the class.
     *
     * @return \ReflectionMethod[] Array of factory methods.
     */
    public static function getFactoryMethods()
    {
        return array(
            new ReflectionMethod(get_called_class(), 'createEmailString')
        );
    }

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
    public static function createEmailString(array $Parts)
    {
        // Gather email components

        // is there a valid local?
        if (preg_match('!^' . self::getRegex('local-part') . '$!', @$Parts['Local'], $m)) {
            $Local = $m[0];
        }

        // is there a sanitizable local
        elseif (! empty($Parts['Local'])) {
            $Local = '"' . addcslashes($Parts['Local'], '\\"') . '"';
        }

        // Default
        else {
            $Local = '';
        }

        // is there a valid domain?
        $Domain = preg_match('!^' . self::getRegex('domain') . '$!', @$Parts['Domain'], $m) ? $m[0] : '';

        // is there a valid personal
        $Personal = preg_match('!^' . self::getRegex('word') . '+$!', @$Parts['Personal'], $m) ? $m[0] : '';

        // Build Email address
        $return = '';

        // Does local part exist?
        if (! empty($Local)) {

            // personal
            if (! empty($Personal))
                $return .= "$Personal <";

            // local
            $return .= $Local;

            // domain
            if (! empty($Domain))
                $return .= "@$Domain";

            // personal
            if (! empty($Personal))
                $return .= '>';
        }

        // Done
        return $return;
    }

#############################################################################################
# EmailAddress trait
#############################################################################################

    /**
     * Constructor
     *
     * @throws InvalidArgumentException If <code>$Address</code> / <code>$Personal</code> are not a strings
     *
     * @param string|IEmailAddress $Address
     *            Email address.
     * @param string $Personal
     *            Name of email address owner.
     */
    public function __construct($Address, $Personal = '')
    {
        // I
        if (is_string($Personal) ?: is_callable(array(
            $Personal,
            '__toString'
        ))) {

            // Import parts from other email address
            if ($Address instanceof IEmailAddress) {
                $this->_Storage = iterator_to_array($Address) + self::$_Default;
            }

            // Build parts from parameters
            elseif (is_string($Address) ?  : is_callable(array(
                $Address,
                '__toString'
            ))) {
                $this->_Storage = $this->parse($Address, $Personal);
            }

            // Invalid $Address
            else
                throw new InvalidArgumentException(0);
        }

        // $Invalid personal
        else
            throw new InvalidArgumentException(1);
    }

    /**
     * Returns an email address regex.
     *
     * @author Cal Henderson <cal@iamcal.com>
     *         @licence GPLv3 - http://www.gnu.org/copyleft/gpl.html
     * @link https://github.com/iamcal/rfc822 Source
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
    public static function getRegex($Name = 'addr-spec')
    {
        static $cache = array();

        if (isset($cache[$Name]))
            return $cache[$Name];

        // ##################################################################################
        //
        // NO-WS-CTL = %d1-8 / ; US-ASCII control characters
        // %d11 / ; that do not include the
        // %d12 / ; carriage return, line feed,
        // %d14-31 / ; and white space characters
        // %d127
        // ALPHA = %x41-5A / %x61-7A ; A-Z / a-z /
        // DIGIT = %x30-39

        $no_ws_ctl = "[\\x01-\\x08\\x0b\\x0c\\x0e-\\x1f\\x7f]";
        $alpha     = "[\\x41-\\x5a\\x61-\\x7a]";
        $unicode   = "\\p{L&}";
        $digit     = "[\\x30-\\x39]";
        $cr        = "\\x0d";
        $lf        = "\\x0a";
        $crlf      = "(?:$cr$lf)";

        // ##################################################################################
        //
        // obs-char = %d0-9 / %d11 / ; %d0-127 except CR and
        // %d12 / %d14-127 ; LF
        // obs-text = *LF *CR *(obs-char *LF *CR)
        // text = %d1-9 / ; Characters excluding CR and LF
        // %d11 /
        // %d12 /
        // %d14-127 /
        // obs-text
        // obs-qp = "\" (%d0-127)
        // quoted-pair = ("\" text) / obs-qp

        $obs_char = "[\\x00-\\x09\\x0b\\x0c\\x0e-\\x7f]";
        $obs_text = "(?:$lf*$cr*(?:$obs_char$lf*$cr*)*)";
        $text     = "(?:[\\x01-\\x09\\x0b\\x0c\\x0e-\\x7f]|$obs_text)";

        // there's an issue with the definition of 'text', since 'obs_text' can
        // be blank and that allows qp's with no character after the slash. we're
        // treating that as bad, so this just checks we have at least one
        // (non-CRLF) character

        $text        = "(?:$lf*$cr*$obs_char$lf*$cr*)";
        $obs_qp      = "(?:\\x5c[\\x00-\\x7f])";
        $quoted_pair = "(?:\\x5c$text|$obs_qp)";

        // ##################################################################################
        //
        // obs-FWS = 1*WSP *(CRLF 1*WSP)
        // FWS = ([*WSP CRLF] 1*WSP) / ; Folding white space
        // obs-FWS
        // ctext = NO-WS-CTL / ; Non white space controls
        // %d33-39 / ; The rest of the US-ASCII
        // %d42-91 / ; characters not including "(",
        // %d93-126 ; ")", or "\"
        // ccontent = ctext / quoted-pair / comment
        // comment = "(" *([FWS] ccontent) [FWS] ")"
        // CFWS = *([FWS] comment) (([FWS] comment) / FWS)
        //
        // note: we translate ccontent only partially to avoid an infinite loop
        // instead, we'll recursively strip *nested* comments before processing
        // the input. that will leave 'plain old comments' to be matched during
        // the main parse.

        $wsp      = "[\\x20\\x09]";
        $obs_fws  = "(?:$wsp+(?:$crlf$wsp+)*)";
        $fws      = "(?:(?:(?:$wsp*$crlf)?$wsp+)|$obs_fws)";
        $ctext    = "(?:$no_ws_ctl|[\\x21-\\x27\\x2A-\\x5b\\x5d-\\x7e])";
        $ccontent = "(?:$ctext|$quoted_pair)";
        $comment  = "(?:\\x28(?:$fws?$ccontent)*$fws?\\x29)";
        $cfws     = "(?:(?:$fws?$comment)*(?:$fws?$comment|$fws))";

        // these are the rules for removing *nested* comments. we'll just detect
        // outer comment and replace it with an empty comment, and recurse until
        // we stop.

        $outer_ccontent_dull = "(?:$fws?$ctext|$quoted_pair)";
        $outer_ccontent_nest = "(?:$fws?$comment)";
        $outer_comment       = "(?:\\x28$outer_ccontent_dull*(?:$outer_ccontent_nest$outer_ccontent_dull*)+$fws?\\x29)";

        // ##################################################################################
        //
        // atext = ALPHA / DIGIT / ; Any character except controls,
        // "!" / "#" / ; SP, and specials.
        // "$" / "%" / ; Used for atoms
        // "&" / "'" /
        // "*" / "+" /
        // "-" / "/" /
        // "=" / "?" /
        // "^" / "_" /
        // "`" / "{" /
        // "|" / "}" /
        // "~"
        // atom = [CFWS] 1*atext [CFWS]

        $atext = "(?:$alpha|$digit|$unicode|[\\x21\\x23-\\x27\\x2a\\x2b\\x2d\\x2f\\x3d\\x3f\\x5e\\x5f\\x60\\x7b-\\x7e])";
        $atom  = "(?:$cfws?(?:$atext)+$cfws?)";

        // ##################################################################################
        //
        // qtext = NO-WS-CTL / ; Non white space controls
        // %d33 / ; The rest of the US-ASCII
        // %d35-91 / ; characters not including "\"
        // %d93-126 ; or the quote character
        // qcontent = qtext / quoted-pair
        // quoted-string = [CFWS]
        // DQUOTE *([FWS] qcontent) [FWS] DQUOTE
        // [CFWS]
        // word = atom / quoted-string

        $qtext         = "(?:$no_ws_ctl|[\\x21\\x23-\\x5b\\x5d-\\x7e])";
        $qcontent      = "(?:$qtext|$quoted_pair)";
        $quoted_string = "(?:$cfws?\\x22(?:$fws?$qcontent)*$fws?\\x22$cfws?)";

        // changed the '*' to a '+' to require that quoted strings are not empty

        $quoted_string = "(?:$cfws?\\x22(?:$fws?$qcontent)+$fws?\\x22$cfws?)";
        $word          = "(?:$atom|$quoted_string)";

        // ##################################################################################
        //
        // obs-local-part = word *("." word)
        // obs-domain = atom *("." atom)

        $obs_local_part = "(?:$word(?:\\x2e$word)*)";
        $obs_domain     = "(?:$atom(?:\\x2e$atom)*)";

        // ##################################################################################
        //
        // dot-atom-text = 1*atext *("." 1*atext)
        // dot-atom = [CFWS] dot-atom-text [CFWS]

        $dot_atom_text  = "(?:$atext+(?:\\x2e$atext+)*)";
        $dot_atom       = "(?:$cfws?$dot_atom_text$cfws?)";

        // ##################################################################################
        //
        // domain-literal = [CFWS] "[" *([FWS] dcontent) [FWS] "]" [CFWS]
        // dcontent = dtext / quoted-pair
        // dtext = NO-WS-CTL / ; Non white space controls
        //
        // %d33-90 / ; The rest of the US-ASCII
        // %d94-126 ; characters not including "[",
        // ; "]", or "\"

        $dtext           = "(?:$no_ws_ctl|[\\x21-\\x5a\\x5e-\\x7e])";
        $dcontent        = "(?:$dtext|$quoted_pair)";
        $domain_literal  = "(?:$cfws?\\x5b(?:$fws?$dcontent)*$fws?\\x5d$cfws?)";

        // ##################################################################################
        //
        // local-part = dot-atom / quoted-string / obs-local-part
        // domain = dot-atom / domain-literal / obs-domain
        // addr-spec = local-part "@" domain

        $local_part  = "(?P<local>(?P<local_atom>$dot_atom)|(?P<local_quoted>$quoted_string)|(?P<local_obs>$obs_local_part))";
        $domain      = "(?P<domain>(?P<domain_atom>$dot_atom)|(?P<domain_literal>$domain_literal)|(?P<domain_obs>$obs_domain))";
        $addr_spec   = "(?P<addr_spec>$local_part\\x40$domain)";

        // ##################################################################################
        //
        // Cache results

        $cache = array(
            'addr-spec'       => $addr_spec,

            'local-part'      => $local_part,
            'domain'          => $domain,

            'dot-atom'        => $dot_atom,
            'quoted-string'   => $quoted_string,
            'obs-local-part'  => $obs_local_part,

            'dotmain-literal' => $domain_literal,
            'obs-domain'      => $obs_domain,

            'atom'            => $atom,
            'word'            => $word,
            'comment'         => $comment
        );

        return @$cache[$Name];
    }

    /**
     * Parse email address into various components.
     *
     * @uses \BLW\Type\AURL::parseTLD() AURL::parseTLD()
     *
     * @param string $Address
     *            Email address to parse.
     * @param string $Personal
     *            Owner of email address
     * @return array Parsed parts:
     *         <ul>
     *         <li><b>Personal</b>:</li>
     *         <li><b>Local</b>:</li>
     *         <li><b>Domain</b>:</li>
     *         <li><b>TLD</b>:</li>
     *         <li><b>LocalAtom</b>:</li>
     *         <li><b>LocalQuoted</b>:</li>
     *         <li><b>LocalObs</b>:</li>
     *         <li><b>DomainAtom</b>:</li>
     *         <li><b>DomainLiteral</b>:</li>
     *         <li><b>DomainObs</b>:</li>
     *         </ul>
     */
    public function parse($Address, $Personal = '')
    {
        $Parts = array();

        // Address
        if (preg_match('!^' . $this->getRegex() . '$!', @strval($Address), $m)) {
            $Parts['Address']       = isset($m[0]) ? $m[0] : '';
            $Parts['Local']         = isset($m['local']) ? $m['local'] : '';
            $Parts['LocalAtom']     = isset($m['local_atom']) ? $m['local_atom'] : '';
            $Parts['LocalQuoted']   = isset($m['local_quoted']) ? $m['local_quoted'] : '';
            $Parts['LocalObs']      = isset($m['local_obs']) ? $m['local_obs'] : '';
            $Parts['Domain']        = isset($m['domain']) ? $m['domain'] : '';
            $Parts['DomainAtom']    = isset($m['domain_atom']) ? $m['domain_atom'] : '';
            $Parts['DomainLiteral'] = isset($m['domain_literal']) ? $m['domain_literal'] : '';
            $Parts['DomainObs']     = isset($m['domain_obs']) ? $m['domain_obs'] : '';
        }

        // Personal
        $Parts['Personal'] = trim(substr(str_replace(array(
            '\xa',
            '\xd'
        ), ' ', @strval($Personal)), 0, 63));

        // TLD
        if (! empty($Parts['Domain']) ? empty($Parts['DomainLiteral']) : false)
            $Parts['TLD'] = AURI::parseTLD($Parts['Domain']);

        // Done
        return array_merge(self::$_Default, $Parts);
    }

    /**
     * Validates an email address.
     *
     * @author Cal Henderson <cal@iamcal.com>
     *         @licence GPLv3 - http://www.gnu.org/copyleft/gpl.html
     * @link https://github.com/iamcal/rfc822 RFC
     *
     * @return bool Returns <code>TRUE</code> if email is valid. <code>FALSE</code> otherwise.
     */
    public function isValid()
    {
        $Range = function ($s, $l, $h)
        {
            $len = @strlen($s);
            return ($len > $l && $len < $h);
        };

        switch (true) {
            // Valid Mailbox and domain
            case empty($this->_Storage['Local']):
            case empty($this->_Storage['Domain']):
            // Proper size
            case ! $Range($this->_Storage['Address'], 4, 255):
            case ! $Range($this->_Storage['Local'], 1, 64):

                return false;
        }

        // ##################################################################################
        //
        // restrictions on domain-literals from RFC2821 section 4.1.3
        //
        // RFC4291 changed the meaning of :: in IPv6 addresses - i can mean one or
        // more zero groups (updated from 2 or more).
        //

        if (! empty($this->_Storage['DomainLiteral'])) {

            $Snum                 = "([\x30-\x39]{1,3})";
            $IPv4_address_literal = "$Snum\x2e$Snum\x2e$Snum\x2e$Snum";
            $IPv6_hex             = "(?:[\x30-\x39\x41-\x5a\x61-\x7a]{1,4})";
            $IPv6_full            = "IPv6\x3a$IPv6_hex(?:\x3a$IPv6_hex){7}";
            $IPv6_comp_part       = "(?:$IPv6_hex(?:\:$IPv6_hex){0,7})?";
            $IPv6_comp            = "IPv6\:($IPv6_comp_part\:\:$IPv6_comp_part)";
            $IPv6v4_full          = "IPv6\:$IPv6_hex(?:\:$IPv6_hex){5}\:$IPv4_address_literal";
            $IPv6v4_comp_part     = "$IPv6_hex(?:\:$IPv6_hex){0,5}";
            $IPv6v4_comp          = "IPv6\:((?:$IPv6v4_comp_part)?\:\:(?:$IPv6v4_comp_part\:)?)$IPv4_address_literal";

            // ##################################################################################
            //
            // IPv4 is simple
            //

            if (preg_match("!^\[$IPv4_address_literal\]$!", $this->_Storage['Domain'], $m)) {

                switch (true) {
                    case (intval($m[1]) > 255):
                    case (intval($m[2]) > 255):
                    case (intval($m[3]) > 255):
                    case (intval($m[4]) > 255):
                        return false;
                }
            }

            else {

                // ##################################################################################
                //
                // this should be IPv6 - a bunch of tests are needed here :)
                //

                for ($x = 0; $x < 100; $x ++) {

                    // Full IPv6
                    if (preg_match("!^\[$IPv6_full\]$!", $this->_Storage['Domain']))
                        break;

                    elseif (preg_match("!^\[$IPv6_comp\]$!", $this->_Storage['Domain'], $m)) {

                        list ($a, $b) = explode('::', $m[1]);

                        $folded = (strlen($a) && strlen($b)) ? "$a:$b" : "$a$b";
                        $groups = explode(':', $folded);

                        if (count($groups) > 7)
                            return false;

                        break;
                    }

                    // Full IPv6v4
                    elseif (preg_match("!^\[$IPv6v4_full\]$!", $this->_Storage['Domain'], $m)) {

                        switch (true) {
                            case (intval($m[1]) > 255):
                            case (intval($m[2]) > 255):
                            case (intval($m[3]) > 255):
                            case (intval($m[4]) > 255):
                                return false;
                        }

                        break;
                    }

                    // What comes next
                    elseif (preg_match("!^\[$IPv6v4_comp\]$!", $this->_Storage['Domain'], $m)) {

                        list ($a, $b) = explode('::', $m[1]);

                        $b      = substr($b, 0, - 1); // remove the trailing colon before the IPv4 address
                        $folded = (strlen($a) && strlen($b)) ? "$a:$b" : "$a$b";
                        $groups = explode(':', $folded);

                        if (count($groups) > 5)
                            return false;

                        break;
                    }

                    // OOPS
                    else
                        return false;
                }
            }
        }

        else {

            // ##################################################################################
            //
            // this is allowed by both dot-atom and obs-domain, but is un-routeable on the
            // public internet, so we'll fail it (e.g. user@localhost)
            //

            if (empty($this->_Storage['TLD']))
                return false;

            // ##################################################################################
            //
            // the domain is either dot-atom or obs-domain - either way, it's
            // made up of simple labels and we split on dots and check TLD
            //

            $labels = explode('.', $this->_Storage['Domain']);

            //
            // checks on each label
            //

            foreach ($labels as $label) {

                $i = strlen($label);

                switch (true) {
                    case ($i > 63 || $i < 1):
                    case ($label[0] == '-'):
                    case ($label[$i - 1] == '-'):

                        return false;
                }
            }
        }

        return true;
    }

    /**
     * All objects must have a string representation.
     *
     * @return string $this
     */
    public function __toString()
    {
        return $this->createEmailString($this->_Storage);
    }

    /**
     * Get the ID of the object.
     *
     * @return string Current ID.
     */
    public function getID()
    {
        return md5(strval($this));
    }

#############################################################################################

}

return true;
