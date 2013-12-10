<?php
/**
 * Common script that configures all BLW scripts.
 * 
 * <h4>Note:</h4>
 * 
 * <p><code>BLW_PLUGIN_URL</code> and <code>BLW_PLUGIN_DIR</code>
 * need to be defined first or it will trigger an error.</p>
 * 
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 * 
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 * @copyright mAsT3RpEE's Zone
 * @license MIT
 */


/* GLOBALS */

/**
 * Wrapper for BLW configuration array.
 *
 * <h4>Note:</h4>
 * 
 * <p>This var is placed in a function to prevent corruption in
 * case of modification of of $GLOBALS array.</p>
 * 
 * @api BLW
 * @since 1.0.0
 * @param bool $reset Reset value of Configuration.
 * @return array Returns a static array that contains all configuration.
 */
function& blw_cfg($reset = false)
{
    static $Configuration = array();
    
    if(!!$reset) $Configuration = array();
    
    return $Configuration;
}

/**
 * Overloards PHP's die() method.
 * @link http://www.php.net/manual/en/function.die.php PHP Reference > die
 * @param string $Title Title of die messege.
 * @param string $Message Body of die messege.
 * @return void
 */
function blw_die($Title, $Messege)
{
    $cfg = blw_cfg();
    $die = isset($cfg['die_function'])
        ? $cfg['die_function']
        : function($Title, $Message) {
            die(sprintf('<b>%s</b>: %s', $Title, $Message));
        }
    ;
    
    call_user_func($die, $Title, $Messege);
}

/**
 * Loads BLW <code>BLW.ini</code> file.
 * @ignore
 * @param string $Name Name of file to load (minus `ini`).
 * @return void
 */
function blw_load_ini_file($Name = 'BLW')
{
    $cfg   = blw_cfg(true);
    $File  = $Name .'.ini';
    
    if(defined('BLW_PLUGIN_DIR')) {
        if (file_exists(BLW_PLUGIN_DIR . '/' . $File)) {
            $cfg = parse_ini_file(BLW_PLUGIN_DIR . '/' . $File);
        }
    }
    
    elseif(file_exists($Name)) {
         $cfg = parse_ini_file($Name);
    }
    
    else {
        // No ini file -_-
        if(!defined('BLW_PLATFORM') || !defined('BLW_PLUGIN_DIR')) {
            $Messege  = "<p>There doesn't seem to be a <code>$Name</code> file. Please Install / Reinstall blw library.</p>";
            
            blw_die('BLW Configuration Error', $Messege);
        }
    }
}

/**
 * Default values if not defined in config files.
 * @ignore
 * @return void
 */
function blw_default_config()
{
    $cfg = blw_cfg();
    
    @define('BLW', '1.0.0') or die('911');
    
    if(!defined('BLW_LIB_PHAR'))        { define('BLW_LIB_PHAR',        strstr(__FILE__, '.phar', true) . 'phar');  }
    if(!defined('BLW_APP_PHAR'))        { define('BLW_APP_PHAR',        BLW_LIB_PHAR);                              }
    if(!defined('BLW_PLUGIN_DIR'))      { define('BLW_PLUGIN_DIR',      dirname(BLW_LIB_PHAR));                     }
    
    if(!defined('BLW_ASSETS'))          { define('BLW_ASSETS',          BLW_APP_PHAR . '/assets');                  }
    if(!defined('BLW_ASSETS_URL'))      { define('BLW_ASSETS_URL',      BLW_PLUGIN_URL . '/assets');                }
    if(!defined('BLW_FRONTEND'))        { define('BLW_FRONTEND',        BLW_APP_PHAR . '/frontend');                }
    if(!defined('BLW_FRONTEND_URL'))    { define('BLW_FRONTEND_URL',    BLW_PLUGIN_URL . '/frontend');              }
    if(!defined('BLW_BACKEND'))         { define('BLW_BACKEND',         BLW_APP_PHAR . '/backend');                 }
    if(!defined('BLW_BACKEND_URL'))     { define('BLW_BACKEND_URL',     BLW_PLUGIN_URL . '/backend');               }
    
    if(!defined('BLW_PLATFORM'))        { define('BLW_PLATFORM',        $cfg['PLATFORM']);                          }
    
    if(BLW_PLATFORM != 'standalone') {
        if(!defined('BLW_EXTENTION'))   { define('BLW_EXTENTION',       '\\Ext_' . BLW_PLATFORM);                   }
    }
    
    else {
        if(!defined('BLW_EXTENTION'))   { define('BLW_EXTENTION',       '');                                        }
    }
}

/**
 * Loads all blw configuration and config files.
 * @api BLW
 * @since 0.1.0
 * @return void
 */
function blw_config()
{
    $cfg = blw_cfg();
    
    // 1. Error Reporting
    error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );
    
    // 2. Config files
    if(!isset($cfg['blw_configured_flag'])) {
        $cfg['blw_configured_flag'] = true;
        
        blw_load_ini_file();
        blw_default_config();
    }
    
    // 3. Validation
    if(!defined('BLW_PLATFORM') || !defined('BLW_PLUGIN_URL')) {
        trigger_error('`BLW_PLATFORM` or `BLW_PLUGIN_URL` are undefined. Config files may be corrupted.', E_USER_WARNING);
        exit;
    }
    
    // 4. Libraries and autoloader
    include BLW_LIB_PHAR . '/vendor/autoload.php';
}


/* SHORTCUTS */


/**
 * Creates a BLW Object.
 * @api BLW
 * @since 0.1.0 
 * @param string $n Name of object to create.
 * @param string[] $o Options passed to object.
 * @param bool $e Load extention.
 * @return \BLW\Object Returns the newly created object.
 */
function blw_o($n,array $o=array(),$e=false)
{
    $n = '\\BLW\\' . $n . $e? BLW_EXTENTION : '';
    
    return new $n($o);
}


/* BACKWARD COMPATABILITY */


;;;;


/* INITIALIZATION */


blw_config();

\BLW\Object::init();
\BLW\ELement::init();
\BLW\Settings::init();


/*-------------------------------------------------------------------- * /


return blw_o('Form', true)
->action('#')
->method(\BLW\Form::POST)
->addChild(blw_o('Form\\Page', true)
    ->title('Step 1')
    ->addChild(blw_o('Form\\Group', true)
        ->title('Login Form')
        ->AddChild(blw_o('Form\\Field\\Name')
            ->autocomplete(false)
            ->required(true)
            ->data('source', 'GoogleSearch')
        )
        ->AddChild(blw_o('Form\\Field\\Password')
            ->required(true)
            ->label('Password')
            ->min(4)
            ->max(38)
        )
        ->addChild(blw_o('Form\\Button\\Submit', true)
            ->label('Login')
            ->parent()
        )
        ->addChild(blw_o('Form\\Button\\Button', true)
            ->label('Cancel')
            ->onClick('CancelForm(this);')
            ->parent()
        )
    )
)
->LoadAll()
->PrintHTML();

/*---------------------------------------------------------------------------------------------*/

return ;