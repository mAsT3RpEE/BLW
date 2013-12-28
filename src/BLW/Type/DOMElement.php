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
 * Upgrades DOMElement php class.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://www.php.net/manual/en/class.domelement.php See reference
 */
class DOMElement extends \DOMElement
{
    /**
     * Parses a HTML string and returns DOMNodeList of toplevel elements.
     * @param string $HTML HTML String to parse.
     * @throws \BLW\InvalidArgumentException If <code>$HTML</code> is not valid markup.
     * @return \DOMNodeList Nodes of parsed string.
     */
    static function ParseHTML($HTML)
    {
        // Validate HTML
        if(!is_string($HTML) || empty($HTML)) {
            throw new \BLW\Model\InvalidArgumentException(0);
            return array();
        }

        // Disable errors
        $current         = libxml_use_internal_errors(true);
        $disableEntities = libxml_disable_entity_loader(true);

        // Convert HTML
        if (function_exists('mb_convert_encoding') && in_array('UTF-8', mb_list_encodings())) {
            $HTML = mb_convert_encoding($HTML, 'HTML-ENTITIES', 'UTF-8');
        }

        // Enable errors
        libxml_use_internal_errors($current);
        libxml_disable_entity_loader($disableEntities);

        // Load HTML
        $Doc = new \DOMDocument('1.0', 'UTF-8');
        $Doc->registerNodeClass('DOMElement', __CLASS__);
        $Doc->loadHTML($HTML);

        // Return Element List
        return preg_match('/<html/i', $HTML) > 0
            ? $Doc->documentElement->childNodes
            : (preg_match('/<title/i', $HTML) > 0
            	? $Doc->getElementsByTagName('title')
                : (preg_match('/<body/i', $HTML) > 0
                    ? $Doc->getElementsByTagName('body')
                    : $Doc->documentElement->lastChild->childNodes
                )
            )
        ;
    }

    /**
     * Wrapper for <code>DOMDocument::createElement()</code>
     * @param string $Name Tag name of element.
     * @param string $Value Value of element if applicable.
     * @link http://www.php.net/manual/en/domdocument.createelement.php See reference
     */
    public function createElement($Name, $Value = NULL)
    {
        return is_null($Value)
            ? $this->ownerDocument->createElement($Name)
            : $this->ownerDocument->createElement($Name, $Value)
        ;
    }

    /**
     * Returns HTML string of this element.
     * @return string Element HTML.
     */
    public function outerHTML()
    {
        if (version_compare(PHP_VERSION, '5.3.6', '>=')) {
            return $this->ownerDocument->saveHTML($this);
        }

        else {

            $Doc = new \DOMDocument('1.0', 'UTF-8');
            $Doc->registerNodeClass('DOMElement', __CLASS__);

            $Doc->appendChild($Doc->importNode($this, true));
            $HTML = $Doc->saveHTML();
            unset($Doc);
            $HTML = $this->tagName != 'html'
                ? rtrim(preg_replace(array('/^.*<body[^>]*>/i', '/<\/body[^>]*>.*$/i'), '', $HTML))
                : rtrim($HTML)
            ;

            return $HTML;
        }
    }

    /**
     * Inner HTML of
     * @return string
     */
    public function innerHTML()
    {
        $innerHTML = '';

        if (version_compare(PHP_VERSION, '5.3.6', '>=')) {
            foreach($this->childNodes as $child) {
                $innerHTML .= $child->ownerDocument->saveHTML($child);
            }
        }

        else {

            foreach($this->childNodes as $child) {
                $Doc = new \DOMDocument('1.0', 'UTF-8');
                $Doc->registerNodeClass('DOMElement', __CLASS__);
                $Doc->appendChild($Doc->importNode($child, true));
                $HTML = $Doc->saveHTML();
                $innerHTML .= $this->tagName != 'html'
                    ? rtrim(preg_replace(array('/^.*<body[^>]*>/i', '/<\/body[^>]*>.*$/i'), '', $HTML))
                    : rtrim($HTML)
                ;
            }

            unset($Doc, $HTML);
        }

        return $innerHTML;
    }

    /**
     * Set / Get current tag of node.
     * @note You should replace current node with return value if modifying node.
     * @param string $Tag New tag name.
     * @throws \BLW\InvalidArgumentException if tag is not null or a valid node name.
     * @return boolean|\DOMElement returns false on failure or new node on success.
     */
    public function tag($Tag = NULL)
    {
        if(is_null($Tag)) {
            return $this->tagName;
        }

        elseif(($New = $this->ownerDocument->createElement($Tag)) instanceof \DOMElement) {

            foreach ($this->attributes as $Attribute) {
                $New->setAttribute($Attribute->nodeName, $Attribute->nodeValue);
            }

            while ($this->firstChild) {
                $New->appendChild($this->firstChild);
            }


            return $this->parentNode->replaceChild($New, $this)
                ? $New
                : false
            ;
        }

        else {
            throw new \BLW\Model\InvalidArgumentException(0);
            return false;
        }
    }
}

return ;