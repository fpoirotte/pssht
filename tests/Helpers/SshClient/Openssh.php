<?php

namespace fpoirotte\Pssht\Tests\Helpers\SshClient;

use fpoirotte\Pssht\Tests\Helpers;

class Openssh extends \fpoirotte\Pssht\Tests\Helpers\AbstractSshClient
{
    public function __construct($host, $login = null, $port = 22)
    {
        // Use OpenSSH's "ssh" binary
        parent::__construct(Helpers\findBinary('ssh'), $host, $login, $port);
    }

    public function __toString()
    {
        $null = strncasecmp(PHP_OS, 'Win', 3) ? '/dev/null' : 'NUL';

        $args = array(
            'setsid',
            $this->binary,
            '-F', $null,                                // No config. file
            '-p', $this->port,                          // Port
            ($this->X11Forwarding ? '-X' : '-x'),       // X11 forwarding
            ($this->agentForwarding ? '-A' : '-a'),     // Agent forwarding
            ($this->ptyAllocation ? '-t' : '-T'),       // Pseudo-TTY
        );

        if ($this->compression) {
            $args[] = '-C';                             // Compression
        }

        if ($this->cipher) {
            array_push($args, '-c', $this->cipher);     // Cipher
        }

        if ($this->mac) {
            array_push($args, '-m', $this->mac);        // MAC
        }

        if ($this->identity) {
            array_push($args, '-i', $this->identity);   // Identity
        }

        if (!$this->shellOrCommand) {
            $args[] = '-N';
        }

        if ($this->home !== null) {
            array_push(
                $args,
                '-o',
                'UserKnownHostsFile=' . $this->home .
                    DIRECTORY_SEPARATOR . '.ssh' .
                    DIRECTORY_SEPARATOR . 'known_hosts'
            );
        }

        // Build the command & escape nasty stuff.
        array_push($args, $this->login . '@' . $this->host);
        $args       = array_merge($args, $this->command);
        $realArgs   = array();
        foreach ($args as $arg) {
            $realArgs[] = escapeshellarg((string) $arg);
        }

        // Build final command with redirections.
        $command = implode(' ', $realArgs) . ' < ' . $null . ' 2>&1';
        return $command;
    }

    public function patchContext()
    {
        if ($this->home !== null) {
            $this->setEnvironment(array('HOME' => $this->home));

            // Replace <port> in known_hosts with the actual port.
            $knownKeys = $this->home .
                DIRECTORY_SEPARATOR . '.ssh' .
                DIRECTORY_SEPARATOR . 'known_hosts';
            file_put_contents(
                $knownKeys,
                str_replace(
                    '<port>',
                    $this->port,
                    file_get_contents($knownKeys . '.tpl')
                )
            );
        }

        if (!$this->agent) {
            // Don't use ssh-agent.
            $this->setEnvironment(
                array(
                    'SSH_AUTH_SOCK' => null,
                    'SSH_AGENT_PID' => null,
                )
            );
        }

        // Abuses SSH_ASKPASS to feed passwords directly into OpenSSH.
        // See http://andre.frimberger.de/index.php/linux/reading-ssh-password-from-stdin-the-openssh-5-6p1-compatible-way/
        $this->setEnvironment(
            array(
                'DISPLAY' => '0.0.0.0:0',
                'SSH_ASKPASS_PASSWORD' =>
                    $this->identity === null
                        ? $this->password
                        : $this->passphrase,
                'SSH_ASKPASS' => dirname(dirname(__DIR__)) .
                    DIRECTORY_SEPARATOR . 'data' .
                    DIRECTORY_SEPARATOR . 'askpass.sh',
            )
        );
    }

    protected function isOldVersion()
    {
        exec(escapeshellarg($this->binary) . ' -V 2>&1', $output, $exitCode);
        if ($exitCode !== 0) {
            throw new \Exception();
        }
        return version_compare($output[0], 'OpenSSH_6.0', '<');
    }

    public function getSupportedCiphers()
    {
        // ssh -Q XXX does not work in OpenSSH < 6.0.
        // So we use a hard-coded list for old versions (5.x).
        if ($this->isOldVersion()) {
            return array(
                'aes128-ctr',
                'aes192-ctr',
                'aes256-ctr',
                'arcfour256',
                'arcfour128',
                'aes128-cbc',
                '3des-cbc',
                'blowfish-cbc',
                'cast128-cbc',
                'aes192-cbc',
                'aes256-cbc',
                'arcfour',
            );
        }

        exec(escapeshellarg($this->binary) . ' -Q cipher', $output, $exitCode);
        if ($exitCode !== 0) {
            throw new \Exception();
        }
        return $output;
    }

    public function getSupportedMACs()
    {
        // ssh -Q XXX does not work in OpenSSH < 6.0.
        // So we use a hard-coded list for old versions (5.x).
        if ($this->isOldVersion()) {
            return array(
                'hmac-md5',
                'hmac-sha1',
                'umac-64@openssh.com',
                'hmac-ripemd160',
                'hmac-sha1-96',
                'hmac-md5-96',
                'hmac-sha2-256',
                'hmac-sha2-256-96',
                'hmac-sha2-512',
                'hmac-sha2-512-96',
            );
        }

        exec(escapeshellarg($this->binary) . ' -Q mac', $output, $exitCode);
        if ($exitCode !== 0) {
            throw new \Exception();
        }
        return $output;
    }

    public function getSupportedKEXs()
    {
        // ssh -Q XXX does not work in OpenSSH < 6.0.
        // So we use a hard-coded list for old versions (5.x).
        if ($this->isOldVersion()) {
            return array(
                'ecdh-sha2-nistp256',
                'ecdh-sha2-nistp384',
                'ecdh-sha2-nistp521',
                'diffie-hellman-group-exchange-sha256',
                'diffie-hellman-group-exchange-sha1',
                'diffie-hellman-group14-sha1',
                'diffie-hellman-group1-sha1',
            );
        }

        exec(escapeshellarg($this->binary) . ' -Q kex', $output, $exitCode);
        if ($exitCode !== 0) {
            throw new \Exception();
        }
        return $output;
    }

    public function getSupportedKeys()
    {
        // ssh -Q XXX does not work in OpenSSH < 6.0.
        // So we use a hard-coded list for old versions (5.x).
        if ($this->isOldVersion()) {
            return array(
                'ssh-rsa',
                'ssh-dss',
                'ecdsa-sha2-nistp256',
                'ecdsa-sha2-nistp384',
                'ecdsa-sha2-nistp521',
                'ssh-rsa-cert-v01@openssh.com',
                'ssh-dss-cert-v01@openssh.com',
                'ecdsa-sha2-nistp256-cert-v01@openssh.com',
                'ecdsa-sha2-nistp384-cert-v01@openssh.com',
                'ecdsa-sha2-nistp521-cert-v01@openssh.com',
                'ssh-rsa-cert-v00@openssh.com',
                'ssh-dss-cert-v00@openssh.com',
            );
        }

        exec(escapeshellarg($this->binary) . ' -Q key', $output, $exitCode);
        if ($exitCode !== 0) {
            throw new \Exception();
        }
        return $output;
    }
}
