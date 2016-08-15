<?php

namespace fpoirotte\Pssht\Tests\Helpers;

/**
 * Abstract testcase to test connection.
 */
abstract class AbstractConnectionTest extends \PHPUnit_Framework_TestCase
{
    private static $phpBinary;

    protected $fakeHome;
    protected $configFile;
    protected $sshClient;

    private $serverPort;
    private $serverProcess;
    private $serverPipes;
    private $serverBuffers;

    private $clientProcess;
    private $clientPipes;
    private $clientBuffers;


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

    final private function prepareCommand()
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

        // Default configuration file for tests,
        // can be overriden by redefining
        // $configFile in subclasses.
        if ($this->configFile === null) {
            $this->configFile = dirname(__DIR__) .
                                DIRECTORY_SEPARATOR . 'pssht.xml';
        }

        // Launch pssht using the proper PHP binary and options.
        $command =
            escapeshellarg(self::$phpBinary) .
            " $options " .
            escapeshellarg(
                dirname(dirname(__DIR__)) .
                DIRECTORY_SEPARATOR . 'bin' .
                DIRECTORY_SEPARATOR . 'pssht'
            ) . ' ' .
            escapeshellarg($this->configFile);
        return $command;
    }

    final private function startServer()
    {
        $this->serverPipes = array();
        $this->serverPort = null;

        $logging    = \Plop\Plop::getInstance();
        $command    = $this->prepareCommand();
        $logging->debug('Starting test server: %s', array($command));

        $descriptors = array(
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );
        $this->serverBuffers = array(
            1 => new \fpoirotte\Pssht\Buffer(),
            2 => new \fpoirotte\Pssht\Buffer(),
        );
        $this->serverProcess = proc_open($command, $descriptors, $this->serverPipes);

        if ($this->serverProcess === false) {
            throw new \Exception('Could not start the test server');
        }

        $msg = 'SERVER: Listening for new connections on ';
        while (true) {
            $write = array();
            $read = $except = array($this->serverPipes[1], $this->serverPipes[2]);

            if (!@stream_select($read, $write, $except, null)) {
                throw new \Exception('Signal received');
            }

            if (count($except)) {
                throw new \Exception('Unexpected error');
            }

            foreach ($read as $stream) {
                $idx = array_search($stream, $this->serverPipes, true);
                if ($idx === false) {
                    throw new \Exception('Unknown stream');
                }

                $buffer = $this->serverBuffers[$idx];
                $data = fread($stream, 8192);
                if ($data === false) {
                    throw new \Exception('EOF reached');
                }
                $buffer->push($data);

                while (($line = $buffer->get(PHP_EOL)) !== null) {
                    $line = rtrim($line);
                    if ($idx === 1) {
                        $logging->debug("[STDOUT] %s", array($line));
                    } else {
                        $logging->error("[STDERR] %s", array($line));
                        break 2;
                    }

                    if (!strncmp($line, $msg, strlen($msg))) {
                        // Grab the port assigned to the server.
                        // "Listening for new connections on ...:..." (address:port)
                        $this->serverPort = (int) substr($line, strrpos($line, ':') + 1);
                        if ($this->serverPort === 0) {
                            throw new \Exception("Could not read the server's port");
                        }
                        $logging->info('Test server listening on port %d', array($this->serverPort));
                        return;
                    }
                }
            }
        }
    }

    final protected function initClient()
    {
        $this->clientPipes = array();

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

    final protected function doIPC()
    {
        $logging    = \Plop\Plop::getInstance();
        $output     = new \fpoirotte\pssht\Buffer();

        while (true) {
            $write = array();
            $read = $except = array(
                $this->serverPipes[1],
                $this->serverPipes[2],
                $this->clientPipes[1],
                $this->clientPipes[2],
            );

            if (!@stream_select($read, $write, $except, null)) {
                throw new \Exception('Signal received');
            }

            if (count($except)) {
                throw new \Exception('Unexpected error');
            }

            foreach ($read as $stream) {
                if (($idx = array_search($stream, $this->serverPipes, true)) !== false) {
                    $buffer = $this->serverBuffers[$idx];
                    $client = false;
                } else if (($idx = array_search($stream, $this->clientPipes, true)) !== false) {
                    $buffer = $this->clientBuffers[$idx];
                    $client = true;
                } else {
                    throw new \Exception('Unknown stream');
                }
$logging->info("received data for the %s's %s", array($client ? "client" : "server", $idx === 1 ? "STDOUT" : "STDERR"));


                $data = fread($stream, 8192);
                if ($data === false) {
                    $logging->error("EOF reached");
                    throw new \Exception('EOF reached');
                }
                $buffer->push($data);

                while (($line = $buffer->get(PHP_EOL)) !== null) {
                    // Preserve the client's STDOUT
                    if ($idx === 1 && $client) {
                        $output->push($line);
                    }

                    $line = rtrim($line);
                    $msg = $client ? "CLIENT: %s" : "%s";

                    if ($idx === 1) {
                        $logging->debug("[STDOUT] $msg", array($line));
                    } else {
                        $logging->error("[STDERR] $msg", array($line));
                    }
                }
            }

            $serverStatus = proc_get_status($this->serverProcess);
            if (!$serverStatus['running']) {
                $logging->error("Server stopped for no apparent reason");
                throw new \Exception('Server stopped for no apparent reason');
            }

            $clientStatus = proc_get_status($this->clientProcess);
            if ($clientStatus['signaled']) {
                throw new \Exception('Client terminated by signal ' . $clientStatus['termsig']);
            }

            if ($clientStatus['stopped']) {
                throw new \Exception('Client stopped by signal ' . $clientStatus['stopsig']);
            }

            if (!$clientStatus['running']) {
                $logging->info("End of client execution");
                fclose($this->clientPipes[1]);  // STDOUT
                fclose($this->clientPipes[2]);  // STDERR
                $this->clientPipes = array();
                proc_close($this->clientProcess);
                $this->clientProcess = false;
                $this->clientBuffers[1] = $output;
                return $clientStatus['exitcode'];
            }
        }
    }

    final protected function runClient($client)
    {
        $logging                = \Plop\Plop::getInstance();
        $descriptors            = array(
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );

        $this->clientBuffers    = array(
            1 => new \fpoirotte\Pssht\Buffer(),
            2 => new \fpoirotte\Pssht\Buffer(),
        );

        $this->clientProcess    = $this->sshClient->run(
            $descriptors,
            $this->clientPipes
        );

        $exitCode   = $this->doIPC();
        $output     = $this->clientBuffers[1]->get(0);
        return array($exitCode, $output);
    }

    final public function setUp()
    {
        \fpoirotte\Pssht\Algorithms::factory();

        $this->startServer();
        $this->initClient();
        $this->sshClient->setPort($this->serverPort);
        $this->sshClient->setHome($this->fakeHome);

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
    }

    final public function tearDown()
    {
        $logging = \Plop\Plop::getInstance();
        if (count($this->clientPipes)) {
            $logging->info("Closing the client's file descriptors");
            fclose($this->clientPipes[1]);  // STDOUT
            fclose($this->clientPipes[2]);  // STDERR
        }
        if (is_resource($this->clientProcess)) {
            $logging->info("Freeing client resources");
            $status = proc_get_status($this->clientProcess);
            posix_kill($status['pid'], defined('SIGKILL') ? constant('SIGKILL') : 9);
            proc_close($this->clientProcess);
        }

        if (count($this->serverPipes)) {
            $logging->info("Closing the server's file descriptors");
            fclose($this->serverPipes[1]);  // STDOUT
            fclose($this->serverPipes[2]);  // STDERR
        }
        if (is_resource($this->serverProcess)) {
            $logging->info("Freeing server resources");
            $status = proc_get_status($this->serverProcess);
            posix_kill($status['pid'], defined('SIGKILL') ? constant('SIGKILL') : 9);
            proc_close($this->serverProcess);
        }
        $this->serverPort = null;
    }
}
