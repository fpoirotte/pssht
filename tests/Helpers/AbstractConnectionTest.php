<?php

namespace fpoirotte\Pssht\Tests\Helpers;

use fpoirotte\Pssht\Tests\Helpers\OutputException;

/**
 * Abstract testcase to test connection.
 */
abstract class AbstractConnectionTest extends \PHPUnit_Framework_TestCase
{
    protected $fakeHome;
    protected $configFile;
    protected $sshClient;
    protected $ipc;

    private static $phpBinary;
    private static $serverProcess;
    private static $serverPID;
    private static $serverPort;

    final protected function initClient()
    {
        // Default configuration file for tests,
        // can be overriden by redefining
        // $configFile in subclasses.
        if ($this->configFile === null) {
            $this->configFile = dirname(__DIR__) .
                                DIRECTORY_SEPARATOR . 'pssht.xml';
        }

        // HOME for the "known_hosts" / "sshhostkeys" file.
        if ($this->fakeHome === null) {
            $this->fakeHome = dirname(__DIR__) .
                DIRECTORY_SEPARATOR . 'data' .
                DIRECTORY_SEPARATOR . 'known_hosts';
        }

        try {
            $this->sshClient = new \fpoirotte\Pssht\Tests\Helpers\SshClient\Openssh('localhost');
        } catch (\Exception $e) {
            try {
                $this->sshClient = new \fpoirotte\Pssht\Tests\Helpers\SshClient\Putty('localhost');
            } catch (\Exception $e) {
                $this->markTestSkipped('No usable SSH client found');
            }
        }
    }

    private static function locatePhpBinary()
    {
        if (self::$phpBinary !== null) {
            return self::$phpBinary;
        }

        // Try to locate the php binary.
        if (defined('PHP_BINARY')) {
            // PHP_BINARY is only defined on PHP 5.4+.
            return PHP_BINARY;
        }

        $binary = findBinary('php', true, array(PHP_BINDIR));
        if ($binary === null) {
            throw new \Exception('Could not locate PHP binary');
        }
        return $binary;
    }

    private function prepareCommand()
    {
        if (self::$phpBinary === null) {
            self::$phpBinary = self::locatePhpBinary();
        }

        $options    =
            // Do not use the configuration file (php.ini).
            // For HHVM, we make it use "/dev/null" or equivalent
            // as its configuration file because the "-n" option
            // does not exist.
            (defined('HHVM_VERSION') ? '-c ' . $null : '-n') .
            ' ' .
            '-d display_startup_errors=Off '.
            '-d detect_unicode=0 ' .
            '-d date.timezone=UTC ' .
            '-d ' . escapeshellarg('extension_dir=' . PHP_EXTENSION_DIR);

        $extensions = array(
            // Standard extensions
            'ctype',    // Required by symfony/config
            'dom',      // Required by symfony/config
            'gmp',
            'hash',
            'iconv',    // Required by ext-http
            'mcrypt',
            'openssl',
            'pcntl',
            'posix',
            'reflection',
            'sockets',
            'spl',

            // Additional extensions
            // raphf & propro must be loaded before http.
            'raphf',    // Required by ext-http
            'propro',   // Required by ext-http
            'http',
        );

        // Load all necessary (shared) extensions.
        foreach ($extensions as $extension) {
            $file = PHP_EXTENSION_DIR . DIRECTORY_SEPARATOR .
                    $extension . '.' . PHP_SHLIB_SUFFIX;
            if (file_exists($file)) {
                $options .= ' -d extension=' .
                            $extension . '.' .
                            PHP_SHLIB_SUFFIX;
            }
        }

        // Launch pssht using the proper PHP binary and options.
        $null = strncasecmp(PHP_OS, 'Win', 3) ? '/dev/null' : 'NUL';
        $command =
            escapeshellarg(self::$phpBinary) .
            " $options " .
            escapeshellarg(
                dirname(dirname(__DIR__)) .
                DIRECTORY_SEPARATOR . 'bin' .
                DIRECTORY_SEPARATOR . 'pssht'
            ) . ' ' .
            escapeshellarg($this->configFile) . ' < ' . $null . ' 2>&1';
        return $command;
    }

