<?php

namespace fpoirotte\Pssht\Tests\Helpers;

abstract class AbstractSshClient
{
    protected $binary;
    protected $home;
    protected $oldEnvironment;

    protected $login;
    protected $host;
    protected $port;
    protected $command;
    protected $cipher;
    protected $mac;
    protected $compression;
    protected $identity;
    protected $passphrase;
    protected $password;
    protected $X11Forwarding;
    protected $agentForwarding;
    protected $ptyAllocation;
    protected $agent;
    protected $shellOrCommand;

    public function __construct($binary, $host, $login = null, $port = 22)
    {
        if ($login === null) {
            $login = getenv('USER');
            if (extension_loaded('posix')) {
                $entry = posix_getpwuid(posix_geteuid());
                $login = $entry['name'];
            }
        }

        if (!is_string($binary)) {
            throw new \InvalidArgumentException('Bad binary');
        }

        if (!is_string($host)) {
            throw new \InvalidArgumentException('Bad host');
        }

        if (!is_string($login)) {
            throw new \InvalidArgumentException('Bad login');
        }

        if (!is_int($port)) {
            throw new \InvalidArgumentException(
                'Bad port: ' . print_r($port, true)
            );
        }

        $this->binary           = $binary;
        $this->home             = null;
        $this->host             = $host;
        $this->login            = $login;
        $this->port             = $port;
        $this->command          = array();
        $this->cipher           = null;
        $this->mac              = null;
        $this->compression      = false;
        $this->identity         = null;
        $this->password         = null;
        $this->passphrase       = '';
        $this->X11Forwarding    = false;
        $this->agentForwarding  = false;
        $this->ptyAllocation    = false;
        $this->agent            = false;
        $this->shellOrCommand   = true;
        $this->oldEnvironment   = array();
    }

    public function getBinary()
    {
        return $this->binary;
    }

    public function setBinary($binary)
    {
        $this->binary = $binary;
        return $this;
    }

    public function getHome()
    {
        return $this->home;
    }

    public function setHome($home)
    {
        $this->home = $home;
        return $this;
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function setLogin($login)
    {
        $this->login = $login;
        return $this;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function setCommand(array $command)
    {
        $this->command = $command;
        return $this;
    }

    public function getCipher()
    {
        return $this->cipher;
    }

    public function setCipher($cipher)
    {
        $this->cipher = $cipher;
        return $this;
    }

    public function getMAC()
    {
        return $this->mac;
    }

    public function setMAC($mac)
    {
        $this->mac = $mac;
        return $this;
    }

    public function compresses()
    {
        return $this->compression;
    }

    public function compress($enable = true)
    {
        $this->compression = $enable;
        return $this;
    }

    public function getIdentity()
    {
        return $this->identity;
    }

    public function setIdentity($identity, $passphrase = '')
    {
        $this->identity     = $identity;
        $this->passphrase   = $passphrase;
        $this->password     = null;
        return $this;
    }

    public function setPassword($password)
    {
        $this->password = $password;
        $this->identity = null;
        return $this;
    }

    public function forwardsX11()
    {
        return $this->X11Forwarding;
    }

    public function forwardX11($enable = true)
    {
        $this->X11Forwarding = $enable;
        return $this;
    }

    public function forwardsAgent()
    {
        return $this->agentForwarding;
    }

    public function forwardAgent($enable = true)
    {
        $this->agentForwarding = $enable;
        return $this;
    }

    public function allocatesPTY()
    {
        return $this->ptyAllocation;
    }

    public function allocatePTY($enable = true)
    {
        $this->ptyAllocation = $enable;
        return $this;
    }

    public function usesAgent()
    {
        return $this->agent;
    }

    public function useAgent($enable = true)
    {
        $this->agent = $enable;
        return $this;
    }

    public function usesShellOrCommand()
    {
        return $this->shellOrCommand;
    }

    public function useShellOrCommand($enable = false)
    {
        $this->shellOrCommand = $enable;
        if (!$enable) {
            $this->command = array();
        }
        return $this;
    }

    abstract public function __toString();

    public function patchContext()
    {
    }

    public function restoreContext()
    {
    }

    protected function setEnvironment(array $env)
    {
        foreach ($env as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            if (!array_key_exists($key, $this->oldEnvironment)) {
                $this->oldEnvironment[$key] = $value;
            }

            if ($value === null) {
                putenv($key);
                unset($_ENV[$key]);
            } else {
                putenv("$key=$value");
                $_ENV[$key] = (string) $value;
            }
        }
    }

    final public function run()
    {
        $output     = array();
        $exitCode   = null;
        $command    = 'ERROR';
        $e          = null;

        $logging    = \Plop\Plop::getInstance();;
        $_ENV       = array();
        $keep       = array(
            'PATH', 'Path', 'USER', 'TERM', 'SHELL',
            'PWD', 'LANG', 'LANGUAGE', 'LC_ALL'
        );

        try {
            // Prepare the context (environment variables, files, etc.).
            $this->oldEnvironment = array();
            foreach ($_SERVER as $key => $value) {
                if (is_string($value)) {
                    if (!in_array($key, $keep)) {
                        $this->setEnvironment(array($key => null));
                    } else {
                        $this->setEnvironment(array($key => $value));
                    }
                }
            }
            $this->patchContext();

            // Execute the command.
            $command = (string) $this;
            $logging->debug('Executing: %s', array($command));
            $logging->debug('Environment: %s', array(var_export($_ENV, true)));
            $process = exec($command, $output, $exitCode);
            $logging->debug(
                "Exit code: %(code)d - Output:\n%(output)s",
                array('code' => $exitCode, 'output' => var_export($output, true))
            );
        } catch (\Exception $e) {
        }

        // Poor man's "finally" to restore context.
        $oldEnvironment = $this->oldEnvironment;
        $this->setEnvironment($oldEnvironment);
        $this->oldEnvironment = $oldEnvironment;
        $this->restoreContext();
        $this->oldEnvironment = array();

        if ($e) {
            throw $e;
        }

        if (!is_string($process)) {
            throw new \Exception('Could not execute command: ' . $command);
        }
        return array($exitCode, $output);
    }
}

