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
            throw new \InvalidArgumentException('Bad port');
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

    final public function getBinary()
    {
        return $this->binary;
    }

    final public function setBinary($binary)
    {
        $this->binary = $binary;
        return $this;
    }

    final public function getHome()
    {
        return $this->home;
    }

    final public function setHome($home)
    {
        $this->home = $home;
        return $this;
    }

    final public function getLogin()
    {
        return $this->login;
    }

    final public function setLogin($login)
    {
        $this->login = $login;
        return $this;
    }

    final public function getHost()
    {
        return $this->host;
    }

    final public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    final public function getCommand()
    {
        return $this->command;
    }

    final public function setCommand(array $command)
    {
        $this->command = $command;
        return $this;
    }

    final public function getCipher()
    {
        return $this->cipher;
    }

    final public function setCipher($cipher)
    {
        $this->cipher = $cipher;
        return $this;
    }

    final public function getMAC()
    {
        return $this->mac;
    }

    final public function setMAC($mac)
    {
        $this->mac = $mac;
        return $this;
    }

    final public function compresses()
    {
        return $this->compression;
    }

    final public function compress($enable = true)
    {
        $this->compression = $enable;
        return $this;
    }

    final public function getIdentity()
    {
        return $this->identity;
    }

    final public function setIdentity($identity, $passphrase = '')
    {
        $this->identity     = $identity;
        $this->passphrase   = $passphrase;
        $this->password     = null;
        return $this;
    }

    final public function setPassword($password)
    {
        $this->password = $password;
        $this->identity = null;
        return $this;
    }

    final public function forwardsX11()
    {
        return $this->X11Forwarding;
    }

    final public function forwardX11($enable = true)
    {
        $this->X11Forwarding = $enable;
        return $this;
    }

    final public function forwardsAgent()
    {
        return $this->agentForwarding;
    }

    final public function forwardAgent($enable = true)
    {
        $this->agentForwarding = $enable;
        return $this;
    }

    final public function allocatesPTY()
    {
        return $this->ptyAllocation;
    }

    final public function allocatePTY($enable = true)
    {
        $this->ptyAllocation = $enable;
        return $this;
    }

    final public function usesAgent()
    {
        return $this->agent;
    }

    final public function useAgent($enable = true)
    {
        $this->agent = $enable;
        return $this;
    }

    final public function usesShellOrCommand()
    {
        return $this->shellOrCommand;
    }

    final public function useShellOrCommand($enable = false)
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
            } else {
                putenv("$key=$value");
            }
        }
    }

    final public function run()
    {
        $output     = array();
        $exitCode   = null;
        $command    = 'ERROR';
        $e          = null;

        try {
            // Prepare the context (environment variables, files, etc.).
            $this->oldEnvironment = array();
            $this->patchContext();
            $command = (string) $this;
            $process = exec($command, $output, $exitCode);
        } catch (\Exception $e) {
        }

        // Poor man's "finally".
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

