<?php
/**
 * YUICompressor.php | Dec 12, 2013
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
namespace BLW\Model; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

/**
 * Description.
 * @package BLW\Core
 * @api BLW
 * @version 1.0.0
 * @since 0.1.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link https://github.com/gpbmike/PHP-YUI-Compressor Based On
 */
class YUICompressor extends \BLW\Type\Object
{
    /**
     * @var array $DefaultOptions Default options used by class if not set in constructor.
     * @api BLW
     * @since 0.1.0
     * @see BLW\Interfaces\Object::__construct() Object::__construct()
     */
    public static $DefaultOptions = array(
        'JAR'           => NULL
        ,'TMP'          => NULL
        ,'Type'         => 'js'
        ,'LineBreak'    => false
        ,'Verbose'      => false
        ,'Semicolons'   => false
        ,'NoMunge'      => false
        ,'NoOptimize'   => false
    );

    /**
     * @var array $Files Stores all files to add to compressor.
     */
    private $Files  = array();

    /**
     * @var string $String String content to add to compressor.
     */
    private $String = '';

    /**
     * @var bool $JavaEnabled Whether java existed at class creation.
     */
    private static $JavaEnabled = false;

    /**
     * Initializes a class for subsequent use.
     * @param array $Options Initialization options.
     * @return array Returns Options used / generated during init.
     */
    public static function Initialize(array $Data = array())
    {
        // Initialize self
        if(!self::$_Initialized || isset($Data['hard_init'])) {
            // Confirm java
            if(is_callable('exec')) {
                @exec('java -h 2>&1', $Output, $Status);
                self::$JavaEnabled = $Status === 0;
                unset($Output, $Status);
            }
        }

        parent::Initialize($Data);
        return static::$DefaultOptions;
    }

    /**
     * Hook that is called when a new instance is created.
     * @return BLW\Interfaces\Object $this
     */
    public static function doCreate()
    {
        $self = parent::doCreate();

        // Build Paths
        if(!@is_dir($self->Options->TMP)) {
            $self->Options->TMP = sys_get_temp_dir();
        }

        if(!@is_file($self->Options->JAR)) {
            $self->Options->JAR = getcwd() . '/vendor/packagist/yuicompressor-bin/bin/yuicompressor.jar';
        }

        if(!@is_file($self->Options->JAR)) {
            $self->Options->JAR = BLW_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'yuicompressor.jar';
        }

        return $self;
    }

    /**
     * Adds a file to YUI compressor.
     * @param string $File File to add to compressor.
     * @throws \BLW\Model\InvalidArgumentException If file is not recognized as a file.
     * @return \BLW\YUICompressor $this
     */
    function AddFile($File)
    {
        if (@is_file($File)) {
            $this->Files[] = $File;
        }

        else {
            throw new \BLW\Model\InvalidArgumentException(0);
        }

        return $this;
    }

    /**
     * Adds string content to YUI compressor.
     * @param string $String String to add to compressor.
     * @return \BLW\YUICompressor $this.
     */
    function AddString($String)
    {
        if (is_string($String)) {
            $this->String .= ' ' . $String;
        }

        else {
            throw new \BLW\Model\InvalidArgumentException(0);
        }

        return $this;
    }

    /**
     * Executes compression command and returns results
     * @return string Returns either compressed string or error output.
     */
    function Compress()
    {
        // Get total String
        $String = '';

        foreach ($this->Files as $File) {

            if (($Contents = file_get_contents($File)) !== false) {
                $String .= $Contents;
            }

            else {
                throw new \BLW\Model\FileException($File);
            	return $String;
            }
        }

        $String .= $this->String;

        if (self::$JavaEnabled) {

            // Compile output
            $File = tempnam($this->Options->TMP, md5($String));

            if(!@file_put_contents($File, $String)) {
                throw new \BLW\Model\FileException($File);
                return $String;
            }

            // Build Command
            $CMD = sprintf(
                'java -Xmx32m -jar %s --charset UTF-8 --type %s%s%s%s%s%s %s 2>&1'
                ,escapeshellarg($this->Options->JAR)
                ,strtolower($this->Options->Type)
                ,isset($this->Options->Linebreak)
                    ? (@intval($this->Options->Linebreak) > 0
                        ? ' --line-break ' . @intval($this->Options->Linebreak)
                        : ''
                    )
                    : ''
                ,!!$this->Options->Verbose
                    ? ' -v'
                    : ''
                ,!!$this->Options->SemiColon
                    ? ' --preserve-semi'
                    : ''
                ,!!$this->Options->NoOptimize
                    ? ' --disable-optimizations'
                    : ''
                ,!!$this->Options->NoMunge
                    ? ' --nomunge'
                    : ''
                ,escapeshellarg($File)
            );

            // Execute Command
            @exec($CMD, $Output, $Satus);
            sleep(1);
            @unlink($File);

            // Return results
            if($Status === 0) {
                $String = implode('\n', $Output);
            }

            unset($Output, $Status);
        }

        return $String;
    }
}

return true;