<?php
/**
 * Shell.php | Mar 31, 2014
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
 * @package BLW\Command
 * @version GIT: 0.2.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model\Command;

use ReflectionMethod;
use ArrayObject;
use BLW\Type\IConfig;
use BLW\Type\IMediator;
use BLW\Type\Command\IInput;
use BLW\Type\Command\IOutput;
use BLW\Model\InvalidArgumentException;
use BLW\Model\Command\Exception as CommandException;

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
 * Command for handling shell commands.
 *
 * @package BLW\Command
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property mixed $Command [readonly] $_Command
 * @property \BLW\Type\IConfig $Config [readonly] $_Config
 * @property integer $Timeout [dynamic] $_Command[Timeout]
 * @property $Mediator \BLW\Type\IMediator [dynamic] Invokes setMediator() and getMediator().
 * @property $MediatorID string [readonly] Invokes getMediator().
 */
class Shell extends \BLW\Type\Command\ACommand implements \BLW\Type\IFactory
{

###########################################################################################
# ShellCommand Trait
###########################################################################################

    // PROCESS PIPES
    const STDIN  = 0;
    const STDOUT = 1;
    const STDERR = 2;

    // BLOCK SIZE FOR READS / WRITES (KB)
    const BLOCKSIZE = 2048;

    /**
     * Processs resource.
     *
     * @var resource $_fp
     */
    protected $_fp = null;

    /**
     * Temporary storage for exit code.
     *
     * @var int $_ExitCode
     */
    protected $_ExitCode = -1;

    /**
     * Temporary storage for process input / output pipes.
     *
     * @var resource[] $_Pipes
     */
    protected $_Pipes = array();

    /**
     * Exit codes translation table.
     *
     * <h4>Note</h4>
     *
     * <p>User-defined errors must use exit codes in the 64-113 range.</p>
     *
     * <hr>
     *
     * @link https://github.com/symfony/Process/blob/master/Process.php Symfony/Process
     * @var string[] $exitCodes
     */
    public static $exitCodes = array(
        0 => 'OK',
        1 => 'General error',
        2 => 'Misuse of shell builtins',

        126 => 'Invoked command cannot execute',
        127 => 'Command not found',
        128 => 'Invalid exit argument',

        // signals
        129 => 'Hangup',
        130 => 'Interrupt',
        131 => 'Quit and dump core',
        132 => 'Illegal instruction',
        133 => 'Trace/breakpoint trap',
        134 => 'Process aborted',
        135 => 'Bus error: "access to undefined portion of memory object"',
        136 => 'Floating point exception: "erroneous arithmetic operation"',
        137 => 'Kill (terminate immediately)',
        138 => 'User-defined 1',
        139 => 'Segmentation violation',
        140 => 'User-defined 2',
        141 => 'Write to pipe with no one reading',
        142 => 'Signal raised by alarm',
        143 => 'Termination (request to terminate)',
        // 144 - not defined
        145 => 'Child process terminated, stopped (or continued*)',
        146 => 'Continue if stopped',
        147 => 'Stop executing temporarily',
        148 => 'Terminal stop signal',
        149 => 'Background process attempting to read from tty ("in")',
        150 => 'Background process attempting to write to tty ("out")',
        151 => 'Urgent data available on socket',
        152 => 'CPU time limit exceeded',
        153 => 'File size limit exceeded',
        154 => 'Signal raised by timer counting virtual time: "virtual timer expired"',
        155 => 'Profiling timer expired',
        // 156 - not defined
        157 => 'Pollable event',
        // 158 - not defined
        159 => 'Bad syscall'
    );

###########################################################################################




###########################################################################################
# Factory Trait
###########################################################################################

    /**
     * Return an array of factory methods associated with the class.
     *
     * @return \ReflectionMethod[] Array of factory methods.
     */
    public static function getFactoryMethods()
    {
        return array(
            new ReflectionMethod(get_called_class(), 'createCommandLine'),
            new ReflectionMethod(get_called_class(), 'createDescriptors')
        );
    }

