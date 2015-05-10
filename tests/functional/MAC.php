<?php

namespace fpoirotte\Pssht\Tests\Functional;

/**
 * Test connection using specific Message Authentication Codes.
 * It uses Public Key authentication to connect.
 */
class MAC extends \fpoirotte\Pssht\Tests\Helpers\AbstractConnectionTest
{
    public function provideMAC()
    {
        $algos      = array();
        $algos[]    = 'hmac-sha2-256';
        $algos[]    = 'hmac-sha2-512';
        $algos[]    = 'hmac-md5';
        $algos[]    = 'hmac-sha1';
        $algos[]    = 'hmac-ripemd160';
        $algos[]    = 'hmac-sha1-96';
        $algos[]    = 'hmac-md5-96';

        switch (get_class($this->sshClient)) {
            case '\\fpoirotte\\Pssht\\Tests\\Helpers\\SshClient\\OpenSSH':
                $algos[] = 'umac-64-etm@openssh.com';
                $algos[] = 'umac-128-etm@openssh.com';
                $algos[] = 'hmac-sha2-256-etm@openssh.com';
                $algos[] = 'hmac-sha2-512-etm@openssh.com';
                $algos[] = 'umac-64@openssh.com';
                $algos[] = 'umac-128@openssh.com';
                $algos[] = 'hmac-md5-etm@openssh.com';
                $algos[] = 'hmac-sha1-etm@openssh.com';
                $algos[] = 'hmac-ripemd160-etm@openssh.com';
                $algos[] = 'hmac-sha1-96-etm@openssh.com';
                $algos[] = 'hmac-md5-96-etm@openssh.com';
                break;
        }

        $res = array();
        foreach ($algos as $algo) {
            $res[] = array($algo);
        }
        return $res;
    }

    /**
     * @dataProvider    provideMAC
     */
    public function testMessageAuthenticationCodes($mac)
    {
        // Skip algorithms which are not currently available.
        $algos = \fpoirotte\Pssht\Algorithms::factory();
        if ($algos->getClass('MAC', $mac) === null) {
            return $this->markTestSkipped("Unsupported MAC: $mac");
        }

        $number = rand(0, 100);
        list($exitCode, $output) = $this->sshClient
            ->setMAC($mac)
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
