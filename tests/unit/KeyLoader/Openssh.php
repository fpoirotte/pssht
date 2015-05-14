<?php

namespace fpoirotte\Pssht\Tests\Unit\KeyLoader;

class OpensshTest extends \PHPUnit_Framework_TestCase
{
    public function provideOID()
    {
        return array(
            array('.1.3', "\x2B"),
            array('.2.1', "\x51"),
            array(
                '.1.3.6.1.4.1.311.21.20',
                "\x2B\x06\x01\x04\x01\x82\x37\x15\x14",
            ),
            array(
                // Note: 43690d = 1010101010101010b.
                '.1.3.6.1.4.1.43690.21.20',
                "\x2B\x06\x01\x04\x01\x82\xd5\x2a\x15\x14",
            ),
        );
    }

    /**
     * @dataProvider provideOID
     */
    public function testOidEncoding($oid, $asn1)
    {
        $res = \fpoirotte\Pssht\KeyLoader\Openssh::encodeOID($oid);
        $this->assertSame($asn1, $res, bin2hex($res) . ' != ' . bin2hex($asn1));
    }
}