    /**
     * Creates a command line string from $Comand and $Input.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param string $Command
     *            Command to execute
     * @param IInput $Input
     *            Command input.
     * @return string Generated command line.
     */
    public static function createCommandLine($Command, IInput $Input)
    {
        static $Format = '%s %s';

        // Command
        $Command = strval($Command);

        // Add Options / Arguments
        $extras = array();

        foreach ($Input->Options as $v) {
            $extras[] = strval($v);
        }

        foreach ($Input->Arguments as $v) {
            $extras[] = strval($v);
        }

        // Build command line
        return sprintf($Format, $Command, implode(' ', $extras));
    }

    /**
     * Creates descriptors used by proc_open() in doRun()
     *
     * @api BLW
     * @since   1.0.0
     * @link http://www.php.net/manual/en/function.proc-open.php proc_open()
     *
     * @return array $_Descriptors [static] Descriptors used in proc_open().
     */
    public static function createDescriptors()
    {
        return array(
            self::STDIN => array(
                'pipe',
                'r'
            ),
            self::STDOUT => array(
                'pipe',
                'w'
            ),
            self::STDERR => array(
                'pipe',
                'w'
            )
        );
    }

###########################################################################################
# ShellCommand Trait
###########################################################################################

    /**
     * Constructor
     *
     * @param mixed $Command
     *            Command to run.
     * @param IConfig $Config
     *            Command configuration.
     *
     * <ul>
     * <li><b>Timeout</b>: <i>int</i> Timeout in seconds for a response from shellcommand.</li>
     * <li><b>CWD</b>: <i>string</i> Path to directory to use as native path of shellcommand.</li>
     * <li><b>Enviroment</b>: <i>array</i> Enviroment variables.</li>
     * <li><b>Extras</b>: <i>array</i> Extras passed to <code>proc_open()</code>.</li>
     * </ul>
     *
     * @param IMediator $Mediator
     *            Mediator for command.
     * @param string $ID
     *            Command Name / Label.
     */
    public function __construct($Command, IConfig $Config, IMediator $Mediator = null, $ID = null)
    {
        // Check proc_open and proc_close
        if (! is_callable('proc_open') || ! is_callable('proc_close')) {
            // @codeCoverageIgnoreStart
            throw new CommandException('Unable to create shell command. Either proc_open() or proc_close() have been disabled.');
            // @codeCoverageIgnoreEnd

        // CheckConfig
        } elseif (! $Config->offsetExists('Timeout')) {
            throw new InvalidArgumentException(1, '%header% $Config[Timeout] is not set');

        } elseif (! $Config->offsetExists('CWD')) {
            throw new InvalidArgumentException(1, '%header% $Config[CWD] is not set');

        } elseif (! $Config->offsetExists('Environment')) {
            throw new InvalidArgumentException(1, '%header% $Config[Enviroment] is not set');

        } elseif (! $Config->offsetExists('Extras')) {
            throw new InvalidArgumentException(1, '%header% $Config[Extras] is not set');

        } elseif (! is_array($Config['Extras'])) {
            throw new InvalidArgumentException(1, '%header% $Config[Extras] is not an array');
        }

        // Set properties
        $this->_Command = strval($Command);
        $this->_Config  = $Config;

        // Mediator
        if ($Mediator) {
            $this->setMediator($Mediator);
        }

        // ID
        $this->ID = is_null($ID)
            ? $this->createID()
            : $ID;

        // DataMapper
        $this->_DataMapper = new ArrayObject();
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->close(true);
    }

    /**
     * Performs the actual command run.
     *
     * @see \BLW\Type\Command\ACommand::doRun() ACommand::doRun()
     * @see \BLW\Type\Command\ACommand::run() ACommand::run()
     * @link http://www.php.net/manual/en/function.proc-open.php proc_open()
     *
     * @throws \BLW\Model\Command\Exception If unnable to run command.
     *
     * @param \BLW\Type\Command\IInput $Input
     *            Input object to read data from.
     * @param \BLW\Type\Command\IOutput $Output
     *            Output object to write data to.
     * @return mixed Result of command.
     */
    public function doRun(IInput $Input, IOutput $Output)
    {
        // Open process
        if (! $this->open($Input)) {
            // @codeCoverageIgnoreStart
            throw new CommandException(sprintf('%s Unable to run command (%s)', '%header%', $CommandLine));
            // @codeCoverageIgnoreEnd
        }

        // Transfer both input and output
        try {
            $Timeout = intval($this->_Config['Timeout']);

            $this->transferStreams($Input, $Output, $Timeout);

        // @codeCoverageIgnoreStart

        // Forward exceptions
        } catch (CommandException $e) {
            throw new CommandException('%header% Error: ' . $e->getMessage(), $e->getCode(), $e);
        }

        // @codeCoverageIgnoreEnd

        // Close process
        $this->close();

        // Done
        return $this->_ExitCode;
    }

