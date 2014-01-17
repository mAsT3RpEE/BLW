<?php
/**
 * Application.php | Jan 05, 2014
 *
 * Copyright (c) 2013-2018 mAsT3RpEE's Zone
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
namespace BLW\Interfaces;

/**
 * Interface for applications (both web and console).
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 */
interface Application
{
    /**
     * Configures the application.
     * @api BLW
     * @since 1.0.0
     * @param array $Options Congigurations options.
     * @return \BLW\Interfaces\Application $this.
     */
    public function configure($Options = array());

    /**
     * Function that is called after main configuration
     * @api BLW
     * @since 1.0.0
     * @return \BLW\Interfaces\Application $this.
     */
    public function doConfigure();

    /**
     * Adds a command to the Application.
     * @api BLW
     * @since 1.0.0
     * @param \BLW\Interfaces\Command $Command Command to add to application.
     * @return \BLW\Interfaces\Application $this.
     */
    public function push(\BLW\Interfaces\ApplicationCommand $Command);

    /**
     * Start the application or run a default command.
     * @api BLW
     * @since 1.0.0
     * @return int Exit code of the application.
     */
    public function start();

    /**
     * Stop the application.
     * @api BLW
     * @since 1.0.0
     * @return void
     */
    public function stop();

    /**
     * Whether application has been stopped by stop method.
     * @api BLW
     * @since 1.0.0
     * @return boolean Value of $_isStopped.
     */
    public function isStopped();
}