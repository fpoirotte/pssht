<?php

namespace fpoirotte\Pssht\Tests\Helpers;

/**
 * Abstract testcase to test connection.
 */
abstract class AbstractConnectionTest extends \PHPUnit_Framework_TestCase
{
    protected $fakeHome;
    protected $configFile;
    protected $sshClient;

    private static $phpBinary;
    private static $serverProcess;
    private static $serverPID;
    private static $serverPort;

    private static function locateBinaries()
    {
        // Try to locate the php binary.
        if (defined('PHP_BINARY')) {
            // PHP_BINARY is only defined on PHP 5.4+.
            self::$phpBinary = PHP_BINARY;
        } else {
            self::$phpBinary = findBinary('php', true, PHP_BINDIR);
        }
        if (self::$phpBinary === null) {
            throw new \Exception('Could not locate PHP binary');
        }
    }

    private function startServer()
    {
        $options = '-n ' .
            '-d detect_unicode=0 ' .
            '-d date.timezone=UTC ' .
            '-d ' . escapeshellarg('extension_dir=' . PHP_EXTENSION_DIR);

        $extensions = array(
            'spl',
            'openssl',
            'mcrypt',
            'gmp',
            'ctype',    // Required by symfony/config
            'dom',      // Required by symfony/config
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

        // Launch pssht using the PHP binary and proper options.
        $command =
            escapeshellarg(self::$phpBinary) .
            " $options " .
            escapeshellarg(
                dirname(dirname(__DIR__)) .
                DIRECTORY_SEPARATOR . 'bin' .
                DIRECTORY_SEPARATOR . 'pssht'
            ) . ' ' .
            escapeshellarg($this->configFile) . ' 2>&1';
        self::$serverProcess = popen($command, 'r');
        if (self::$serverProcess === false) {
            throw new \Exception('Could not start the test server using ' .
                                 'this command line: ' . $command);
        }

        // Grab the server's PID.
        $init = fgets(self::$serverProcess, 1024);
        if ($init === false) {
            throw new \Exception("Could not read the server's PID");
        }
        $init   = rtrim($init);
        $msg    = 'pssht is starting';
        if (strncmp($init, $msg, strlen($msg) - 1)) {
            throw new \Exception(
                'Unexpected content: ' .
                addcslashes($init, "\x00..\x1F\x7F..\xFF")
            );
        }
        // "pssht is starting (PID ...)"
        self::$serverPID = (int) substr($init, strrpos($init, '(') + 5, -1);
        if (self::$serverPID === 0) {
            throw new \Exception("Could not read the server's PID");
        }

        // Grab the port assigned to the server.
        $init = fgets(self::$serverProcess, 1024);
        if ($init === false) {
            throw new \Exception("Could not read the server's port");
        }
        $init   = rtrim($init);
        $msg    = 'Listening for new connections on ';
        if (strncmp($init, $msg, strlen($msg) - 1)) {
            throw new \Exception(
                'Unexpected content: ' .
                addcslashes($init, "\x00..\x1F\x7F..\xFF")
            );
        }
        // "Listening for new connections on ...:..." (address:port)
        self::$serverPort = (int) substr($init, strrpos($init, ':') + 1);
        if (self::$serverPort === 0) {
            throw new \Exception("Could not read the server's port");
        }

        $logging = \Plop\Plop::getInstance();
        $logging->info('Test server running on port %d (PID %d)',
                        array(self::$serverPort, self::$serverPID));
    }

    final public function setUp()
    {
        // Let subclasses do their own setup.
        $this->setUp2();

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
                DIRECTORY_SEPARATOR . 'known_hosts' .
                DIRECTORY_SEPARATOR . 'rsa';
        }

        self::locateBinaries();

        // Start pssht for real and initialize the algorithms.
        if (self::$serverPID === null) {
            $this->startServer();
            \fpoirotte\Pssht\Algorithms::factory();
        }


        $cls = null;
        if ($this->sshClient !== null) {
            $binary = $this->sshClient->getBinary();
            $cls = get_class($this->sshClient);
        } else {
            // Look for a usable SSH client:
            // - OpenSSH's      "ssh"
            // - PuTTY's        "plink"
            // - TortoiseGit's  "tortoiseplink"
            if (($binary = findBinary('ssh2')) !== null) {
                $cls = '\\fpoirotte\\Pssht\\Tests\\Helpers\\SshClient\\OpenSSH';
            } elseif (($binary = findBinary('plink')) !== null ||
                      ($binary = findBinary('tortoiseplink')) !== null) {
                $cls = '\\fpoirotte\\Pssht\\Tests\\Helpers\\SshClient\\PuTTY';
            }
        }

        if ($cls === null) {
            return $this->markTestSkipped('No usable SSH client found');
        }

        $this->sshClient = new $cls($binary, 'localhost', null, self::$serverPort);
        $this->sshClient->setHome($this->fakeHome);

        // PuTTY stores its configuration in the registry on Windows,
        // preventing us from overriding it like we do on Linux/Unix.
        //
        // There exists modified builds that can store the configuration
        // in a separate file. We can't relay on that since these versions
        // are not official.
        if (!strncasecmp(PHP_OS, 'Win', 3) &&
            ($this->sshClient instanceof
             \fpoirotte\Pssht\Tests\Helpers\SshClient\PuTTY)) {
            return $this->markTestSkipped("Windows is not yet supported");
        }
    }

    public function setUp2()
    {
        // Subclasses can override this method instead of
        // "setUp" to implement their own logic.
    }

    final public static function tearDownAfterClass()
    {
        if (self::$serverPID !== null) {
            // Just kill the damn thing already!
            posix_kill(self::$serverPID, defined('SIGTERM') ? SIGTERM : 15);
            pclose(self::$serverProcess);
            self::$serverPID = null;
        }
        static::tearDownAfterClass2();
    }

    public static function tearDownAfterClass2()
    {
        // Subclasses can override this method instead of
        // "tearDownAfterClass" to implement their own logic.
    }
}