    /**
     * Takes a command input an opens a process for intermittent running.
     *
     * @api BLW
     * @since   1.0.0
     * @see \BLW\Model\Command\self::close() self::close()
     *
     * @param \BLW\Type\Command\IInput $Input
     *            Input object to read data from.
     * @return boolean <code>TRUE</code> on success.<code>FALSE</code> otherwise.
     */
    public function open(IInput $Input)
    {
        // CommandLine
        $CommandLine = $this->createCommandLine($this->_Command, $Input);

        // Configuration
        $CWD         = strval($this->_Config['CWD']) ?: null;
        $Environment = $this->_Config['Environment'] ?: null;
        $Extras      = $this->_Config['Extras'] ?: array();

        // Open proces handle
        $fp = proc_open($CommandLine, $this->createDescriptors(), $Pipes, $CWD, $Environment, $Extras);

        // Check result
        if (! is_resource($fp)) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        // Update process resource / pipes
        $this->_fp    = $fp;
        $this->_Pipes = $Pipes;

        // Success
        return true;
    }

    /**
     * Closes a process
     *
     * @api BLW
     * @since   1.0.0
     * @see \BLW\Model\Command\self::open() self::open()
     *
     * @param boolean $Terminate
     *            Whether to close normally or terminate process.
     * @return mixed Result of command.
     */
    public function close($Terminate = false)
    {
        // Is process still open?
        if (is_resource($this->_fp)) {

            // Close pipes
            foreach ($this->_Pipes as $Pipe) {
                if (is_resource($Pipe)) {
                    fclose($Pipe);
                }
            }

            // $Terminate flag?
            if (! $Terminate) {
                // Close process
                $this->_ExitCode = proc_close($this->_fp);

            // No terminate flag
            } else {
                // Try command line
                $pid   = $this->getStatus('pid');
                $descr = array(
                     0 => array('pipe', 'r'),
                     1 => array('pipe', 'w'),
                     2 => array('pipe', 'w')
                );
                $fp    = proc_open(DIRECTORY_SEPARATOR ==  '\\'? "taskkill /PID $pid" : "kill $pid", $descr, $pipes);

                foreach ($pipes as $pipe) {
                    fclose($pipe);
                }

                proc_close($fp);

                // Sleep
                usleep(100000);

                // Try to terminate
                proc_terminate($this->_fp);
            }

            // Done
            return $this->_ExitCode;
        }

        // Modify time limit
        if (is_callable('set_time_limit')) {
            set_time_limit(ini_get('max_execution_time'));
        }
    }

    /**
     * Transfers input streams.
     *
     * @param \BLW\Type\Command\IInput $Input
     *            Command input.
     * @param array $WritePipes
     *            Process write pipes.
     * @return boolean <code>TRUE</code> if bytes were transfered. <code>FALSE</code> otherwise.
     */
    private function _transferStdIn(IInput $Input, array &$WritePipes)
    {
        // Read from $Input
        $written = fread($Input->stdIn->fp, self::BLOCKSIZE);
        $size    = fwrite($this->_Pipes[self::STDIN], $written);

        // Input Event
        if ($this->Mediator instanceof IMediator) {
            $this->_do('Input', new Event($Input->stdIn, self::STDIN, array(
                'Bytes'  => &$size,
                'Data'   => &$written,
                'Pipes'  => &$WritePipes
            )));
        }

        // Are we at the end?
        if (feof($Input->stdIn->fp)) {

            // Remove from stream_select
            unset($WritePipes[0]);

            // Sleep 0.1 seconds
            usleep(100000);
        }

        // Run through loop again
        return (bool) $size;
    }

