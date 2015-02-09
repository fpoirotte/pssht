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

            // Test vector from section 2.5.1 of
            // https://tools.ietf.org/html/draft-nir-cfrg-chacha20-poly1305-06
            array(
                '43727970746f6772617068696320466f72756d2052657365617263682047726f7570',
                '85d6be7857556d337f4452fe42d506a80103808afb0db2fd4abff6af4149f51b',
                'a8061dc1305136c6c22b8baf0c0127a9',
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
