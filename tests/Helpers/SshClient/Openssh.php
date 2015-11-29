<?php

namespace fpoirotte\Pssht\Tests\Helpers\SshClient;

class Openssh extends \fpoirotte\Pssht\Tests\Helpers\AbstractSshClient
{
    public function __toString()
    {
        $null = strncasecmp(PHP_OS, 'Win', 3) ? '/dev/null' : 'NUL';

        $args = array(
            $this->phpBinary,
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
        $command = implode(' ', $realArgs) . ' < ' . $null;
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
}