    /**
     * Transfers output streams.
     *
     * @param \BLW\Type\Command\IOutput $Output
     *            Command output.
     * @param array $ReadPipes
     *            Process read pipes.
     * @return boolean <code>TRUE</code> if bytes were transfered. <code>FALSE</code> otherwise.
     */
    private function _transferStdOut(IOutput $Output, array &$ReadPipes)
    {
        // Read from pipe
        $written = fread($this->_Pipes[self::STDOUT], self::BLOCKSIZE);
        $size    = fwrite($Output->stdOut->fp, $written);

        // Output Event
        if ($this->Mediator instanceof IMediator) {
            $this->_do('Output', new Event($Output->stdOut, self::STDOUT, array(
                'Bytes'  => &$size,
                'Data'   => &$written,
                'Pipes'  => &$ReadPipes
            )));
        }

        // Are we at the end? Close pipe
        if (feof($this->_Pipes[self::STDOUT])) {

            // Remove from stream_select
            unset($ReadPipes[0]);
        }

        // Run through loop again
        return (bool) $size;
    }

    /**
     * Transfers error streams.
     *
     * @param \BLW\Type\Command\IOutput $Output
     *            Command output.
     * @param array $ReadPipes
     *            Process read pipes.
     * @return boolean <code>TRUE</code> if bytes were transfered. <code>FALSE</code> otherwise.
     */
    private function _transferStdErr(IOutput $Output, array &$ReadPipes)
    {
        // Read from pipe
        $written = fread($this->_Pipes[self::STDERR], self::BLOCKSIZE);
        $size    = fwrite($Output->stdErr->fp, $written);

        // Error event
        if ($this->Mediator instanceof IMediator) {
            $this->_do('Error', new Event($Output->stdErr, self::STDERR, array(
                'Bytes'  => &$size,
                'Data'   => &$written,
                'Pipes'  => &$ReadPipes
            )));
        }

        // Are we at the end? Close pipe
        if (feof($this->_Pipes[self::STDERR])) {

            // Remove from stream_select
            unset($ReadPipes[1]);
        }

        // Run through loop again
        return (bool) $size;
    }

    /**
     * Transfers streams to / from process input / output.
     *
     * @api BLW
     * @since   1.0.0
     * @see \BLW\Model\Command\self::open() self::open()
     * @see \BLW\Model\Command\self::close() self::close()
     *
     * @Event $Input.Input
     * @Event $Output.Output
     * @Event $Output.Error
     *
     * @throws \BLW\Model\Command\Exception If a timeout occurs.
     *
     * @param \BLW\Type\Command\IInput $Input
     *            Command input.
     * @param \BLW\Type\Command\IOutput $Output
     *            Command output.
     * @param integer $Timeout
     *            Timeout in seconds.
     */
    public function transferStreams(IInput $Input, IOutput $Output, $Timeout)
    {
        // Set up pipes
        stream_set_blocking($Input->stdIn->fp, 0);
        stream_set_blocking($this->_Pipes[self::STDIN], 0);
        stream_set_blocking($this->_Pipes[self::STDOUT], 0);
        stream_set_blocking($this->_Pipes[self::STDERR], 0);

        // Run loop
        $Timeout    = max(5, intval($Timeout));
        $WritePipes = array(
            $this->_Pipes[self::STDIN]
        );
        $ReadPipes  = array(
            $this->_Pipes[self::STDOUT],
            $this->_Pipes[self::STDERR]
        );

        do {

            // Modify time limit
            if (is_callable('set_time_limit')) {
                set_time_limit(max($Timeout, (int) ini_get('max_execution_time')));
            }

            // Wait for streams to become available
            $read    = $ReadPipes;
            $write   = $WritePipes;
            $except  = array();
            $written = false;

            // Running status of process
            $RunStatus = is_resource($this->_fp)
                ? $this->getStatus('running')
                : false;

            // If process has stopped or wait for input / output
            if (@stream_select($read, $write, $except, $Timeout, 100000) > 0) {

                // STDIN ready?
                if (in_array($this->_Pipes[self::STDIN], $write)) {
                    // Transfer BLOCKSIZE @ a time
                    $written = $this->_transferStdIn($Input, $WritePipes);

                // STDOUT ready?
                } elseif (in_array($this->_Pipes[self::STDOUT], $read)) {
                    // Transfer BLOCKSIZE @ a time
                    $written = $this->_transferStdOut($Output, $ReadPipes);

                // STDERR ready?
                } elseif (in_array($this->_Pipes[self::STDERR], $read)) {
                    // Transfer BLOCKSIZE @ a time
                    $written = $this->_transferStdErr($Output, $ReadPipes);
                }

            // If system call has not been interupted and process is still running
            } elseif ($RunStatus && ! $this->isSystemCallInterupt()) {
                // @codeCoverageIgnoreStart
                // Timeout
                throw new CommandException('Timeout occurred while waiting for input / output');
                // @codeCoverageIgnoreEnd
            }

        } while (($RunStatus || $written) && $ReadPipes);

        // Process exited? Update $_ExitCode
        if (is_resource($this->_fp)) {
            $this->getStatus('exitcode');
        }
    }

