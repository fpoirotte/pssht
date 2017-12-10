<?php

namespace fpoirotte\Pssht\Tests\Unit\Algorithms;

/**
 * Test vectors for ChaCha20.
 */
class ChaCha20 extends \PHPUnit\Framework\TestCase
{
    public function vectors()
    {
        return array(
            // These test vectors come from section 7 of
            // http://tools.ietf.org/html/draft-agl-tls-chacha20poly1305-04
            array(
                '0000000000000000000000000000000000000000000000000000000000000000',
                '0000000000000000',
                '76b8e0ada0f13d90405d6ae55386bd28bdd219b8a08ded1aa836efcc' .
                '8b770dc7da41597c5157488d7724e03fb8d84a376a43b8f41518a11c' .
                'c387b669b2ee6586',
            ),
            array(
                '0000000000000000000000000000000000000000000000000000000000000001',
                '0000000000000000',
                '4540f05a9f1fb296d7736e7b208e3c96eb4fe1834688d2604f450952' .
                'ed432d41bbe2a0b6ea7566d2a5d1e7e20d42af2c53d792b1c43fea81' .
                '7e9ad275ae546963',
            ),
            array(
                '0000000000000000000000000000000000000000000000000000000000000000',
                '0000000000000001',
                'de9cba7bf3d69ef5e786dc63973f653a0b49e015adbff7134fcb7df1' .
                '37821031e85a050278a7084527214f73efc7fa5b5277062eb7a0433e' .
                '445f41e3',
            ),
            array(
                '0000000000000000000000000000000000000000000000000000000000000000',
                '0100000000000000',
                'ef3fdfd6c61578fbf5cf35bd3dd33b8009631634d21e42ac33960bd1' .
                '38e50d32111e4caf237ee53ca8ad6426194a88545ddc497a0b466e7d' .
                '6bbdb0041b2f586b',
            ),
            array(
                '000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f',
                '0001020304050607',
                'f798a189f195e66982105ffb640bb7757f579da31602fc93ec01ac56' .
                'f85ac3c134a4547b733b46413042c9440049176905d3be59ea1c53f1' .
                '5916155c2be8241a38008b9a26bc35941e2444177c8ade6689de9526' .
                '4986d95889fb60e84629c9bd9a5acb1cc118be563eb9b3a4a472f82e' .
                '09a7e778492b562ef7130e88dfe031c79db9d4f7c7a899151b9a4750' .
                '32b63fc385245fe054e3dd5a97a5f576fe064025d3ce042c566ab2c5' .
                '07b138db853e3d6959660996546cc9c4a6eafdc777c040d70eaf46f7' .
                '6dad3979e5c5360c3317166a1c894c94a371876a94df7628fe4eaaf2' .
                'ccb27d5aaae0ad7ad0f9d4b6ad3b54098746d4524d38407a6deb3ab7' .
                '8fab78c9',
            ),
        );
    }

    /**
     * @dataProvider vectors
     */
    public function testChaCha20($key, $nonce, $keystream)
    {
        $key        = pack('H*', $key);
        $nonce      = pack('H*', $nonce);
        $keystream  = pack('H*', $keystream);

        $cipher     = new \fpoirotte\Pssht\Algorithms\ChaCha20($key);
        $plain      = str_repeat("\x00", strlen($keystream));
        $this->assertSame($keystream, $cipher->encrypt($plain, $nonce));
    }
}
