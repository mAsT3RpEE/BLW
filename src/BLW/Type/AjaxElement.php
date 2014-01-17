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
namespace BLW\Type; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

/**
 * Core Ajax enabled Element class.
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
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 */
abstract class AjaxElement extends \BLW\Type\Element
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
     * @var array $DefaultOptions Default options used by class if not set in constructor.
     * @see \BLW\Type\Object::__construct() Object::__construct()
     */
    public static $DefaultOptions = array(
        'HTML'              => '<span class="ajax"></span>'
        ,'DocumentVersion'  => '1.0'
        ,'AJAX'             => array()
        ,'Type'             => self::TYPE_COOKIE
    );

    /**
     * @var string[] $Scripts Array of javascripts relative to asset directory to load.
     */
    public static $Scripts = array();

    /**
     * @var string[] $Notices Stores all ajax notices.
     */
    public static $Notices = array();

    /**
     * @var string[] $Errors Stores all ajax errors.
     */
    public static $Errors = array();

    /**
     * Initializes a child class for subsequent use.
     * @param array $Options Initialization options. (Automatically adds blw_cfg())
     * @return array Returns Options used / generated during init.
     */
    public static function Initialize(array $Data = array())
    {
        parent::Initialize($Data);

        // Initialize self
        if(!self::$_Initialized || isset($Data['hard_init'])) {
            self::SecurityID();
            self::InitializeScripts();
        }

        // Initialize children
        elseif(($class = get_called_class()) != __CLASS__) {
            self::InitializeChild($class, $Data);
        }

        return static::$DefaultOptions;
    }

	/**
	 *	Creates a security ID to track ajax requests per user.
	 *	@api BLW
	 *	@since 1.0.0
	 *	@see \BLW\AjaxElement::ValidateSecurityID()
	 *	@return string Generated security ID
	 */
	public static function SecurityID()
	{
		/* Set uniqueid */
		if(!isset($_SESSION['blwid'])) {
			$_SESSION['blwid'] = md5(session_id() . (isset($_SERVER['HTTP_USER_AGENT'])
				? $_SERVER['HTTP_USER_AGENT']
				: ''
			));
		}

		return $_SESSION['blwid'];
	}

    /**
	 *	Validates security ID.
	 *	@api BLW
	 *	@since 1.0.0
	 *	@see \BLW\AjaxElement::SecurityID()
	 *	@return bool Returns true if security ID matches
	 */
	public static function ValidateSecurityID()
	{
		/* Validate secret */
		if(defined('STDIN')) {
			return true;
		}

		elseif(isset($_REQUEST['secret'])? $_REQUEST['secret'] === static::SecurityID(): false) {
			return true;
		}

		return false;
	}

	/**
     * @ignore
     */
    private static function InitializeScripts()
    {
        // Scripts Container
        if(isset(\BLW::$Base->Scripts)? !is_object(\BLW::$Base->Scripts) : true) {

            \BLW::$Base->push(\BLW\Model\Element::GetInstance(array(
                'ID'    => 'Scripts'
                ,'HTML' => NULL
            )));

           \BLW::$Base->Scripts = \BLW::$Base->top();
        }

        // Empty scripts
        while (!\BLW::$Base->Scripts->isEmpty()) {
            \BLW::$Base->Scripts->offsetUnset(0);
        }

        // Rebuild scripts
        foreach(static::$Scripts as $Value) {
            \BLW::$Base->Scripts->push(\BLW\Model\Element::GetInstance(array(
                'HTML' => sprintf('<script type="text/javascript" src="%s"></script>', $Value)
            )));
        }
    }

    /**
     * @ignore
     */
    private static function InitializeChild($class, $Data = array())
    {
        // Ajax Actions;
        \BLW::AddAction($class . '.Created');

        // Auto load scripts
        $Script = sprintf('%s/%s.js', BLW_ASSETS_URL, str_replace('\\', '.', $class));

        if(file_exists($Script) ) {
            self::$Scripts[$class] = $Script;
            self::InitializeScripts();
        }
    }

    /**
     * Hook that is called when a new instance is created.
     * @return \BLW\Interfaces\Object $this
     */
    public static function doCreate()
    {
        $self = parent::doCreate();

        // Set Element ID
        if(!$self->isEmpty()) {
            if(($Node = $self->bottom()) instanceof \DOMElement) {
                $Node->setAttribute('id', $self->GetID());
            }
        }

        // Check if headers have been sent
        if (headers_sent($File, $Line) && !defined('STDIN')) {
            trigger_error('Headers already sent in '.$File.' on line '.$Line.'. Disabling ajax.', E_USER_WARNING);
            return $self;
        }

        // Register ajax actions
        foreach ($self->Options->AJAX as $Action => $Function) {

            if (is_callable($Function)) {
                $self->_on('Action.' . $Action, $Function);
            }

            elseif (is_callable(array($self, $Function))) {
                $self->Options->AJAX[$Action] = array($self, $Function);
                $self->_on('Action.' . $Action, array($self, 'doAjax'));
            }

            else {
                throw new \BLW\Model\InvalidArgumentException(0, sprintf('%header% Invalid ajax action ( %s ) with callback ( %s )', $Action, print_r($Function, true)));
                return $self;
            }
        }

        // Trigger Ajaxelement.Created event
        static::GetMediator()->Trigger(get_class($self) . '.Created', new \BLW\Model\Event\General($self));

        return $self;
    }

    /**
     * Changes the ID of the current object.
     * @param string $ID New ID to give Object
     * @return \BLW\Object $this
     */
    public function doSetID()
    {
        parent::doSetID();

        // Update element ID
        if(!\SplDoublyLinkedList::isEmpty()) {
            if(($Node = \SplDoublyLinkedList::bottom()) instanceof \DOMElement) {
                $Node->setAttribute('id', $this->GetID());
            }
        }

        return $this;
    }

    /**
     * Hook that is called just before an object is serialized.
     * @return \BLW\Interface\Object $this
     */
    public function doSerialize()
    {
        parent::doSerialize();

        foreach ($this->Options->AJAX as $k => $Function) {
            // Remove Closure's
            if ($Function instanceof \Closure) {
                unset($this->Options->AJAX[$k]);
            }
            // Remove self references.
            else if (is_array($Function)) {

                if ($Function[0] instanceof $this) {
                    $this->Options->AJAX[$k] = $Function[1];
                }

                else {
                    unset($this->Options->AJAX[$k]);
                }
            }
        }

        return $this;
    }

    /**
     * Hook that is called just after an object is unserialized.
     * @return \BLW\Interface\Object $this
     */
    public function doUnSerialize()
    {
        parent::doUnSerialize();

        foreach ($this->Options->AJAX as $k => $Function) {
            // Restore self references.
            if (is_string($Function)) {
                $this->Options->AJAX[$k] = array($this, $Function);
            }
        }

        return $this;
    }

    /**
     * Perform an ajax request then exit.
     * @api BLW
     * @since 1.0.0
     * @param \BLW\Interface\Event $Event Event associated with ajax request.
     * @return string;
     */
    final public function doAJAX(\BLW\Interfaces\Event $Event)
    {
        $Subject = $Event->GetSubject();
        $Action  = substr($Subject->Name, 7);

        if(!defined('STDOUT')) {
            header("Content-type: application/json");
            header("Expires: " . gmdate("D, d M Y H:i:s", time()+1) . " GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s",  time()+1) . " GMT");
        }

        // Validate secret
        if(!static::ValidateSecurityID()) {
            $Response = '{"status":0,"result":{}}';

            if(!defined('STDOUT')) {
                die($Response);
            }

            else {
                return $Response;
            }
        }

        // Default response
        $Response = array(
             'status'  => \BLW\Interfaces\Object::INVALID_CALLBACK
            ,'result'  => array()
            ,'notices' => &self::$Notices
            ,'errors'  => &self::$Errors
        );

        // Make sure we give some kind of response
        if(!defined('STDOUT')) {
            register_shutdown_function(function() use ($Response) {
                echo json_encode($Response);
            });
        }

        // Call function
        if(is_callable($this->Options->AJAX[$Action])) {
            $Response['status'] = self::PROCESSING;
            $Response['result'] = call_user_func($this->Options->AJAX[$Action], $Action, $this);
            $Response['status'] = self::FINISHED;
        }

        else {
            throw new \BLW\Model\InvalidClassException($this->_Status);
        }

        if(!defined('STDOUT')) exit;

        return json_encode($Response);
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

		// Add script
		if(isset($Script)) {
			$HTML .= sprintf("<script type=\"text/javascript\">//<![CDATA[\n%s;//]]>\n</script>", $Script);
		}

		// Encapsulate everything in main container
        return preg_replace('|(.*)(</'.$this->tag().'>)(.*)|is', '$1$3$2', $HTML, 1);
    }

    /**
     * Get inline JavaScript used by object.
     *
     * <h4>Note:</h4>
     *
     * <p>Should return <code>NULL</code> if no inline JavaScript is set.</p>
     *
     * <hr>
     * @api BLW
     * @since 1.0.0
     * @return string
     */
    abstract public function InlineJS();

    /**
     * Sets an ajax action to $Function.
     * @api BLW
     * @since 1.0.0
     * @throws \BLW\Model\InvalidArgumentException If:
     * <ul>
     * <li><code>$Action</code> is not a string.</li>
     * <li><code>$Function</code> is not callable.</li>
     * </ul>
     * @param string $Action Action to set
     * @param callable $Function Function to handle ajax action
     * @return callable Previous action or <code>false</code> on error.
     */
    public function SetAction($Action, $Function)
    {
        // Validate
        if($Action != static::SanitizeLabel($Action)) {
            throw new \BLW\Model\InvalidArgumentException(0);
            return false;
        }

        if(!is_callable($Function)) {
            throw new \BLW\Model\InvalidArgumentException(1);
            return false;
        }

        $Old = isset($this->Options->AJAX[$Action])
            ? $this->Options->AJAX[$Action]
            : NULL
        ;

        $this->Options->AJAX[$Action] = $Function;

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

return true;