    /**
     * Get process status
     *
     * @api   BLW
     * @since 1.0.0
     * @link  http://www.php.net/manual/en/function.proc-get-status.php proc_get_status()
     *
     * @throws \BLW\Model\Command\Exception On error.
     *
     * @param string $Status
     *            Status to retrieve.
     * @return mixed Either and array or status or <code>NULL</code> in case of error.
     *
     * <ul>
     * <li><b>command</b>:<i>string</i> - The command string that was passed to proc_open().</li>
     * <li><b>pid</b>:<i>int</i> - process id.</li>
     * <li><b>running</b>:<i>bool</i> - <code>TRUE</code> if the process is still running, <code>FALSE</code> if it has terminated.</li>
     * <li><b>signaled</b>:<i>bool</i> - <code>TRUE</code> if the child process has been terminated by an uncaught signal. Always set to <code>FALSE</code> on Windows.</li>
     * <li><b>stopped</b>:<i>bool</i> - <code>TRUE</code> if the child process has been stopped by a signal. Always set to <code>FALSE</code> on Windows.</li>
     * <li><b>exitcode</b>:<i>int</i> - The exit code returned by the process (which is only meaningful if running is <code>FALSE</code>).
     * Only first call of this function return real value, next calls return -1.</li>
     * <li><b>termsig</b>:<i>int</i> - The number of the signal that caused the child process to terminate its execution (only meaningful if signaled is <code>TRUE</code>).</li>
     * <li><b>stopsig</b>:<i>int</i> - The number of the signal that caused the child process to stop its execution (only meaningful if stopped is <code>TRUE</code>).</li>
     * </ul>
     */
    public function getStatus($Status)
    {
        // Get Status
        if (!is_resource($this->_fp) ?: ! $return = @proc_get_status($this->_fp)) {
            // @codeCoverageIgnoreStart
            throw new CommandException('Unable to get process status');
            // @codeCoverageIgnoreEnd
        }

        // ################################################
        // proc_get_status() only returns exit code 1 time
        // so we must cache all exit codes just in case
        // ################################################

        // Exit code uknown?
        if ($return['exitcode'] == - 1 && $this->_ExitCode !== null) {
            // Restore previous code
            $return['exitcode'] = $this->_ExitCode;

        // Exit code present?
        } elseif ($return['exitcode'] != - 1) {
            // Save exit code
            $this->_ExitCode = $return['exitcode'];
        }

        // Does status exist? Return status.
        if (isset($return[$Status])) {
            return $return[$Status];

        // No status? Exception.
        } else {
            throw new InvalidArgumentException(0);
        }
    }

    /**
     * Determines if a system call has been interupted.
     *
     * @api BLW
     * @since   1.0.0
     * @link http://www.php.net/manual/en/function.error-get-last.php error_get_last()
     *
     * @param array $Error
     *            Array of errorinfo similar to <code>error_get_last()</code>.
     * @return boolean <code>TRUE</code> if a system call has been interupted. <code>FALSE</code> otherwise.
     */
    public static function isSystemCallInterupt(array $Error = null)
    {
        $Error = $Error ?: error_get_last();

        return isset($Error['message'])
            ? stripos($Error['message'], 'interrupted system call') > 0
            : false;
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
