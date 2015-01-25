<?php

namespace fpoirotte\Pssht\Tests;

/**
 * Test vectors for Poly1305.
 */
class Poly1305 extends \PHPUnit_Framework_TestCase
{
    public function vectors()
    {
        return array(
            // These test vectors come from section 7 of
            // http://tools.ietf.org/html/draft-agl-tls-chacha20poly1305-04
            array(
                '0000000000000000000000000000000000000000000000000000000000000000',
                '746869732069732033322d62797465206b657920666f7220506f6c7931333035',
                '49ec78090e481ec6c26b33b91ccc0307',
            ),
            array(
                '48656c6c6f20776f726c6421',
                '746869732069732033322d62797465206b657920666f7220506f6c7931333035',
                'a6f745008f81c916a20dcc74eef2b2f0',
            ),
        );
    }

    /**
     * @dataProvider vectors
     */
    public function testPoly1305($input, $key, $tag)
    {
        $input  = pack('H*', $input);
        $key    = pack('H*', $key);
        $tag    = pack('H*', $tag);

        $mac = new \fpoirotte\Pssht\Poly1305($key);
        $this->assertSame($tag, $mac->mac($input));
    }
}
