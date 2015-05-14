<?php

namespace fpoirotte\Pssht\Tests\Functional;

/**
 * Test connection using specific public keys.
 */
class PublicKeys extends \fpoirotte\Pssht\Tests\Helpers\AbstractConnectionTest
{
    public function providePublicKeys()
    {
        $algos      = array();
        $algos[]    = 'dsa/1024';
        $algos[]    = 'rsa/1024';
        $algos[]    = 'rsa/2048';
        $algos[]    = 'rsa/4096';
        $algos[]    = 'rsa/8192';
        $algos[]    = 'rsa/16384';

        switch (get_class($this->sshClient)) {
            case '\\fpoirotte\\Pssht\\Tests\\Helpers\\SshClient\\OpenSSH':
                $algos[]    = 'ecdsa/256';
                $algos[]    = 'ecdsa/384';
                $algos[]    = 'ecdsa/521';
                $algos[]    = 'ed25519/256';
                break;
        }

        $res = array();
        foreach ($algos as $algo) {
            $res[] = array($algo);
        }
        return $res;
    }

    /**
     * @dataProvider    providePublicKeys
     */
    public function testPublicKeys($key)
    {
        // Skip algorithms which are not currently available.
        $algos = \fpoirotte\Pssht\Algorithms::factory();

        $number = rand(0, 100);
        list($exitCode, $output) = $this->sshClient
            ->setCommand(array($number))
            ->setIdentity(
                dirname(__DIR__) .
                DIRECTORY_SEPARATOR . 'data' .
                DIRECTORY_SEPARATOR . 'plaintext' .
                DIRECTORY_SEPARATOR .
                str_replace('/', DIRECTORY_SEPARATOR, $key)
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
