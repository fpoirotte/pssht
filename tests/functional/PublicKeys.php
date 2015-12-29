<?php

namespace fpoirotte\Pssht\Tests\Functional;

/**
 * Test connection using specific public keys.
 */
class PublicKeys extends \fpoirotte\Pssht\Tests\Helpers\AbstractConnectionTest
{
    public function provideAlgorithms()
    {
        $this->initClient();
        $res = array();

        // Keep only algorithms which are supported
        // by both the SSH client and Pssht.
        $algos = \fpoirotte\Pssht\Algorithms::factory();
        foreach ($this->sshClient->getSupportedKeys() as $algo) {
            if ($algos->getClass('Key', $algo) === null) {
                continue;
            }

            $base = dirname(__DIR__) .
                    DIRECTORY_SEPARATOR . 'data' .
                    DIRECTORY_SEPARATOR . 'plaintext' .
                    DIRECTORY_SEPARATOR . $algo;

            $it = new \GlobIterator(
                $base . DIRECTORY_SEPARATOR . '*.pub',
                \FilesystemIterator::KEY_AS_PATHNAME |
                \FilesystemIterator::SKIP_DOTS
            );
            foreach ($it as $key => $value) {
                $res[] = array(substr($key, 0, -4));
            }
        }

        return $res;
    }

    /**
     * @dataProvider    provideAlgorithms
     */
    public function testPublicKeys($identity)
    {
        $number = rand(0, 100);
        $client = $this->sshClient
            ->setCommand(array($number))
            ->setIdentity($identity);
        list($exitCode, $output) = $this->runClient($client);
        $this->assertSame("Your number: $number" . PHP_EOL, $output);
        $this->assertSame(
            $number,
            $exitCode,
            "Wrong exit code ($exitCode)."
        );
    }
}
