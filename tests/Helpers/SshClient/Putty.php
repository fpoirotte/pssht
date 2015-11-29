<?php

namespace fpoirotte\Pssht\Tests\Helpers\SshClient;

class Putty extends \fpoirotte\Pssht\Tests\Helpers\AbstractSshClient
{
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
            $this->phpBinary,
            'setsid',
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

        // Build the command & escape nasty stuff.
        array_push($args, $this->login . '@' . $this->host);
        $args       = array_merge($args, $this->command);
        $realArgs   = array();
        foreach ($args as $arg) {
            $realArgs[] = escapeshellarg((string) $arg);
        }

        // Build final command with redirections.
        $command = implode(' ', $realArgs);
        if ($this->identity !== null && $this->passphrase !== '') {
            $command = 'echo ' . escapeshellarg($this->passphrase) . ' | ' .
                        $command;
        }
        return $command;
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
#                    file_get_contents($knownKeys . '.tpl')
#                )
            );
        }
    }
}
