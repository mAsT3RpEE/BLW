<?php
/**
 * Element.php | Nov 29, 2013
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
namespace BLW; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

/**
 * Core BLW DOM Element object.
 *
 * <h3>About</h3>
 *
 * <p>This is the core BLW DOMobject. All other Objects must extent this class
 * or implement it's interface.</p>
 *
 * <hr>
 * @package BLW\Core
 * @api BLW
 * @version 1.0.0
 * @since 0.1.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @link http://mast3rpee.tk/projects/BLW/ mAsT3RpEE's Zone > Projects > BLW
 */
class Element extends \BLW\Object implements \BLW\ElementInterface
{
    /**
     * @var array $DefaultOptions Default options used by class if not set in constructor.
     * @api BLW
     * @since 0.1.0
     * @see \BLW\Object::___construct() Object::__construct()
     */
    public static $DefaultOptions = array(
        'HTML'                => '<span></span>'
        ,'DocumentVersion'    => '1.0'
    );
    
    /**
     * @var \DOMDocument $Document Pointer to current element document if created from string.
     * @link http://php.net/manual/en/class.domdocument.php
     */
    private $Document = 0;
    
    /**
     * Overloads parent function
     * @see \BLW\Object::ValidateOptions() Object::ValidateOptions()
     * @param mixed $Options Options to validate
     * @return bool Return <code>true</code> if options are valid <code>false</code> otherwise.
     */
    public static function ValidateOptions($Options)
    {
        return is_array($Options) || $Options instanceof \BLW\ObjectInterface || $Options instanceof \DOMNode || $Options instanceof \DOMNodeList;
    }
    
    /**
     * Builds Options for current object.
     * @see \BLW\Object::__construct() Objecct::__contruct()
     * @param mixed $Options Options to build
     * @return \stdClass Returns built options.
     */
    public static function BuildOptions($Options)
    {
        if($Options instanceof \DOMNode) {
            $return          = (object)(static::$DefaultOptions);
            $return->DOMNode = $Options;
            
            unset($Options->HTML);
            
            return $return;
        }
        
        elseif($Options instanceof \DOMNodeList || is_array($Options)? end($Options) instanceof \DOMNode : false) {
            $return              = (object)(static::$DefaultOptions);
            $return->DOMNodeList = $Options;
            
            unset($Options->HTML);
            
            return $return;
        }
        
        elseif(is_array($Options)) {
            return (object)(array_replace(static::$DefaultOptions, $Options));
        }
        
        return new \stdClass;
    }
    
    /**
     * Hook that is called when a new instance is created.
     * @note Format is <code>mixed function (\BLW\ObjectInterface $o)</code>.
     * @param \Closure $Function Function to call after object has been created.
     * @return \BLW\Object Current object
     */
    public function onCreate(\Closure $Funtion = NULL)
    {
        static $OnCreate = NULL;
        
        if(is_null($Funtion)) {
            
            if(isset($this->Options->HTML)) {
                $this->LoadHTML($this->Options->HTML);
                unset($this->Options->HTML);
            }
            
            elseif(isset($this->Options->DOMNode)) {
                $this->AddNode($this->Options->DOMNode);
                unset($this->Options->DOMNode);
            }
            
            elseif(isset($this->Options->DOMNodeList)) {
                foreach ($this->Options->DOMNodeList as $Node) {
                    $this->AddNode($Node);
                }
                unset($this->Options->DOMNode);
            }
            
            if(is_callable($OnCreate)) {
                $OnCreate($this);
            }
        }
        
        elseif(is_callable($Function)) {
            $OnCreate = $Funtion;
        }         
        
        else {
            $this->Status &= static::INVALID_CALLBACK;
            
            throw new \BLW\InvalidClassException();
        }
        
        return $this;
    }
    
    /**
     * Returns the current elements document or creates one if it doesnt exist.
     * @api BLW
     * @since 1.0.0
     * @return \DOMDocument Current Object's <code>DOMDocument</code>.
     */
    public function & Document()
    {
        if(!$this->Document instanceof \DOMDocument) {
            if(!isset($this->Options->DocumentVersion)) {
                throw new InvalidClassException('%header% Options->DocumentVersion does not exits.');
                return NULL;
            }
            
            $this->Document = new \DOMDocument($this->Options->DocumentVersion, 'UTF-8');
        }
        
        return $this->Document;
    }
    
    /**
     * Converts HTML string into DOMNodes and ataches them to the object.
     * @api BLW
     * @since 1.0.0
     * @param string $HTML HTML string to load.
     * @return \BLW\Element $this
     */
    public function LoadHTML($HTML)
    {
        // Validate HTML
        if(!is_string($HTML) || empty($HTML)) {
            throw new \BLW\InvalidArgumentException(0);
            return $this;
        }
        
        // Empty current Element
        for($i=0,$k=true;$i<count($this);$i++,$k=true) {
            
            foreach ($this as $i => $o) {
                if ($o instanceof \DOMNode || $o instanceof \BLW\ElementInterface) {
                	unset($this[$i]);
                	$k = false;
                	break;
                }
            }
            
            if($k) break;
        }
        
        $this->Document = NULL;
        
        // Disable errors
        $current         = libxml_use_internal_errors(true);
        $disableEntities = libxml_disable_entity_loader(true);
        
        // Convert HTML
        if (function_exists('mb_convert_encoding') && in_array('UTF-8', mb_list_encodings())) {
            $HTML = mb_convert_encoding($HTML, 'HTML-ENTITIES', 'UTF-8');
        }
        
        // Load HTML
        $this->Document()->loadHTML($HTML);
        
        // Enable errors
        libxml_use_internal_errors($current);
        libxml_disable_entity_loader($disableEntities);
        
        // Add DOMElements
        $isDocument = preg_match('/<html/i', $HTML) > 0;
        
        $this->AddDocument($this->Document, $isDocument);
    }
    
    /**
     * Loads Nodes from a DOMDocument.
     * @api BLW
     * @since 1.0.0
     * @param \DOMDocument $Document Document to Add to current Object.
     * @param string $isDocument Wheather to load the Entire document or just its body.
     * @return \BLW\Element $this
     */
    public function AddDocument(\DOMDocument $Document, $isDocument = false)
    {
        if(!$isDocument) {
            foreach($Document->documentElement->lastChild->childNodes as $Node) {
                $this->AddNode($Node);
            }
        }
        
        else {
            $this->AddNode($Document->documentElement);
        }
        
        return $this;
    }
    
    /**
     * Adds a DOMNode to the current object.
     * @api BLW
     * @since 1.0.0
     * @param \DOMNode $Node Node to Add to Object.
     * @return \BLW\Element $this
     */
    final public function AddNode(\DOMNode $Node)
    {
        if (!$Node instanceof \DOMDocument) {
            $this->push($Node);
        }
        
        else {
            $this->AddDocument($Node);
        }
        
        return $this;
    }
}

return ;