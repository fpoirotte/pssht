<?php

namespace fpoirotte\Pssht\Tests\Functional;

/**
 * Test connection using specific ciphers.
 */
class Ciphers extends \fpoirotte\Pssht\Tests\Helpers\AbstractConnectionTest
{
    public function provideAlgorithms()
    {
        $this->initClient();
        $res = array();

        // Keep only algorithms which are supported
        // by both the SSH client and Pssht.
        $algos = \fpoirotte\Pssht\Algorithms::factory();
        foreach ($this->sshClient->getSupportedCiphers() as $algo) {
            if ($algos->getClass('Encryption', $algo) !== null) {
                $res[] = array($algo);
            }
        }

        return $res;
    }

    /**
     * @dataProvider    provideAlgorithms
     */
    public function testCiphers($cipher)
    {
        $number = rand(0, 100);
        $client = $this->sshClient
            ->setCipher($cipher)
            ->setCommand(array($number))
            ->setIdentity(
                dirname(__DIR__) .
                DIRECTORY_SEPARATOR . 'data' .
                DIRECTORY_SEPARATOR . 'plaintext' .
                DIRECTORY_SEPARATOR . 'ssh-rsa' .
                DIRECTORY_SEPARATOR . '4096'
            );
        list($exitCode, $output) = $this->runClient($client);
        $this->assertSame("Your number: $number" . PHP_EOL, $output);
        $this->assertSame(
            $number,
            $exitCode,
            "Wrong exit code ($exitCode)."
        );
    }
}
