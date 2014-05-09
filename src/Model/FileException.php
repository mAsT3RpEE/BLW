<?php
/**
 * FileException.php | Dec 28, 2013
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
 * @version GIT: 0.2.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model;

use Exception;

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
 * Makes reporting errors just easy.
 *
 * @package BLW\Core
 * @api     BLW
 * @version GIT: 0.2.0
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://www.php.net/manual/en/class.runtimeexception.php RuntimeException
 */
final class FileException extends \BLW\Type\ARuntimeException
{
    /**
     * File with exception
     *
     * @var string
     */
    public $File = '';

    /**
     * Constructor
     *
     * @param string $File
     *            File exception is associated with.
     * @param string $message
	 *            Fomratted exception message.
	 *
     * <ul>
     * <li><b>%header%</b>: <code>class::function(arguments):</code>.</li>
     * <li><b>%file%</b>: File with error.</li>
     * <li><b>%access%</b>: File access code in octal format or invalid if file doesnt exist.</li>
     * <li><b>%args%</b>: All arguments as a string.</li>
     * <li><b>%class%</b>: Class of of function.</li>
     * <li><b>%func%</b>: Function with invalid argument.</li>
     * </ul>
	 *
     * @param integer $code
     *            Exception code.
     * @param \Exception $previous
     *            Previous Exception.
     */
    public function __construct($File, $message = null, $code = 0, Exception $previous = null)
    {
        if (is_null($message)) {
            $message = '%header% File error: (%file%) with access (%access%).';
        }

        $Replacements             = $this->GetFields();
        $Replacements['%file%']   = $this->File = @strval($File);
        $Replacements['%access%'] = (is_file($File) || is_dir($File))
            ? sprintf('%o', @fileperms($File))
            : 'invalid';

        $message  = str_replace(array_keys($Replacements), array_values($Replacements), $message);
        $message .= $Replacements['%caused%'];

        parent::__construct($message, $code, $previous);
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
