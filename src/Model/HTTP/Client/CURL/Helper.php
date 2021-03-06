<?php
/**
 * Helper.php | Apr 12, 2014
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
 * @package BLW\HTTP
 * @version GIT: 0.2.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model\HTTP\Client\CURL;

use RuntimeException;
use DateTime;
use BLW\Type\AURI;
use BLW\Model\InvalidArgumentException;

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
 * Helper class for cURL.
 *
 * <h3>Introduction</h3>
 *
 * <p>This is a helper class for cURL HTTP client.</p>
 *
 * <p>Responsibilities include:</p>
 *
 * <ul>
 * <li>Creating and initializing handles</li>
 * <li>Closing and freeing handles</li>
 * <li>Queing and executing requests to transport</li>
 * <li>Managing statistical data of requests</li>
 * </ul>
 *
 * <p>This is done due to the dual responsibility of IClient class.<p>
 *
 * <p>Since IClient class handles both the filtering / sanitizing /
 * queing of HTTP requests as well as actual execution of these
 * requests, it made more sense to have another class to actually
 * do the grunt work of feeding requests in parrallel to the
 * transport.</p>
 *
 * <p>This is more memmory inneficient but easier to test, modify
 * and maintain.</p>
 *
 * <hr>
 *
 * @todo Add tests for class.
 * @todo 1st Refractoring.
 * @todo Update Documentation.
 *
 * @package BLW\HTTP
 * @version GIT: 0.2.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
 */
class Helper
{

    /**
     * curl_multi handle
     *
     * @var resource $MainHandle
     */
    public $MainHandle = null;

    /**
     * curl_init handles
     *
     * @var resource[] $Handles
     */
    public $Handles = array();

    /**
     * Stores handles not in use
     *
     * @var resource[] $FreeHandles
     */
    public $FreeHandles = array();

    /**
     * Stores status information about curl requests:
     *
     * <ul>
     * <li><b>NewConnections</b>: Stores an array of <code>DateTime</code> for fresh connections</li>
     * <li><b>HostConnections</b>: Stores number of connections to a particular host</li>
     * </ul>
     *
     * @var array $Stats
     */
    public $Stats = array(
        'NewConnections' => array(),
        'HostConnections' => array()
    );

