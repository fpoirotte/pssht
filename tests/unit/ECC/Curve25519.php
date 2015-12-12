<?php

namespace fpoirotte\Pssht\Tests\Unit\ECC;

/**
 * Test vectors for Curve 25519.
 */
class Curve25519 extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->curve = \fpoirotte\Pssht\ECC\Curve25519::getInstance();
    }

    public function testHandshake()
    {
        // These test vectors were taken from annex A.2 of
        // https://tools.ietf.org/html/draft-josefsson-tls-curve25519-06

        // The secret keys in that document have been pre-converted
        // into large little-endian integers, but we want raw input instead.
        $S_a = strrev(pack('H*', '6A2CB91DA5FB77B12A99C0EB872F4CDF4566B25172C1163C7DA518730A6D0770'));
        $S_b = strrev(pack('H*', '6BE088FF278B2F1CFDB6182629B13B6FE60E80838B7FE1794B8A4A627E08AB58'));
        $P_a = pack('H*', '8520F0098930A754748B7DDCB43EF75A0DBF3A0D26381AF4EBA4A98EAA9B4E6A');
        $P_b = pack('H*', 'DE9EDB7D7B7DC1B4D35B61C2ECE435373F8343C85B78674DADFC7E146F882B4F');
        $SS  = pack('H*', '4A5D9D5BA4CE2DE1728E3BF480350F25E07E21C947D19E3376F09B3C1E161742');

        // Public key computation.
        $this->assertSame($P_a, $this->curve->getPublic($S_a));
        $this->assertSame($P_b, $this->curve->getPublic($S_b));

        // Shared secret computation.
        $this->assertSame($SS, $this->curve->getShared($S_a, $P_b));
        $this->assertSame($SS, $this->curve->getShared($S_b, $P_a));
    }

    public function testHandshake2()
    {
        // These test vectors were taken from annex A of
        // https://tools.ietf.org/html/draft-josefsson-tls-curve25519-02

        // The values in that document use little-endian representation.
        $d_A = strrev(pack('H*', '5AC99F33632E5A768DE7E81BF854C27C46E3FBF2ABBACD29EC4AFF517369C660'));
        $d_B = strrev(pack('H*', '47DC3D214174820E1154B49BC6CDB2ABD45EE95817055D255AA35831B70D3260'));
        $x_A = strrev(pack('H*', '057E23EA9F1CBE8A27168F6E696A791DE61DD3AF7ACD4EEACC6E7BA514FDA863'));
        $x_B = strrev(pack('H*', '6EB89DA91989AE37C7EAC7618D9E5C4951DBA1D73C285AE1CD26A855020EEF04'));
        $x_S = strrev(pack('H*', '61450CD98E36016B58776A897A9F0AEF738B99F09468B8D6B8511184D53494AB'));

        // Public key computation.
        $this->assertSame($x_A, $this->curve->getPublic($d_A));
        $this->assertSame($x_B, $this->curve->getPublic($d_B));

        // Shared secret computation.
        $this->assertSame($x_S, $this->curve->getShared($d_A, $x_B));
        $this->assertSame($x_S, $this->curve->getShared($d_B, $x_A));
    }
}
