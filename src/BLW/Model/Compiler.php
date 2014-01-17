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
namespace BLW\Model; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

/**
 * Builds phar files for blw projects.
 * @package BLW\Core
 * @api BLW
 * @version 1.0.0
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class Compiler extends \BLW\Type\Object
{
    /**
     * @var array $DefaultOptions Default options used by class if not set in constructor.
     * @api BLW
     * @since 0.1.0
     * @see \BLW\Type\Object::__construct() Object::__construct()
     */
    public static $DefaultOptions = array(
        'PHAR'          => 'APP.phar'
        ,'Root'         => NULL
    );

    /**
     * Initializes a child class for subsequent use.
     * @param array $Options Initialization options. (Automatically adds blw_cfg())
     * @return array Returns Options used / generated during init.
     */
    public static function Initialize(array $Data = array())
    {
        parent::Initialize();

        return static::$DefaultOptions;
    }

    /**
     * Hook that is called when a new instance is created.
     * @return \BLW\Model\Object $this
     */
    public static function doCreate()
    {
        $self = parent::doCreate();

        // Build Paths
        if(!is_dir($self->Options->Root)) {
            $self->Options->Root = getcwd();
        }

        $self->Options->AppRoot  = $self->Options->Root . DIRECTORY_SEPARATOR . 'app';
        $self->Options->ExtRoot  = $self->Options->Root . DIRECTORY_SEPARATOR . 'vendor';
        $self->Options->LiblRoot = $self->Options->Root . DIRECTORY_SEPARATOR . 'src';
        $self->Options->OutRoot  = $self->Options->Root . DIRECTORY_SEPARATOR . 'build';

        return $self;
    }

    /**
     * Hook that is called after a compile process has completed.
     * @param int $Steps Steps completed.
     * @return \BLW\Interfaces\Object $this
     */
    public function doAdvance($Steps = 1)
    {
        $this->_do('Advance', new \BLW\Model\Event\General($this, array('Steps' => $Steps)));

        return $this;
    }

    /**
     * Hook that is called after a compile process has completed.
     * @note Format is <code>mixed function (\BLW\Interfaces\Event $Event)</code>.
     * @param callable $Function Function to call before object is serialized.
     * @return \BLW\Interfaces\Object $this
     */
    public function onAdvance($Function)
    {
        if(is_callable($Function)) {
            $this->_on('Advance', $Function);
        }

        else {
            $this->_Status &= static::INVALID_CALLBACK;
            throw new \BLW\Model\InvalidClassException($this->_Status);
        }

        return $this;
    }

    /**
     * Updates the name of phar file to output
     * @param string $File
     * @return \BLW\Model\Object $this
     */
    public function phar($File = NULL)
    {
        if(is_null($File)) {
            return $this->Options->PHAR;
        }

        elseif(preg_match('/.phar$/', @strval($File)))  {
            $this->Options->PHAR = $File;
        }

        else {
            throw \BLW\Model\InvalidArgumentException(0);
        }

        return $this;
    }

    /**
     * Updates the build path.
     * @param string $Dir
     * @return \BLW\Model\Object $this
     */
    public function out($Dir = NULL)
    {
        if(is_null($Dir)) {
            return $this->Options->Root;
        }

        elseif(!is_file(@strval($Dir))) {
            $this->Options->OutRoot = $Dir;
        }

        else {
            throw new \BLW\Model\InvalidArgumentException(0);
        }

        return $this;
    }

    /**
     * Optimizes a file and returns the optimized version of the file.
     * @param string $File File to optimize
     * @throws \BLW\Model\InvalidArgumentException If file is not recognized as a file.
     * @return string Optimized version of the file
     */
    public function Optimize($File)
    {
        if(!is_file($File)) {
            throw new \BLW\Model\FileException($File);
            return '';
        }

        switch (true)
        {
        	case preg_match('/.php$/i', $File):    return php_strip_whitespace($File);
        	case preg_match('/.css$/i', $File):    return \CSSmin::minify(file_get_contents($File));
        	case preg_match('/.js$/i' , $File):    return \JSMin::minify(file_get_contents($File));
//        	case preg_match('/.jpg$/i', $File):    return JPegCompressor::GetInstance()->SetFile($File)->Compress();
        	default:                               return file_get_contents($File);
        }
    }

    /**
     * Creates a PHAR archives in the build path.
     * @return \BLW\Model\Object $this
     */
    public function run()
    {
        $File   = $this->Options->OutRoot . DIRECTORY_SEPARATOR . $this->Options->PHAR;
        $Files  = array_merge($this->GetLibFiles(), $this->GetAppFiles());

        // Validation
        if (!is_file($this->Options->Root . DIRECTORY_SEPARATOR . 'LICENSE.txt')) {
            throw new \RuntimeException('LICENSE.txt Doesnt exist');
            return $this;
        }

        elseif (!is_file($this->Options->AppRoot . DIRECTORY_SEPARATOR . 'BLW.ini')) {
            throw new \RuntimeException('BLW.ini Doesnt exist');
            return $this;
        }

        elseif (!is_file($this->Options->AppRoot . DIRECTORY_SEPARATOR . 'readme.php')) {
            throw new \RuntimeException('readme.php Doesnt exist');
            return $this;
        }

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
            $Path = str_replace($this->Options->Root . DIRECTORY_SEPARATOR, '', $File);
            $Path = str_replace('\\', '/', $Path);

            $PHAR->addFromString($Path, $this->Optimize($File));

            $this->doAdvance();
        }

        // Stub
        $PHAR['_stub.php'] = file_get_contents($this->Options->AppRoot . DIRECTORY_SEPARATOR . 'readme.php');
        $PHAR->setStub($PHAR->createDefaultStub('_stub.php'));

        $PHAR->stopBuffering();
        // $PHAR->compressFiles(\Phar::GZ);

        unset($PHAR);

        $this->doAdvance(10);

        // Copy app files
        foreach ($this->GetApplications() as $File) {
            $New = str_replace(
                 array($this->Options->AppRoot, 'APP.')
                ,array($this->Options->OutRoot, '')
                ,$File
            );

            copy ($File, $New);

            $this->doAdvance();
        }

        // Copy assets
        $Assets = $this->Options->OutRoot . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR;

        @mkdir($Assets);

        foreach ($this->GetAssets() as $File) {

            $New = str_replace($this->Options->Root . DIRECTORY_SEPARATOR, '', $File);
            $New = str_replace(DIRECTORY_SEPARATOR, '.', $New);

            file_put_contents($Assets . $New, $this->Optimize($File));

            $this->doAdvance();
        }

        // Copy Config and Licence
        copy($this->Options->Root . DIRECTORY_SEPARATOR .'LICENSE.txt', $this->Options->OutRoot . DIRECTORY_SEPARATOR . 'LICENCE.txt');
        copy($this->Options->AppRoot . DIRECTORY_SEPARATOR . 'BLW.ini',  $this->Options->OutRoot . DIRECTORY_SEPARATOR . 'BLW.ini');

        $this->doAdvance(10);

        // Create Archive
        $TAR  = str_replace('.phar', '.tar', $this->Options->PHAR);

        @unlink($this->Options->OutRoot . DIRECTORY_SEPARATOR . $TAR);
        @unlink($this->Options->OutRoot . DIRECTORY_SEPARATOR . $TAR . '.gz');

        $PHAR = new \PharData(
           $this->Options->OutRoot . DIRECTORY_SEPARATOR . $TAR,
            \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::KEY_AS_FILENAME,
            $TAR
        );

        $PHAR->startBuffering();

        $Iterator = new \RecursiveIteratorIterator (new \RecursiveDirectoryIterator ($this->Options->OutRoot), \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($Iterator as $File) {

            if (preg_match ('#([.]log$)#i', $File)) continue;

            if(is_file($File)) {
                $Path = str_replace($this->Options->OutRoot . DIRECTORY_SEPARATOR, '', $File);
                $PHAR->addFile($File, $Path);
            }

            $this->doAdvance();
        }

        $PHAR->stopBuffering();
        $PHAR->compress(\Phar::GZ);

        unset($PHAR);

        @unlink($this->Options->OutRoot . DIRECTORY_SEPARATOR . $TAR);

        return $this;
    }

    /**
     * Gets files from src directory
     * @return string[] Returns all relevant files found.
     */
    protected function GetLibFiles()
    {
        $Files = array();

        if (is_file($this->Options->Root . DIRECTORY_SEPARATOR . 'LICENSE.txt')) {
            $Files[] = new \SplFileInfo($this->Options->Root . DIRECTORY_SEPARATOR . 'LICENSE.txt');
        }

        if (is_file($this->Options->ExtRoot . DIRECTORY_SEPARATOR . 'guzzle/http/Guzzle/Http/Resources/cacert.pem')) {
            $Files[] = new \SplFileInfo($this->Options->ExtRoot . DIRECTORY_SEPARATOR . 'guzzle/http/Guzzle/Http/Resources/cacert.pem');
            $Files[] = new \SplFileInfo($this->Options->ExtRoot . DIRECTORY_SEPARATOR . 'guzzle/http/Guzzle/Http/Resources/cacert.pem.md5');
        }

        $Dirs = array(
            $this->Options->ExtRoot
            ,$this->Options->LiblRoot
        );

        foreach ($Dirs as $Dir) {

            $Iterator = new \RecursiveIteratorIterator (new \RecursiveDirectoryIterator ($Dir), \RecursiveIteratorIterator::SELF_FIRST);

            foreach ($Iterator as $File) {

                if (preg_match('#/.*(?:test|example|extra)[s]?/#i', $File)) continue;

                if (preg_match ('#([.]php$|[.]htm$|[.]html$)#i', $File)) {
                    $Files[] = $File;
                }
            }
        }

        return $Files;
    }

    /**
     * Gets files from app directory
     * @return string[] Returns all relevant files found.
     */
    protected function GetAppFiles()
    {
        $Files      = array();
        $Iterator   = new \RecursiveIteratorIterator (new \RecursiveDirectoryIterator ($this->Options->AppRoot), \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($Iterator as $File) {
            if (preg_match ('#[\\\\/](?:APP|OBJ|EL)[.].*[.]php$#i', $File)) continue;

            if (preg_match ('#([.]php$|[.]htm$|[.]html$)#i', $File)) {
                    $Files[] = $File;
            }
        }

        return $Files;
    }

    /**
     * Gets files from app directory starting with (APP, OBJ, FORM, EL) and .PHAR files.
     * @return string[] Returns all relevant files found.
     */
    protected function GetApplications()
    {
        $Files      = array();
        $Iterator   = new \RecursiveIteratorIterator (new \RecursiveDirectoryIterator ($this->Options->AppRoot), \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($Iterator as $File) {
            if (preg_match ('#[\\\\/](?:APP|OBJ|EL)[.].*[.]php$#i', $File)) {
                $Files[] = $File;
            }

            elseif (preg_match ('#([.]phar$)#i', $File)) {
                $Files[] = $File;
            }
        }

        return $Files;
    }

    /**
     * Gets stylesheets, images and javascripts from src and app directory
     * @return string[] Returns all relevant files found.
     */
    protected function GetAssets()
    {
        $Files      = array();
        $Iterator   = new \RecursiveIteratorIterator (new \RecursiveDirectoryIterator ($this->Options->AppRoot), \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($Iterator as $File) {
            if (preg_match ('#([.]js$|[.]css$|[.]jpg$|[.]png$)#i', $File)) {
                $Files[] = $File;
            }
        }

        $Iterator   = new \RecursiveIteratorIterator (new \RecursiveDirectoryIterator ($this->Options->LiblRoot), \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($Iterator as $File) {
            if (preg_match ('#([.]js$|[.]css$|[.]jpg$|[.]png$)#i', $File)) {
                $Files[] = $File;
            }
        }

        return $Files;
    }
}

return true;