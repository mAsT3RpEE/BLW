<?php
/**
 * Browser.php | Jan 17, 2014
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
 *	@package BLW\Core
 *	@version 1.0.0
 *	@author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Interfaces; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

/**
 * Browser Interface
 *
 * <h4>Note</h4>
 *
 * <p>All browsers must implement this interface</p>
 *
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
interface Browser extends \BLW\Interfaces\Object
{
    /**
     * Get the UserAgent
     * @return string UserAgent
     */
    public function GetUserAgent();

    /**
     * Set the UserAgent
     * @param string $UserAgent
     */
    public function SetUserAgent($UserAgent);

    /**
     * Start the browser at the specified URL.
     * @param string $URL Address to go to.
     * @return \BLW\Model\Browser\Casper $this
     */
    public function Start($URL = NULL);

    /**
     * Open URL after the initial opening
     * @param string $URL Address to go to.
     * @return \BLW\Model\Browser\Casper $this
     */
    public function Navigate($URL);

    /**
     * Fill the form with the array of data.
     * @param string $Selector CSS selector for form
     * @param array $Data Data to fill form with
     * @param bool $Submit Whether to submit the form after.
     * @return \BLW\Model\Browser\Casper $this
     */
    public function Fill($Selector, $Data = array(), $Submit = false);

    /**
     * Sleep
     * @param number $Time
     * @return \BLW\Model\Browser\Casper $this
     */
    public function Wait($Time = 0);

    /**
     * Click a link / button / input
     * @param string $Selector CSS Selector
     * @return \BLW\Model\Browser\Casper $this
     */
    public function Click($Selector);

    /**
     * Dumps the current page.
     * @return \BLW\Model\Browser\Casper $this
     */
    public function Dump();

    /**
     * Run browser stored actions.
     * @return string Output from casperjs
     */
    public function Run();

    /**
     * Get index of contents that have been dumped by Dump.
     * @param int $Index Index contents was dumped.
     * @return NULL|string Contents.
     */
    public function GetContents($Index = 0);

    /**
     * Get index of HTML that have been dumped by Dump.
     * @param int $Index Index contents was dumped.
     * @return NULL|string Contents.
     */
    public function GetHTML($Index = 0);

    /**
     * Retrieves the current URL address of browser.
     * @return string Current Address.
     */
    public function GetCurrentUrl();

    /**
     * Retrieves the addresses used by browser.
     * @return string[] All addresses.
     */
    public function GetRequestedUrls();
}