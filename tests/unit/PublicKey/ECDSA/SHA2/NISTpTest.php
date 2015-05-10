<?php

namespace fpoirotte\Pssht\Tests\Unit\PublicKey\ECDSA\SHA2;

/**
 * Test ECDSA implementation using the test vectors
 * in http://tools.ietf.org/html/rfc4754#section-8
 */
class NISTpTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        \fpoirotte\Pssht\ECC\Curve::initialize();
    }

    public function testNISTp256()
    {
        $key = new \fpoirotte\Pssht\PublicKey\ECDSA\SHA2\NISTp256(
            new \fpoirotte\Pssht\ECC\Point(
                gmp_init('2442A5CC0ECD015FA3CA31DC8E2BBC70BF42D60CBCA20085E0822CB04235E970', 16),
                gmp_init('6FC98BD7E50211A4A27102FA3549DF79EBCB4BF246B80945CDDFE7D509BBFD7D', 16)
            ),
            gmp_init('DC51D3866A15BACDE33D96F992FCA99DA7E6EF0934E7097559C27F1614C88A7F', 16)
        );
        $rng = new \fpoirotte\Pssht\Random\Fixed(
            pack('H*', '9E56F509196784D963D1C0A401510EE7ADA3DCC5DEE04B154BF61AF1D5A6DECE')
        );
        $key->setRNG($rng);

        $msg    = 'abc';
        $res    = $key->sign($msg);

        $prefix     = "\x00\x00\x00\x21\x00";
        $expR       = pack('H*', 'CB28E0999B9C7715FD0A80D8E47A77079716CBBF917DD72E97566EA1C066957C');
        $expS       = pack('H*', '86FA3BB4E26CAD5BF90B7F81899256CE7594BB1EA0C89212748BFF3B3D5B0315');
        $expected   = bin2hex($prefix . $expR . $prefix . $expS);
        $this->assertSame($expected, bin2hex($res));
        $this->assertTrue($key->check($msg, $res));
    }

    public function testNISTp384()
    {
        $key = new \fpoirotte\Pssht\PublicKey\ECDSA\SHA2\NISTp384(
            new \fpoirotte\Pssht\ECC\Point(
                gmp_init('96281BF8DD5E0525CA049C048D345D3082968D10FEDF5C5ACA0C64E6465A97EA5CE10C9DFEC21797415710721F437922', 16),
                gmp_init('447688BA94708EB6E2E4D59F6AB6D7EDFF9301D249FE49C33096655F5D502FAD3D383B91C5E7EDAA2B714CC99D5743CA', 16)
            ),
            gmp_init('0BEB646634BA87735D77AE4809A0EBEA865535DE4C1E1DCB692E84708E81A5AF62E528C38B2A81B35309668D73524D9F', 16)
        );
        $rng = new \fpoirotte\Pssht\Random\Fixed(
            pack('H*', 'B4B74E44D71A13D568003D7489908D564C7761E229C58CBFA18950096EB7463B854D7FA992F934D927376285E63414FA')
        );
        $key->setRNG($rng);

        $msg    = 'abc';
        $res    = $key->sign($msg);

        $prefix     = "\x00\x00\x00\x31\x00";
        $expR       = pack('H*', 'FB017B914E29149432D8BAC29A514640B46F53DDAB2C69948084E2930F1C8F7E08E07C9C63F2D21A07DCB56A6AF56EB3');
        $expS       = pack('H*', 'B263A1305E057F984D38726A1B46874109F417BCA112674C528262A40A629AF1CBB9F516CE0FA7D2FF630863A00E8B9F');
        $expected   = bin2hex($prefix . $expR . $prefix . $expS);
        $this->assertSame($expected, bin2hex($res));
        $this->assertTrue($key->check($msg, $res));
    }

    public function testNISTp521()
    {
        $key = new \fpoirotte\Pssht\PublicKey\ECDSA\SHA2\NISTp521(
            new \fpoirotte\Pssht\ECC\Point(
                gmp_init('0151518F1AF0F563517EDD5485190DF95A4BF57B5CBA4CF2A9A3F6474725A35F7AFE0A6DDEB8BEDBCD6A197E592D40188901CECD650699C9B5E456AEA5ADD19052A8', 16),
                gmp_init('006F3B142EA1BFFF7E2837AD44C9E4FF6D2D34C73184BBAD90026DD5E6E85317D9DF45CAD7803C6C20035B2F3FF63AFF4E1BA64D1C077577DA3F4286C58F0AEAE643', 16)
            ),
            gmp_init('0065FDA3409451DCAB0A0EAD45495112A3D813C17BFD34BDF8C1209D7DF5849120597779060A7FF9D704ADF78B570FFAD6F062E95C7E0C5D5481C5B153B48B375FA1', 16)
        );
        $rng = new \fpoirotte\Pssht\Random\Fixed(
            pack('H*', '00C1C2B305419F5A41344D7E4359933D734096F556197A9B244342B8B62F46F9373778F9DE6B6497B1EF825FF24F42F9B4A4BD7382CFC3378A540B1B7F0C1B956C2F')
        );
        $key->setRNG($rng);

        $msg    = 'abc';
        $res    = $key->sign($msg);

        $prefix     = "\x00\x00\x00\x42";
        $expR       = pack('H*', '0154FD3836AF92D0DCA57DD5341D3053988534FDE8318FC6AAAAB68E2E6F4339B19F2F281A7E0B22C269D93CF8794A9278880ED7DBB8D9362CAEACEE544320552251');
        $expS       = pack('H*', '017705A7030290D1CEB605A9A1BB03FF9CDD521E87A696EC926C8C10C8362DF4975367101F67D1CF9BCCBF2F3D239534FA509E70AAC851AE01AAC68D62F866472660');
        $expected   = bin2hex($prefix . $expR . $prefix . $expS);
        $this->assertSame($expected, bin2hex($res));
        $this->assertTrue($key->check($msg, $res));
    }
}
