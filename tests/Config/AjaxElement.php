<?php
class AjaxElement extends \BLW\Type\AjaxElement
{
    /**
     * @var array $DefaultOptions Default options used by class if not set in constructor.
     * @see \BLW\Type\Object::__construct() Object::__construct()
     */
    public static $DefaultOptions = array(
         'HTML'             => '<span class="ajax"></span>'
        ,'DocumentVersion'  => '1.0'
        ,'AJAX'             => array(
    	   'SetGlobal'      => 'SetGlobal'
        )
        ,'Type'             => self::TYPE_COOKIE
    );

    /**
     * Get inline JavaScript used by object.
     *
     * <h4>Note:</h4>
     *
     * <p>Should return <code>NULL</code> if no inline JavaScript is set.</p>
     * <hr>
     * @api BLW
     * @since 1.0.0
     * @return string
     */
    public function InlineJS()
    {
        return 'var x = 100';
    }

    public function SetGlobal($Action)
    {
        $GLOBALS['AJAX_ACTION'] = $Action;
    }
}