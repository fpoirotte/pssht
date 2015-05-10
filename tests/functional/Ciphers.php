<?php

namespace fpoirotte\Pssht\Tests\Functional;

/**
 * Test connection using specific ciphers.
 */
class Ciphers extends \fpoirotte\Pssht\Tests\Helpers\AbstractConnectionTest
{
    public function provideCipher()
    {
        $algos      = array();
        $algos[]    = '3des-cbc';
        $algos[]    = 'aes128-cbc';
        $algos[]    = 'aes192-cbc';
        $algos[]    = 'aes256-cbc';
        $algos[]    = 'aes128-ctr';
        $algos[]    = 'aes192-ctr';
        $algos[]    = 'aes256-ctr';
        $algos[]    = 'arcfour';
        $algos[]    = 'arcfour128';
        $algos[]    = 'arcfour256';
        $algos[]    = 'blowfish-cbc';
        $algos[]    = 'cast128-cbc';

        switch (get_class($this->sshClient)) {
            case '\\fpoirotte\\Pssht\\Tests\\Helpers\\SshClient\\OpenSSH':
                $algos[] = 'aes128-gcm@openssh.com';
                $algos[] = 'aes256-gcm@openssh.com';
                $algos[] = 'chacha20-poly1305@openssh.com';
                break;
        }

        $res = array();
        foreach ($algos as $algo) {
            $res[] = array($algo);
        }
        return $res;
    }

    /**
     * @dataProvider    provideCipher
     */
    public function testCiphers($cipher)
    {
        // Skip algorithms which are not currently available.
        $algos = \fpoirotte\Pssht\Algorithms::factory();
        if ($algos->getClass('Encryption', $cipher) === null) {
            return $this->markTestSkipped("Unsupported cipher: $cipher");
        }

        $number = rand(0, 100);
        list($exitCode, $output) = $this->sshClient
            ->setCipher($cipher)
            ->setCommand(array($number))
            ->setIdentity(
                dirname(__DIR__) .
                DIRECTORY_SEPARATOR . 'data' .
                DIRECTORY_SEPARATOR . 'plaintext' .
                DIRECTORY_SEPARATOR . 'rsa' .
                DIRECTORY_SEPARATOR . '4096'
            )
            ->run();

        $this->assertSame(
            $number,
            $exitCode,
            "Wrong exit code ($exitCode). Output: " . print_r($output, true)
        );
        $this->assertSame(1, count($output));
        $this->assertSame("Your number: $number", $output[0]);
    }
}
