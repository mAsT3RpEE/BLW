<?php
/**
 * Archiver.php | Dec 11, 2013
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

use Phar;
use PharData;
use FilesystemIterator;

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
 * Archiver for making tar.gz files.
 *
 * @package BLW\Core
 * @api     BLW
 * @version GIT: 0.2.0
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class Archiver extends \BLW\Model\Compiler
{

    /**
     * Creates a PHAR archives in the build path.
     *
     * <h4>Note</h4>
     *
     * <p>Raises an <b>E_USER_WARNING</b> if a file cannot be accessed / read.</p>
     *
     * <hr>
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Project</code> contains: <code> " * / : < > ? \ | NL CR TAB </code>.
     * @throws \RuntimeException
     *
     * @param string $Project
     *            Name of the project.
     * @return boolean <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function compile($Project = 'APP')
    {
        // Is $Project invalid
        $Project = trim(@substr($Project, 0, 64));
        $Regex   = '!^([^\x00-\x1f\x22\x2a\x2f\x3a\x3c\x3e\x3f\x5c\x7c]{1,32})(?:\x2e[Pp][Hh][Aa][Rr])?$!';

        if (! preg_match($Regex, $Project, $m)) {
            // Exception
            throw new InvalidArgumentException(0);
        }

        // Create Archive
        $TAR = sprintf('%s%s%s.tar', $this->_Temp, DIRECTORY_SEPARATOR, $Project);

        @unlink($TAR);
        @unlink("$TAR.gz");

        $TAR = new PharData($TAR, FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME);

        $TAR->startBuffering();

        // Advance Event
        $this->doAdvance(10);

        // Add files to phar
        foreach ($this->_Files as $File) {

            // Is file readable?
            if (is_readable($File)) {

                // Generate path
                $Path = str_replace($this->_Root . '/', '', $File);
                $Path = str_replace($this->_Root . '\\', '', $Path);
                $Path = str_replace(DIRECTORY_SEPARATOR, '/', $Path);

                // Optimize and add
                $TAR->addFromString($Path, $this->optimize($File));

                // Advance Event
                $this->doAdvance();
            }

            // @codeCoverageIgnoreStart

            // $File is not readable
            else {
                // Warning
                trigger_error(sprintf('Unable to read file (%s) with mode (%o)', $File, @fileperms($File)), E_USER_WARNING);
            }

            // @codeCoverageIgnoreEnd
        }

        $TAR->stopBuffering();
        $TAR->compress(Phar::GZ, '.tar.gz');

        unset($TAR);

        // Advance Event
        $this->doAdvance(10);

        // Copy finished tar
        $TAR    = sprintf('%s%s%s.tar', $this->_Temp, DIRECTORY_SEPARATOR, $Project);
        $return = copy("$TAR.gz", sprintf('%s%s%s.gz', $this->_Build, DIRECTORY_SEPARATOR, basename($TAR)));

        // Delete temp files
        usleep(100000);
        unlink($TAR);
        usleep(100000);
        unlink("$TAR.gz");

        // Done
        return $return;
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
