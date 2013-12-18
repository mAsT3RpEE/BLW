<?php
/**
 *	Compiler.php | Dec 11, 2013
 *
 *	Copyright (c) mAsT3RpEE's Zone
 *
 *	This source file is subject to the MIT license that is bundled
 *	with this source code in the file LICENSE.
 *
 *	@filesource
 *	@copyright mAsT3RpEE's Zone
 *	@license MIT
 */

/**
 * @package BLW\Core
* @version 1.0.0
* @author Walter Otsyula <wotsyula@mast3rpee.tk>
*/
namespace BLW; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

/**
 * Builds phar files for blw projects.
 * @package BLW\Core
 * @api BLW
 * @version 1.0.0
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class Compiler extends \BLW\Object implements \BLW\ObjectInterface
{
    /**
     * @var array $DefaultOptions Default options used by class if not set in constructor.
     * @api BLW
     * @since 0.1.0
     * @see \BLW\Object::__construct() Object::__construct()
     */
    public static $DefaultOptions = array(
        'PHAR'          => 'app.BLW.phar'
        ,'Root'         => NULL
    );

    /**
     * Initializes a class for subsequent use.
     * @param array $Options Initialization options.
     * @return array Returns Options used / generated during init.
     */
    public static function init(array $Data = array())
    {
        if(!static::$Initialized || isset($Data['hard_init'])) {

            \BLW\YUICompressor::init();

            // Call Parent init
            $ParentOptions = parent::init();

            // Initialize self
            static::$DefaultOptions = array_replace($ParentOptions, static::$DefaultOptions, $Data);
            static::$Initialized    = true;

            unset(static::$DefaultOptions['hard_init']);
        }

        return static::$DefaultOptions;
    }

    /**
     * Hook that is called when a new instance is created.
     * @note Format is <code>mixed function (\BLW\ObjectInterface $o)</code>.
     * @param \Closure $Function Function to call after object has been created.
     * @return \BLW\Object $this
     */
    public static function onCreate(\Closure $Function = NULL)
    {
        if(is_null($Function)) {

            // Build Paths
            if(!is_dir(Object::$self->Options->Root)) {
                Object::$self->Options->Root = getcwd();
            }

            Object::$self->Options->AppRoot = Object::$self->Options->Root . '/app';
            Object::$self->Options->ExtRoot = Object::$self->Options->Root . '/vendor';
            Object::$self->Options->LibRoot = Object::$self->Options->Root . '/lib';
            Object::$self->Options->IncRoot = Object::$self->Options->Root . '/inc';
            Object::$self->Options->OutRoot = Object::$self->Options->Root . '/build';

            // Call parent
            return parent::onCreate();
        }

        return parent::onCreate($Function);
    }

    /**
     * Updates the name of phar file to output
     * @param string $File
     * @return \BLW\Object $this
     */
    public function phar($File = NULL)
    {
        if(is_null($File)) {
            return $this->Options->PHAR;
        }

        elseif(@preg_match('/.phar$/', $File))  {
            $this->Options->PHAR = $File;
        }

        else {
            throw \BLW\InvalidArgumentException(0);
        }

        return $this;
    }

    /**
     * Updates the build path.
     * @param string $Dir
     * @return \BLW\Object $this
     */
    public function out($Dir = NULL)
    {
        if(is_null($Dir)) {
            return $this->Options->Root;
        }

        elseif(is_string($Dir) && !@is_file($Dir)) {
            $this->Options->OutRoot = $Dir;
        }

        else {
            throw new \BLW\InvalidArgumentException(0);
        }

        return $this;
    }

    /**
     * Optimizes a file and returns the optimized version of the file.
     * @param string $File File to optimize
     * @throws \BLW\InvalidArgumentException If file is not recognized as a file.
     * @return string Optimized version of the file
     */
    public function Optimize($File)
    {
        if(!is_file($File)) {
            throw new \BLW\InvalidArgumentException(0);
            return '';
        }

        switch (true)
        {
        	case preg_match('/.php$/i', $File):    return php_strip_whitespace($File);
        	case preg_match('/.css$/i', $File):    return \BLW\YUICompressor::create(array('Type'=>'css'))->AddFile($File)->Compress();
        	case preg_match('/.js$/i' , $File):    return \BLW\YUICompressor::create(array('Type'=>'js' ))->AddFile($File)->Compress();
//        	case preg_match('/.jpg$/i', $File):    return \BLW\ImageCompressor::create()->SetFile($File)->Compress();
        	default:                               return file_get_contents($File);
        }
    }

    /**
     * Creates a PHAR archives in the build path.
     * @return \BLW\Object $this
     */
    public function run()
    {
        $File   = $this->Options->OutRoot . '/' . $this->Options->PHAR;
        $Files  = $this->Options->PHAR === 'BLW.phar'
            ? array_merge(
                $this->GetLibFiles()
                ,$this->GetAppFiles()
            )
            : $this->GetAppFiles()
        ;

        // Create PHAR
        @mkdir($this->Options->OutRoot);
        @unlink($File);

        $PHAR = new \Phar(
            $File,
            \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::KEY_AS_FILENAME,
            $this->Options->PHAR
        );

        $PHAR->setSignatureAlgorithm(\Phar::SHA1);
        $PHAR->startBuffering();

        // Add files to phar
        foreach ($Files as $File) {
            $Path = str_replace($this->Options->Root . '/', '', $File);
            $Path = str_replace('\\', '/', $Path);
            $PHAR->addFromString($Path, $this->Optimize($File));
        }

        // Stub
        $PHAR['_stub.php'] = file_get_contents($this->Options->AppRoot . '/readme.php');
        $PHAR->setStub($PHAR->createDefaultStub('_stub.php'));

        $PHAR->stopBuffering();
        // $PHAR->compressFiles(\Phar::GZ);

        unset($PHAR);

        // Copy app files
        foreach ($this->GetApplications() as $File) {
            $New = $this->Options->OutRoot . str_replace($this->Options->AppRoot, '', $File);
            copy ($File, $New);
        }

        // Copy assets
        $Assets = $this->Options->OutRoot . '/assets/';

        @mkdir($Assets);

        foreach ($this->GetAssets() as $File) {

            $New = str_replace($this->Options->Root . '/', '', $File);
            $New = str_replace('/', '.', $New);
            $New = str_replace('\\', '.', $New);

            file_put_contents($Assets . $New, $this->Optimize($File));
        }

        // Copy Config and Licence
        copy($this->Options->Root . '/LICENSE.txt', $this->Options->OutRoot . '/LICENCE.txt');
        copy($this->Options->AppRoot . '/BLW.ini',  $this->Options->OutRoot . '/BLW.ini');

        // Create Archive
        $TAR  = str_replace('.phar', '.tar', $this->Options->PHAR);

        @unlink($this->Options->OutRoot . '/' . $TAR);
        @unlink($this->Options->OutRoot . '/' . $TAR . '.gz');

        $PHAR = new \PharData(
           $this->Options->OutRoot . '/' . $TAR,
            \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::KEY_AS_FILENAME,
            $TAR
        );

        $PHAR->startBuffering();

        $Iterator = new \RecursiveIteratorIterator (new \RecursiveDirectoryIterator ($this->Options->OutRoot), \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($Iterator as $File) {
            if(is_file($File)) {
                $Path = str_replace($this->Options->OutRoot . DIRECTORY_SEPARATOR, '', $File);
                $PHAR->addFile($File, $Path);
            }
        }

        $PHAR->stopBuffering();
        $PHAR->compress(\Phar::GZ);

        unset($PHAR);

        @unlink($this->Options->OutRoot . '/' . $TAR);

        return $this;
    }

    protected function GetLibFiles()
    {
        $Files = array(
            $this->Options->Root . '/LICENSE.txt'
            ,$this->Options->ExtRoot . '/autoload.php'
            ,$this->Options->ExtRoot . '/guzzle/http/Guzzle/Http/Resources/cacert.pem'
            ,$this->Options->ExtRoot . '/guzzle/http/Guzzle/Http/Resources/cacert.pem.md5'
        );

        $Dirs = array(
            $this->Options->ExtRoot
            ,$this->Options->LibRoot
            ,$this->Options->IncRoot
        );

        foreach ($Dirs as $Dir) {

            $Iterator = new \RecursiveIteratorIterator (new \RecursiveDirectoryIterator ($Dir), \RecursiveIteratorIterator::SELF_FIRST);

            foreach ($Iterator as $File) {

                if (preg_match('"/test[s]?/"i', $File)) continue;

                if (preg_match ('/(.php$|.htm$|.html$)/i', $File)) {
                    array_push($Files, $File);
                }
            }
        }

        return $Files;
    }

    protected function GetAppFiles()
    {
        $Files = array(
            $this->Options->Root . '/LICENSE.txt'
            ,$this->Options->ExtRoot . '/autoload.php'
        );

        $Iterator = new \RecursiveIteratorIterator (new \RecursiveDirectoryIterator ($this->Options->AppRoot), \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($Iterator as $File) {
            if (preg_match ('/[\\\\\\/]APP[.].*[.]php$/i', $File)) continue;
            if (preg_match ('/([.]php$|[.]htm$|[.]html$)/i', $File)) {
                array_push($Files, $File);
            }
        }

        return $Files;
    }

    protected function GetApplications()
    {
        $Files      = array();
        $Iterator   = new \RecursiveIteratorIterator (new \RecursiveDirectoryIterator ($this->Options->AppRoot), \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($Iterator as $File) {
            if (preg_match ('/[\\\\\\/]APP[.]run[.]php/i', $File)) continue;
            if (preg_match ('/[\\\\\\/]APP[.].*[.]php$/i', $File)) {
                array_push($Files, $File);
            }
        }

        return $Files;
    }

    protected function GetAssets()
    {
        $Files      = array();
        $Iterator   = new \RecursiveIteratorIterator (new \RecursiveDirectoryIterator ($this->Options->AppRoot), \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($Iterator as $File) {
            if (preg_match ('/([.]js$|[.]css$|[.]jpg$|[.]png$)/i', $File)) {
                array_push($Files, $File);
            }
        }

        $Iterator   = new \RecursiveIteratorIterator (new \RecursiveDirectoryIterator ($this->Options->LibRoot), \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($Iterator as $File) {
            if (preg_match ('/([.]js$|[.]css$|[.]jpg$|[.]png$)/i', $File)) {
                array_push($Files, $File);
            }
        }

        return $Files;
    }
}

return ;