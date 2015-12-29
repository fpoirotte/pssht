<?php

namespace fpoirotte\Pssht\Tests\Functional;

/**
 * Test connection using specific Message Authentication Codes.
 */
class MAC extends \fpoirotte\Pssht\Tests\Helpers\AbstractConnectionTest
{
    public function provideAlgorithms()
    {
        $this->initClient();
        $res = array();

        // Keep only algorithms which are supported
        // by both the SSH client and Pssht.
        $algos = \fpoirotte\Pssht\Algorithms::factory();
        foreach ($this->sshClient->getSupportedMACs() as $algo) {
            if ($algos->getClass('MAC', $algo) !== null) {
                $res[] = array($algo);
            }
        }

        return $res;
    }

    /**
     * @dataProvider    provideAlgorithms
     */
    public function testMessageAuthenticationCodes($mac)
    {
        $number = rand(0, 100);
        $client = $this->sshClient
            ->setMAC($mac)
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
