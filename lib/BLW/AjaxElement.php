<?php
/**
 * AjaxElement.php | Dec 10, 2013
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

use Symfony\Component\CssSelector\CssSelector;

/**
 * Core BLW Ajax object.
 *
 * <h3>About</h3>
 *
 * <p>I really thought about how to add ajax to objects. I finally resolved
 * it this way:</p>
 *
 * <p>Most CMS have ajax but my system needs to be compatible with all of
 * them while still allowing for ajax in a stand alone fashion.</p>
 *
 * <p>Therefore you must create an ajax element before any headers are sent
 * just like the header() function in php allowing ajax elements to output
 * cookies as well as output ajax replies on same url. Simple.</p>
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
class AjaxElement extends \BLW\Element
{
    /* Loading methods */
    const   TYPE_GET        = 0x01;
    const   TYPE_POST       = 0x02;
    const   TYPE_COOKIE     = 0x04;
    const   TYPE_SESSION    = 0x08;

    /* Ajax Status */
    const   FINISHED        = 0x00;
    const   PROCESSING      = 0x01;

    /**
     * @var string[] $Scripts Array of javascripts relative to asset directory to load.
     */
    protected static $Scripts = array();
    /**
     * @var callback[] $Actions List of all ajax actions available.
     */
    private static $Actions = array();
    /**
     * @var string[] $notices Stores all ajax notices.
     */
    public static $Notices = array();
    /**
     * @var string[] $errors Stores all ajax errors.
     */
    public static $Errors = array();

    /**
     * @var string[] $DefaultOptions Default options used by class if not set in constructor.
     * @see \BLW\Object::__construct() Object::__construct()
     */
    public static    $DefaultOptions = array(
        'HTML'    => '<span class="ajax"></span>'
        ,'AJAX'    => array()
        ,'Type'    => self::TYPE_COOKIE
    );

    /**
     * Initializes a child class for subsequent use.
     * @param array $Options Initialization options. (Automatically adds blw_cfg())
     * @return array Returns Options used / generated during init.
     */
    public static function initChild(array $Data = array())
    {
        $class = get_called_class();

        // Initialize self
        if($class == __CLASS__) {

            if(!self::$Initialized || isset($Data['hard_init'])) {

                $StaticOptions        = parent::init();
                self::$DefaultOptions = array_replace(parent::$DefaultOptions, $StaticOptions, $Data);
                self::$Initialized    = true;

                unset(self::$DefaultOptions['hard_init']);

                // Scripts Container
                if(!is_object(Object::$base->Scripts)) {

                    \BLW\Object::$base->push(Element::create(array(
                        'ID'    => 'Scripts',
                        'HTML'  => NULL
                    )));

                    \BLW\Object::$base->Scripts = \BLW\Object::$base->top();
                }
            }

            // Return Options
            return self::$DefaultOptions;
        }

        else {
            // Initialize children
            if(!static::$Initialized || isset($Data['hard_init'])) {
                static::$DefaultOptions = array_replace(self::$DefaultOptions, static::$DefaultOptions, $Data);
                static::$Initialized    = true;

                unset(static::$DefaultOptions['hard_init']);

                // Auto load scripts
                $script    = sprintf('%s/%s.js', BLW_ASSETS_URL, str_replace('\\', '.', $class));

                if(file_exists($Script) ) {
                    self::$Scripts[$class] =$Script;

                    // Empty scripts
                    while (!\BLW\Object::$base->Scripts->isEmpty()) {
                        \BLW\Object::$base->Scripts->offsetUnset(0);
                    }

                    // Rebuild scripts
                    foreach(static::$Scripts as $Value) {
                        \BLW\Object::$base->Scripts->push(Element::create(array(
                            'HTML' => sprintf('<script type="text/javascript" src="%s"></script>', $Value)
                        )));
                    }
                }
            }
        }

        return static::$DefaultOptions;
    }

    /**
     * Hook that is called when a new instance is created.
     * @note Format is <code>mixed function (\BLW\ObjectInterface $o)</code>.
     * @param \Closure $Function Function to call after object has been created.
     * @return \BLW\Object Current object
     */
    public static function onCreate(\Closure $Function = NULL)
    {
        if(is_null($Function)) {

            // Validate Actions
            foreach(Object::$self->Options->AJAX as $Action => $Func) if(!is_callable($Func)) {

                if(!is_callable(array(Object::$self, $Func))) {
                    throw new InvalidArgumentException(0, sprintf('%header% Invalid ajax action `%s` with callback %s', $Action, print_r($Func)));
                    return parent::onCreate();
                }

                else {
                    Object::$self->Options->AJAX[$Action] = array($this, $Func);
                }
            }

            // Do ajax
            if (!headers_sent($File, $Line) || defined('STDIN')) {
                // $_REQUEST[a] = action
                // $_REQUEST[o] = id
                if(isset($_REQUEST['a']) && isset($_REQUEST['o'])) {
                    if($_REQUEST['o'] === $this->GetID() && is_string($_REQUEST['a'])) {
                        Object::$self->doAJAX($_REQUEST['a']);
                    }
                }
            }

            else {
                trigger_error('Headers already sent in '.$File.' on line '.$Line.'. Disabling ajax.', E_USER_WARNING);
            }

            return parent::onCreate();
        }

        return parent::onCreate($Function);
    }

    /**
     * Changes the ID of the current object.
     * @param string $ID New ID to give Object
     * @return \BLW\Object $this
     */
    public function onSetID(\Closure $Function = NULL)
    {
        if(is_null($Function)) {

            if(($Node = \SplDoublyLinkedList::offsetGet(0)) instanceof \DOMNode) {
                $Node->setAttribute('id', $this->GetID());
            }

            return parent::onSetID();
        }

        return parent::onSetID($Function);
    }

    /**
     * Hook that is called just before an object is serialized.
     * @note Format is <code>mixed function (\BLW\ObjectInterface $o)</code>.
     * @param \Closure $Function Function to call before object is serialized.
     * @return \BLW\AjaxElement $this
     */
    public function onSerialize(\Closure $Function = NULL)
    {
        if(is_null($Function)) {

            // Remove Closure's
            foreach ($this->Options->AJAX as $k => $Action) if ($Action instanceof \Closure) {
                unset($this->Options->AJAX[$k]);
            }

            return parent::onSerialize();
        }

        return parent::onSerialize($Function);
    }

    /**
     * Get inline Javascript used by object.
     * @api BLW
     * @since 1.0.0
     * @return string
     */
    public function InlineJS()
    {
        return '';
    }

    /**
     * Overloads GetHTML.
     * @see \BLW\Element::GetHTML()
     * @return string HTML output of Form
     */
    public function GetHTML()
    {
        $HTML   = parent::GetHTML();
		$Script	= $this->InlineJS();

		if(!empty($Script)) {
			$HTML .= sprintf("<script type=\"text/javascript\">//<![CDATA[\n%s\n//]]></script>", $Script);
		}

        return preg_replace('"(.*)(</'.$this->tag().'>)(.*)"is', '$1$3$2', $HTML, 1);
    }

	/**
	 *	Creates a security ID to track ajax requests per user.
	 *	@api BLW
	 *	@since 1.0.0
	 *	@see \BLW\AjaxElement::ValidateSecurityID()
	 *	@return string Generated security ID
	 */
	public function SecurityID()
	{
		/* Set uniqueid */
		if(!isset($_SESSION['blwid'])) {
			$_SESSION['blwid'] = md5(session_id() . isset($_SERVER['HTTP_USER_AGENT'])
				? $_SERVER['HTTP_USER_AGENT']
				: ''
			);
		}

		return $_SESSION['blwid'];
	}

	/**
	 *	Validates security ID.
	 *	@api BLW
	 *	@since 1.0.0
	 *  @note On fail deletes session. Hope this does not ruin your coding -_-.
	 *	@see \BLW\AjaxElement::SecurityID()
	 *	@return bool Returns true if security ID matches
	 */
	public function ValidateSecurityID()
	{
		/* Validate secret */
		if(defined('STDIN')) {
			return true;
		}

		elseif(isset($_REQUEST['secret'])? $_REQUEST['secret'] === self::SecurityID(): false) {
			return true;
		}

		@session_regenerate_id(true);

		return false;
	}

    /**
     * Perform an ajax request then exit.
     * @api BLW
     * @since 1.0.0
     * @param string $Action Ajax action to perform.
     * @return string;
     */
    public function doAJAX($Action)
    {
        if(!defined('STDOUT')) {
            header("Content-type: application/json");
            header("Expires: " . gmdate("D, d M Y H:i:s", time()+1) . " GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s",  time()+1) . " GMT");
        }

        /* Validate secret */
        if(!$this->ValidateSecurityID() || $Action != static::SanitizeLabel($Action)) {
            $Response = '{"status":0,"result":{}}';

            if(!defined('STDOUT')) {
                die($Response);
            }

            else {
                return $Response;
            }
        }

        /* Default response */
        $Response = array(
            'status' => Object::INVALID_CALLBACK,
            'result' => array()
        );

        /* Make sure we give some kind of response */
        if(!defined('STDOUT')) {
            register_shutdown_function(function() use ($Response) {
                echo json_encode($Response);
            });
        }

        /* Validate function */
        if(is_callable($this->Options->AJAX[$Action]))
        {
            /* Process request */
            $Response['status'] = self::PROCESSING;
            $Response           = call_user_func($this->Options->AJAX[$Action], $Action, $this);
        }

        if(!defined('STDOUT')) exit;

        return json_encode($Response);
    }

    /**
     * Sets an ajax action to a specific value.
     * @api BLW
     * @since 1.0.0
     * @param string $Action Action to set
     * @param \Closure $Func Function to handle ajax action
     * @return \Closure|null Previous action.
     */
    public function SetAction($Action, \Closure $Func)
    {
        if($Action != static::SanitizeLabel($Action)) {
            throw new InvalidArgumentException(0);
            return NULL;
        }

        if(!is_callable($Func)) {
            throw new InvalidArgumentException(1);
            return NULL;
        }

        $Old = isset($this->Options->AJAX[$Action])
            ? $this->Options->AJAX[$Action]
            : NULL
        ;

        $this->Options->AJAX[$Action] = $Func;

        return $Old;
    }

    /**
     * Loads element from session / <code>$_REQUEST</code>.
     * @api BLW
     * @since 1.0.0
     * @return \BLW\AjaxElement $this
     */
    public function LoadAjax()
    {
        return $this;
    }

    /**
     * Saves element to session / <code>$_REQUEST</code>.
     * @api BLW
     * @since 1.0.0
     * @return \BLW\AjaxElement $this
     */
    public function SaveAjax()
    {
        return $this;
    }
}

return ;