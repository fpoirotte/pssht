<?php

namespace fpoirotte\Pssht\Tests\Unit\Key;

class ED25519TestHelper extends \fpoirotte\Pssht\Key\SSH\ED25519
{
    public function __construct($sk, $pk)
    {
        parent::__construct($sk, $pk);
    }
}

/**
 * Test our EdDSA implementation using the test vectors
 * from http://ed25519.cr.yp.to/python/sign.input
 */
class ED25519Test extends \PHPUnit_Framework_TestCase
{
    public function vectors()
    {
        $lines = file(
            dirname(dirname(__DIR__)) .
            DIRECTORY_SEPARATOR . 'data' .
            DIRECTORY_SEPARATOR . 'ed25519.vectors'
        );

        $vectors = array();
        foreach ($lines as $index => $line) {
            $fields = explode(':', $line);
            $vectors[] = array(
                $index + 1,
                // Private key
                substr($fields[0], 0, 64),
                // Public key
                $fields[1],
                // Message
                $fields[2],
                // Signature
                substr($fields[3], 0, 128),
            );
        }

        // We only test a random set of vectors
        // to minimize the wallclock time spent.
        return array_intersect_key($vectors, array_flip(array_rand($vectors, 10)));
    }

    /**
     * @dataProvider    vectors
     * @group           medium
     */
    public function testED25519($index, $private, $public, $message, $signature)
    {
        $logging = \Plop\Plop::getInstance();
        $logging->debug("Testing using vector #%d", array($index));
        $msg = pack('H*', $message);
        $key = new ED25519TestHelper(pack('H*', $public), pack('H*', $private));
        $result = $key->sign($msg);
        $this->assertSame($signature, bin2hex($result));
        $this->assertTrue($key->check($msg, $result));
    }
}
