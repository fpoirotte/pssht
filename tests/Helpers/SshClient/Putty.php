<?php

namespace fpoirotte\Pssht\Tests\Helpers\SshClient;

use fpoirotte\Pssht\Tests\Helpers;

class Putty extends \fpoirotte\Pssht\Tests\Helpers\AbstractSshClient
{
    public function __construct($host, $login = null, $port = 22)
    {
        // Use either:
        // - PuTTY's        "plink"
        // - TortoiseGit's  "tortoiseplink"
        $binary = Helpers\findBinary('plink') or Helpers\findBinary('tortoiseplink');
        parent::__construct($binary, $host, $login, $port);
    }

    public function setIdentity($identity, $passphrase = '')
    {
        if (file_exists($identity . '.ppk')) {
            $identity .= '.ppk';
        }
        return parent::setIdentity($identity, $passphrase);
    }

    public function __toString()
    {
        $args = array(
            $this->binary,
            '-ssh',
            '-batch',
            '-P', $this->port,                          // Port
            ($this->X11Forwarding ? '-X' : '-x'),       // X11 forwarding
            ($this->agentForwarding ? '-A' : '-a'),     // Agent forwarding
            ($this->ptyAllocation ? '-t' : '-T'),       // Pseudo-TTY
            '-' . ($this->agent ? '' : 'no') . 'agent', // pageant
        );

        if ($this->compression) {
            $args[] = '-C';                             // Compression
        }

        /// @TODO
        if ($this->cipher) {
        }

        /// @TODO
        if ($this->mac) {
        }

        if ($this->identity) {
            array_push($args, '-i', $this->identity);   // Identity
        }

        if (!$this->shellOrCommand) {
            $args[] = '-N';
        }

        if ($this->home !== null) {
            array_push($args, '-load', 'pssht');
        }

        if ($this->password !== null) {
            array_push($args, '-pw', $this->password);
        }

        // Build the command & escape nasty stuff.
        array_push($args, $this->login . '@' . $this->host);
        $args       = array_merge($args, $this->command);
        $realArgs   = array();
        foreach ($args as $arg) {
            $realArgs[] = escapeshellarg((string) $arg);
        }

        // Build final command with redirections.
        $command = implode(' ', $realArgs);
        if ($this->passphrase !== '') {
            $command = 'echo ' . escapeshellarg($this->passphrase) . ' | ' .
                        $command;
        }
        return $command . ' 2>&1';
    }

    public function patchContext()
    {
        if ($this->home !== null) {
            $this->setEnvironment(array('HOME' => $this->home));

            // Replace <port> in sshknownKeys with the actual port.
            $knownKeys = $this->home .
                DIRECTORY_SEPARATOR . '.putty' .
                DIRECTORY_SEPARATOR . 'sshhostkeys';
            file_put_contents(
                $knownKeys,
                str_replace(
                    '<port>',
                    $this->port,
                    file_get_contents($knownKeys . '.tpl')
                )
            );

            // Replace variables in session configuration file.
            $session = $this->home .
                DIRECTORY_SEPARATOR . '.putty' .
                DIRECTORY_SEPARATOR . 'sessions' .
                DIRECTORY_SEPARATOR . 'pssht';
            file_put_contents(
                $session,
                file_get_contents($session . '.tpl')
#                str_replace(
#                    '<port>',
#                    $this->port,
#                    file_get_contents($session . '.tpl')
#                )
            );
        }
    }

    public function getSupportedCiphers()
    {
        // PuTTY's plink uses hard-coded lists.
        // Extracted from 51465fac73742602003db2c445109a3526fad16e using:
        // grep -hIrA 12 'struct ssh2_cipher' . | grep '"' | grep -v ', ' | grep -v HISTORIC
        return array(
            "aes128-ctr",
            "aes192-ctr",
            "aes256-ctr",
            "aes128-cbc",
            "aes192-cbc",
            "aes256-cbc",
            "rijndael-cbc@lysator.liu.se",
            "arcfour128",
            "arcfour256",
            "blowfish-cbc",
            "blowfish-ctr",
            "chacha20-poly1305@openssh.com",
            "3des-cbc",
            "3des-ctr",
            "des-cbc",
            "des-cbc@ssh.com",
        );
    }

    public function getSupportedMACs()
    {
        // PuTTY's plink uses hard-coded lists.
        // Extracted from 51465fac73742602003db2c445109a3526fad16e using:
        // grep -hIrA 9 'struct ssh_mac' . | grep '"' | grep ',' | sed 's/ NULL,//'
        return array(
            "hmac-md5", "hmac-md5-etm@openssh.com",
            "hmac-sha2-256", "hmac-sha2-256-etm@openssh.com",
            "hmac-sha1", "hmac-sha1-etm@openssh.com",
            "hmac-sha1-96", "hmac-sha1-96-etm@openssh.com",
            "hmac-sha1",
            "hmac-sha1-96",
        );
    }

    public function getSupportedKEXs()
    {
        // PuTTY's plink uses hard-coded lists.
        // Extracted from 51465fac73742602003db2c445109a3526fad16e using:
        // grep -hIrA 12 'struct ssh_kex' . | grep '"' | awk '{print $1}'
        return array(
            "diffie-hellman-group1-sha1",
            "diffie-hellman-group14-sha1",
            "diffie-hellman-group-exchange-sha256",
            "diffie-hellman-group-exchange-sha1",
            "curve25519-sha256@libssh.org",
            "ecdh-sha2-nistp256",
            "ecdh-sha2-nistp384",
            "ecdh-sha2-nistp521",
            "rsa1024-sha1",
            "rsa2048-sha256",
        );
    }

    public function getSupportedKeys()
    {
        // PuTTY's plink uses hard-coded lists.
        // Extracted from 51465fac73742602003db2c445109a3526fad16e using:
        // grep -hIrA 13 'struct ssh_signkey ssh' . | grep '"'
        return array(
            "ssh-dss",
            "ssh-ed25519",
            "ecdsa-sha2-nistp256",
            "ecdsa-sha2-nistp384",
            "ecdsa-sha2-nistp521",
            "ssh-rsa",
        );
    }
}