    private function startServer()
    {
        $logging    = \Plop\Plop::getInstance();
        $null       = strncasecmp(PHP_OS, 'Win', 3) ? '/dev/null' : 'NUL';
        $command    = $this->prepareCommand();
        $logging->debug('Starting test server: %s', array($command));
        self::$serverPID        = null;
        self::$serverProcess    = popen($command, 'r');
        if (self::$serverProcess === false) {
            self::$serverProcess = null;
            throw new \Exception('Could not start the test server using ' .
                                 'this command line: ' . $command);
        }
        $logging->info('The test server is starting...');
        $this->ipc = array(
            array(self::$serverProcess, new \fpoirotte\Pssht\Buffer())
        );

        while (true) {
            $read = $except = array($this->ipc[0][0]);
            $write = array();
            if (!@stream_select($read, $write, $except, null)) {
                throw new \Exception('Signal received');
            }

            if (count($except)) {
                throw new \Exception('Unexpected server shutdown');
            }

            $data = fread($this->ipc[0][0], 8192);
            if ($data === false) {
                throw new \Exception('Could not read data');
            }
            $this->ipc[0][1]->push($data);

            $line = $this->ipc[0][1]->get(PHP_EOL);
            if ($line === null) {
                sleep(1);
                continue;
            }
            $line = rtrim($line);

            $msg  = 'LOG: pssht ';
            $msg2 = 'LOG: Listening for new connections on ';

            if (!strncmp($line, $msg, strlen($msg) - 1) &&
                // Grab the server's PID.
                // "pssht v... is starting (PID ...)"
                strpos($line, 'is starting')) {
                self::$serverPID = (int) substr($line, strrpos($line, '(') + 5, -1);
                if (self::$serverPID === 0) {
                    throw new \Exception("Could not read the server's PID");
                }
                $logging->info('Test server started (PID %d)', array(self::$serverPID));
            } elseif (!strncmp($line, $msg2, strlen($msg2) - 1)) {
                // Grab the port assigned to the server.
                // "Listening for new connections on ...:..." (address:port)
                self::$serverPort = (int) substr($line, strrpos($line, ':') + 1);
                if (self::$serverPort === 0) {
                    throw new \Exception("Could not read the server's port");
                }
                $logging->info(
                    'Test server listening on port %d',
                    array(self::$serverPort)
                );
                break;
            }
        }
    }

    private function doIPC($sec=null, $usec=null)
    {
        while (true) {
            $read = $except = array();
            foreach (array(0, 1) as $idx) {
                if (isset($this->ipc[$idx][0]) && !feof($this->ipc[$idx][0])) {
                    $read[$idx] = $this->ipc[$idx][0];
                }
            }
            $except = $read;
            $write  = array();

            if (!count($read)) {
                return;
            }

            $nb     = stream_select($read, $write, $except, $sec, $usec);
            if ($nb === false) {
                throw new \Exception('Signal received');
            }
            if ($nb === 0) {
                return;
            }

            $feof = false;
            foreach ($read as $idx => $fd) {
                $data = fread($fd, 8192);
                if ($data === false) {
                    throw new \Exception('Could not read data');
                }

                $this->ipc[$idx][1]->push($data);

                if (feof($fd)) {
                    $feof = true;
                }
            }

            if (count($except)) {
                throw new \Exception('Unknown error');
            }

            if ($feof) {
                return;
            }
        }
    }

    final protected function runClient($client)
    {
        $process        = $client->run();
        $logging        = \Plop\Plop::getInstance();
        $this->ipc[1]   = array($process, new \fpoirotte\Pssht\Buffer());
        $this->doIPC();

        $exitCode   = pclose($this->ipc[1][0]);
        $output     = $this->ipc[1][1]->get(0);
        unset($this->ipc[1]);

        $this->doIPC(0, 2000);
        while (($line = $this->ipc[0][1]->get(PHP_EOL)) !== null) {
            if (strncmp($line, 'LOG: ', 5)) {
                $this->fail('Test server: ' . rtrim($line));
            } else {
                $logging->debug(rtrim($line));
            }
        }

        return array($exitCode, $output);
    }

    final public function setUp()
    {
        $this->initClient();

        // PuTTY stores its configuration in the registry on Windows,
        // preventing us from overriding it like we do on Linux/Unix.
        //
        // There exists modified builds that can store the configuration
        // in a separate file. We can't rely on that since these versions
        // are not official.
        if (!strncasecmp(PHP_OS, 'Win', 3) &&
            ($this->sshClient instanceof
             \fpoirotte\Pssht\Tests\Helpers\SshClient\PuTTY)) {
            return $this->markTestSkipped("Windows is not yet supported");
        }

        // Start pssht for real and initialize the algorithms.
        if (self::$serverPID === null) {
            $this->startServer();
            \fpoirotte\Pssht\Algorithms::factory();
        } else {
            $this->ipc = array(
                array(self::$serverProcess, new \fpoirotte\Pssht\Buffer())
            );
        }

        $this->sshClient->setPort(self::$serverPort);
        $this->sshClient->setHome($this->fakeHome);
    }

    protected final function runTest()
    {
        $e = null;
        try {
            parent::runTest();
        } catch (\Exception $e) {
            if ($e instanceof PHPUnit_Framework_SelfDescribing) {
                $buffer = $e->toString();
                if (!empty($buffer)) {
                    $buffer = trim($buffer) . "\n";
                }
            } else {
                $buffer = $e->getMessage() . "\n";
            }

            $e = new OutputException(
                $buffer . PHP_EOL . $this->getActualOutput(),
                0,
                null,
                null,
                $e->getTrace()
            );
        }

        // HACK: swallow original test STDOUT.
        $this->setOutputCallback('is_object');
        if ($e) {
            throw $e;
        }
    }

    public final static function tearDownAfterClass()
    {
        if (self::$serverPID !== null) {
            // Just kill the damn thing already!
            posix_kill(self::$serverPID, defined('SIGINT') ? SIGINT : 15);
            pclose(self::$serverProcess);
            self::$serverPID = null;
        }
    }
}
