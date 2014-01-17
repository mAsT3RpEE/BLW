<?php
/**
 * DOMElement.php | Dec 21, 2013
 *
 * Copyright (c) mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 *
 * @filesource
 * @copyright mAsT3RpEE's Zone
 * @license MIT
 */

/**
 * @package BLW\Core
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

/**
 * Class for working with URL's
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://www.php.net/manual/en/function.parse-url.php See reference
 */
class URL
{
    /**
     *
     * @var string $_URL Complete, sanitized URL.
     */
    private $_URL = '';

    /**
     * @var array $_Parameters ReadOnly Class Parameters.
     */
    private $_Parameters = NULL;

    /**
     * Constructor. Parses / Sanitizes and builds URL
     * @param string $URL URL to parse / sanitize.
     * @return void
     */
    public function __construct($URL)
    {
        $Parts = array();

        try {
            $Parts = parse_url(@strval($URL));
        }

        catch (\Exception $e) {}

        $this->_URL        = filter_var($URL, FILTER_SANITIZE_URL);
        $this->_Properties = array(
            'scheme'    => isset($Parts['scheme'])?   $Parts['scheme']   : NULL
            ,'host'     => isset($Parts['host'])?     $Parts['host']     : NULL
            ,'port'     => isset($Parts['port'])?     $Parts['port']     : NULL
            ,'user'     => isset($Parts['user'])?     $Parts['user']     : NULL
            ,'pass'     => isset($Parts['pass'])?     $Parts['pass']     : NULL
            ,'path'     => isset($Parts['path'])?     $Parts['path']     : NULL
            ,'query'    => isset($Parts['query'])?    $Parts['query']    : NULL
            ,'fragment' => isset($Parts['fragment'])? $Parts['fragment'] : NULL
        );
    }

    /**
     * Provide read only access to properties.
     * @param string $name Label of property to retrieve
     * @return void
     */
    public function __get($name)
    {
        return isset($this->_Properties[$name])
            ? $this->_Properties[$name]
            : NULL
        ;
    }

    /**
     * Returns the string representation of the url.
     * @return string URL
     */
    public function __toString()
    {
        return $this->_URL;
    }
}

return true;