    /**
     * Constructor
     *
     * @link http://www.php.net/manual/en/function.curl-init.php curl_init()
     * @link http://www.php.net/manual/en/function.curl-multi-init.php curl_multi_init()
     *
     * @throws \RuntimeException If curl_init() or curl_multi_init() fails.
     * @throws \BLW\Model\InvalidArgumentException If <code>$Handles</code> is not an integer.
     *
     * @param integer $Handles
     *            Maximum handles to create for parralell operations.
     */
    public function __construct($Handles)
    {
        // Validate $Handles
        if (! is_int($Handles)) {
            throw new InvalidArgumentException(0);
        }

        // curl_multi handle
        $this->MainHandle = curl_multi_init();

        // @codeCoverageIgnoreStart

        // Check results
        if (! is_resource($this->MainHandle)) {
            throw new RuntimeException('Unable to initialize cURL multi session');
        }

        // Enable pipelining
        if (is_callable('curl_multi_setopt')) {
            curl_multi_setopt($this->MainHandle, CURLMOPT_PIPELINING, 1);
        }

        // @codeCoverageIgnoreEnd

        // curl_init handles
        foreach (range(0, $Handles) as $i) {

            // Create handle
            $this->Handles[$i] = curl_init();

            // Check result. Add handle to free handles.
            if (is_resource($this->Handles[$i])) {
                $this->FreeHandles[] = $this->Handles[$i];

            // @codeCoverageIgnoreStart

            // Unable to create handle? Exception.
            } else {
                throw new RuntimeException('Unable to initialize cURL session');
            }

            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Destructor
     *
     * @link http://www.php.net/manual/en/function.curl-multi-remove-handle.php curl_multi_remove_handle()
     * @link http://www.php.net/manual/en/function.curl-multi-close.php curl_multi_close()
     */
    public function __destruct()
    {
        // Make sure multi handle exists
        if (is_resource($this->MainHandle)) {

            // For each handle
            foreach ($this->Handles as $Handle) {
                if (is_resource($Handle)) {
                    // Remove handle
                    curl_multi_remove_handle($this->MainHandle, $Handle);
                }
            }

                // Close main handle
            curl_multi_close($this->MainHandle);
        }
    }

    /**
     * Get a resouce from $FreeHandles.
     *
     * @link http://www.php.net/manual/en/function.reset.php reset()
     * @link http://www.php.net/manual/en/function.current.php current()
     *
     * @return resource Next free resource. <code>FALSE</code> if none exist.
     */
    public function getFreeHandle()
    {
        // Are there free handles?
        reset($this->FreeHandles);

        return current($this->FreeHandles) ?  : false;
    }

    /**
     * Perform a request and update $FreeHandles.
     *
     * @link http://www.php.net/manual/en/function.curl-setopt-array.php curl_setopt_array()
     * @link http://www.php.net/manual/en/function.curl-setopt.php curl_multi_add_handle()
     *
     * @throws \RuntimeException If there is a cURL error.
     * @throws \BLW\Model\InvalidArgumentException If <code>$Handle</code> is not a valid resource.
     *
     * @param resource $Handle
     *            Resource from <code>curl_init()</code> stored in <code>$Handles</code>.
     * @param array $Options
     *            Options passed to <code>curl_setopt_array()</code>
     * @return boolean <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function execute($Handle, array $Options)
    {
        // Validate $Handle
        if (! is_resource($Handle) ?: ! in_array($Handle, $this->FreeHandles)) {
            throw new InvalidArgumentException(0);
        }

        // Prepare handle
        elseif (! $result = @curl_setopt_array($Handle, $Options)) {
            throw new RuntimeException('Unable to set cURL options', curl_errno($Handle));
        }

        // Add handle
        $result = curl_multi_add_handle($this->MainHandle, $Handle);

        // Check results
        if ($result == 0) {

            // Remove handle from free list
            $this->FreeHandles = array_filter($this->FreeHandles, function ($v) use ($Handle) {
                return $v != $Handle;
            });

            // Update Stats

            // 1. NewConnections
            $this->Stats['NewConnections'][] = new DateTime();

            // 2. HostConnections
            if (isset($Options[CURLOPT_URL])) {

                $Host = AURI::parse($Options[CURLOPT_URL]);
                $Host = $Host['host'];

                // Stats exist? Update
                if (isset($this->Stats['HostConnections'][$Host])) {
                    $this->Stats['HostConnections'][$Host][] = $Handle;

                // No stats? Create
                } else {
                    $this->Stats['HostConnections'][$Host] = array(
                        $Handle
                    );
                }
            }

            // Done
            return true;

        // @codeCoverageIgnoreStart

        // Unable to add
        } else {
            // Exception
            throw new RuntimeException(sprintf('Error [%d]: Unable to add curl request (%s)', $result, curl_error($Handle)), $result);
        }

        // @codeCoverageIgnoreEnd
    }

    /**
     * Frees a handle for reuse.
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Handle</code> is not a valid, in-use handle.
     *
     * @param resource $Handle
     * @return void
     */
    public function freeHandle($Handle)
    {
        // Validate $Handle
        if (!in_array($Handle, $this->Handles) || in_array($Handle, $this->FreeHandles)) {
            throw new InvalidArgumentException(0);
        }

        // Remove handle
        curl_multi_remove_handle($this->MainHandle, $Handle);

        // Add handle to free list
        $this->FreeHandles[] = $Handle;

        // UpdateStats

        // 1. HostConnections

        // Loop through each HostConnection
        foreach ($this->Stats['HostConnections'] as $Host => & $Handles) {

            // If handle is not current handle continue
            if (! in_array($Handle, $Handles)) {
                continue;
            }

                // Remove handle from list
            $Handles = array_filter($Handles, function ($v) use ($Handle) {
                return $v != $Handle;
            });

            // If list is empty? remove from list
            if (empty($Handles)) {
                unset($this->Stats['HostConnections'][$Host]);
            }
        }
    }

    /**
     * Calculates the number of new requests made per minute.
     *
     * @todo Everything
     * @return double Number of new requests in the last minute.
     */
    public function getRate()
    {
        // Get number of requests made in the last minute
        $count = 0;
        $Test  = new DateTime('-1 min -23 sec');

        foreach ($this->Stats['NewConnections'] as $k => $Time) {

            // Time is not expired? Add to count
            if ($Time > $Test) {
                $count ++;

            // Time too old? Remove from list
            } else {
                unset($this->Stats['NewConnections'][$k]);
            }
        }

        // Reindex
        $this->Stats['NewConnections'] = array_values($this->Stats['NewConnections']);

        // Convert result to float
        return floatval($count);
    }

    /**
     * Gets the connections to a particular host.
     *
     * @param string $Host
     * @return int
     */
    public function getConnections($Host)
    {
        // Do stats exist? return stats. Else? return 0.
        return isset($this->Stats['HostConnections'][$Host])
            ? count($this->Stats['HostConnections'][$Host])
            : 0;
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
