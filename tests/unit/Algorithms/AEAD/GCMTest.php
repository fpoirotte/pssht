<?php

namespace fpoirotte\Pssht\Tests\Unit\Algorithms\AEAD;

/**
 * Test point addition for various NIST curves
 * using the test vectors at http://point-at-infinity.org/ecc/nisttv
 */
class GCMTest extends \PHPUnit\Framework\TestCase
{
    static $cache;

    public static function setUpBeforeClass()
    {
        self::$cache = array();
    }

    public function getGCM($key)
    {
        if (!isset(self::$cache[$key])) {
            self::$cache[$key] = new \fpoirotte\Pssht\Algorithms\AEAD\GCM(
                MCRYPT_RIJNDAEL_128,
                pack('H*', $key),
                128
            );
        }
        return self::$cache[$key];
    }

    public function vectors()
    {
        /// @TODO vectors from http://www.ieee802.org/1/files/public/docs2011/bn-randall-test-vectors-0511-v1.pdf

        // K, P, A, IV, C, T
        return array(
            // Test cases from The Galois/Counter Mode of Operation (GCM)
            // http://csrc.nist.gov/groups/ST/toolkit/BCM/documents/proposedmodes/gcm/gcm-spec.pdf
            // Test case #1
            array(
                '00000000000000000000000000000000',
                '',
                '',
                '000000000000000000000000',
                '',
                '58e2fccefa7e3061367f1d57a4e7455a',
            ),

            // Test case #2
            array(
                '00000000000000000000000000000000',
                '00000000000000000000000000000000',
                '',
                '000000000000000000000000',
                '0388dace60b6a392f328c2b971b2fe78',
                'ab6e47d42cec13bdf53a67b21257bddf',
            ),

            // Test case #3
            array(
                'feffe9928665731c6d6a8f9467308308',
                'd9313225f88406e5a55909c5aff5269a' .
                '86a7a9531534f7da2e4c303d8a318a72' .
                '1c3c0c95956809532fcf0e2449a6b525' .
                'b16aedf5aa0de657ba637b391aafd255',
                '',
                'cafebabefacedbaddecaf888',
                '42831ec2217774244b7221b784d0d49c' .
                'e3aa212f2c02a4e035c17e2329aca12e' .
                '21d514b25466931c7d8f6a5aac84aa05' .
                '1ba30b396a0aac973d58e091473f5985',
                '4d5c2af327cd64a62cf35abd2ba6fab4',
            ),

            // Test case #4
            array(
                'feffe9928665731c6d6a8f9467308308',
                'd9313225f88406e5a55909c5aff5269a' .
                '86a7a9531534f7da2e4c303d8a318a72' .
                '1c3c0c95956809532fcf0e2449a6b525' .
                'b16aedf5aa0de657ba637b39',
                'feedfacedeadbeeffeedfacedeadbeef' .
                'abaddad2',
                'cafebabefacedbaddecaf888',
                '42831ec2217774244b7221b784d0d49c' .
                'e3aa212f2c02a4e035c17e2329aca12e' .
                '21d514b25466931c7d8f6a5aac84aa05' .
                '1ba30b396a0aac973d58e091',
                '5bc94fbc3221a5db94fae95ae7121a47',
            ),

            // Test case 5
            array(
                'feffe9928665731c6d6a8f9467308308',
                'd9313225f88406e5a55909c5aff5269a' .
                '86a7a9531534f7da2e4c303d8a318a72' .
                '1c3c0c95956809532fcf0e2449a6b525' .
                'b16aedf5aa0de657ba637b39',
                'feedfacedeadbeeffeedfacedeadbeef' .
                'abaddad2',
                'cafebabefacedbad',
                '61353b4c2806934a777ff51fa22a4755' .
                '699b2a714fcdc6f83766e5f97b6c7423' .
                '73806900e49f24b22b097544d4896b42' .
                '4989b5e1ebac0f07c23f4598',
                '3612d2e79e3b0785561be14aaca2fccb',
            ),

            // Test case #6
            array(
                'feffe9928665731c6d6a8f9467308308',
                'd9313225f88406e5a55909c5aff5269a' .
                '86a7a9531534f7da2e4c303d8a318a72' .
                '1c3c0c95956809532fcf0e2449a6b525' .
                'b16aedf5aa0de657ba637b39',
                'feedfacedeadbeeffeedfacedeadbeef' .
                'abaddad2',
                '9313225df88406e555909c5aff5269aa' .
                '6a7a9538534f7da1e4c303d2a318a728' .
                'c3c0c95156809539fcf0e2429a6b5254' .
                '16aedbf5a0de6a57a637b39b',
                '8ce24998625615b603a033aca13fb894' .
                'be9112a5c3a211a8ba262a3cca7e2ca7' .
                '01e4a9a4fba43c90ccdcb281d48c7c6f' .
                'd62875d2aca417034c34aee5',
                '619cc5aefffe0bfa462af43c1699d050',
            ),

            // Test case #7
            array(
                '00000000000000000000000000000000' .
                '0000000000000000',
                '',
                '',
                '000000000000000000000000',
                '',
                'cd33b28ac773f74ba00ed1f312572435',
            ),

            // Test case #8
            array(
                '00000000000000000000000000000000' .
                '0000000000000000',
                '00000000000000000000000000000000',
                '',
                '000000000000000000000000',
                '98e7247c07f0fe411c267e4384b0f600',
                '2ff58d80033927ab8ef4d4587514f0fb',
            ),

            // Test case #9
            array(
                'feffe9928665731c6d6a8f9467308308' .
                'feffe9928665731c',
                'd9313225f88406e5a55909c5aff5269a' .
                '86a7a9531534f7da2e4c303d8a318a72' .
                '1c3c0c95956809532fcf0e2449a6b525' .
                'b16aedf5aa0de657ba637b391aafd255',
                '',
                'cafebabefacedbaddecaf888',
                '3980ca0b3c00e841eb06fac4872a2757' .
                '859e1ceaa6efd984628593b40ca1e19c' .
                '7d773d00c144c525ac619d18c84a3f47' .
                '18e2448b2fe324d9ccda2710acade256',
                '9924a7c8587336bfb118024db8674a14',
            ),

            // Test case #10
            array(
                'feffe9928665731c6d6a8f9467308308' .
                'feffe9928665731c',
                'd9313225f88406e5a55909c5aff5269a' .
                '86a7a9531534f7da2e4c303d8a318a72' .
                '1c3c0c95956809532fcf0e2449a6b525' .
                'b16aedf5aa0de657ba637b39',
                'feedfacedeadbeeffeedfacedeadbeef' .
                'abaddad2',
                'cafebabefacedbaddecaf888',
                '3980ca0b3c00e841eb06fac4872a2757' .
                '859e1ceaa6efd984628593b40ca1e19c' .
                '7d773d00c144c525ac619d18c84a3f47' .
                '18e2448b2fe324d9ccda2710',
                '2519498e80f1478f37ba55bd6d27618c',
            ),

            // Test case #11
            array(
                'feffe9928665731c6d6a8f9467308308' .
                'feffe9928665731c',
                'd9313225f88406e5a55909c5aff5269a' .
                '86a7a9531534f7da2e4c303d8a318a72' .
                '1c3c0c95956809532fcf0e2449a6b525' .
                'b16aedf5aa0de657ba637b39',
                'feedfacedeadbeeffeedfacedeadbeef' .
                'abaddad2',
                'cafebabefacedbad',
                '0f10f599ae14a154ed24b36e25324db8' .
                'c566632ef2bbb34f8347280fc4507057' .
                'fddc29df9a471f75c66541d4d4dad1c9' .
                'e93a19a58e8b473fa0f062f7',
                '65dcc57fcf623a24094fcca40d3533f8',
            ),

            // Test case #12
            array(
                'feffe9928665731c6d6a8f9467308308' .
                'feffe9928665731c',
                'd9313225f88406e5a55909c5aff5269a' .
                '86a7a9531534f7da2e4c303d8a318a72' .
                '1c3c0c95956809532fcf0e2449a6b525' .
                'b16aedf5aa0de657ba637b39',
                'feedfacedeadbeeffeedfacedeadbeef' .
                'abaddad2',
                '9313225df88406e555909c5aff5269aa' .
                '6a7a9538534f7da1e4c303d2a318a728' .
                'c3c0c95156809539fcf0e2429a6b5254' .
                '16aedbf5a0de6a57a637b39b',
                'd27e88681ce3243c4830165a8fdcf9ff' .
                '1de9a1d8e6b447ef6ef7b79828666e45' .
                '81e79012af34ddd9e2f037589b292db3' .
                'e67c036745fa22e7e9b7373b',
                'dcf566ff291c25bbb8568fc3d376a6d9',
            ),

            // Test case #13
            array(
                '00000000000000000000000000000000' .
                '00000000000000000000000000000000',
                '',
                '',
                '000000000000000000000000',
                '',
                '530f8afbc74536b9a963b4f1c4cb738b',
            ),

            // Test case #14
            array(
                '00000000000000000000000000000000' .
                '00000000000000000000000000000000',
                '00000000000000000000000000000000',
                '',
                '000000000000000000000000',
                'cea7403d4d606b6e074ec5d3baf39d18',
                'd0d1c8a799996bf0265b98b5d48ab919',
            ),

            // Test case #15
            array(
                'feffe9928665731c6d6a8f9467308308' .
                'feffe9928665731c6d6a8f9467308308',
                'd9313225f88406e5a55909c5aff5269a' .
                '86a7a9531534f7da2e4c303d8a318a72' .
                '1c3c0c95956809532fcf0e2449a6b525' .
                'b16aedf5aa0de657ba637b391aafd255',
                '',
                'cafebabefacedbaddecaf888',
                '522dc1f099567d07f47f37a32a84427d' .
                '643a8cdcbfe5c0c97598a2bd2555d1aa' .
                '8cb08e48590dbb3da7b08b1056828838' .
                'c5f61e6393ba7a0abcc9f662898015ad',
                'b094dac5d93471bdec1a502270e3cc6c',
            ),

            // Test case #16
            array(
                'feffe9928665731c6d6a8f9467308308' .
                'feffe9928665731c6d6a8f9467308308',
                'd9313225f88406e5a55909c5aff5269a' .
                '86a7a9531534f7da2e4c303d8a318a72' .
                '1c3c0c95956809532fcf0e2449a6b525' .
                'b16aedf5aa0de657ba637b39',
                'feedfacedeadbeeffeedfacedeadbeef' .
                'abaddad2',
                'cafebabefacedbaddecaf888',
                '522dc1f099567d07f47f37a32a84427d' .
                '643a8cdcbfe5c0c97598a2bd2555d1aa' .
                '8cb08e48590dbb3da7b08b1056828838' .
                'c5f61e6393ba7a0abcc9f662',
                '76fc6ece0f4e1768cddf8853bb2d551b',
            ),

            // Test case #17
            array(
                'feffe9928665731c6d6a8f9467308308' .
                'feffe9928665731c6d6a8f9467308308',
                'd9313225f88406e5a55909c5aff5269a' .
                '86a7a9531534f7da2e4c303d8a318a72' .
                '1c3c0c95956809532fcf0e2449a6b525' .
                'b16aedf5aa0de657ba637b39',
                'feedfacedeadbeeffeedfacedeadbeef' .
                'abaddad2',
                'cafebabefacedbad',
                'c3762df1ca787d32ae47c13bf19844cb' .
                'af1ae14d0b976afac52ff7d79bba9de0' .
                'feb582d33934a4f0954cc2363bc73f78' .
                '62ac430e64abe499f47c9b1f',
                '3a337dbf46a792c45e454913fe2ea8f2',
            ),

            // Test case #18
            array(
                'feffe9928665731c6d6a8f9467308308' .
                'feffe9928665731c6d6a8f9467308308',
                'd9313225f88406e5a55909c5aff5269a' .
                '86a7a9531534f7da2e4c303d8a318a72' .
                '1c3c0c95956809532fcf0e2449a6b525' .
                'b16aedf5aa0de657ba637b39',
                'feedfacedeadbeeffeedfacedeadbeef' .
                'abaddad2',
                '9313225df88406e555909c5aff5269aa' .
                '6a7a9538534f7da1e4c303d2a318a728' .
                'c3c0c95156809539fcf0e2429a6b5254' .
                '16aedbf5a0de6a57a637b39b',
                '5a8def2f0c9e53f1f75d7853659e2a20' .
                'eeb2b22aafde6419a058ab4f6f746bf4' .
                '0fc0c3b780f244452da3ebf1c5d82cde' .
                'a2418997200ef82e44ae7e3f',
                'a44a8266ee1c8eb0c8b5d4cf5ae9f19a',
            ),
        );
    }

    /**
     * @dataProvider vectors
     * @group medium
     */
    public function testGCM($K, $P, $A, $IV, $C, $T)
    {
        $IV = pack('H*', $IV);
        $A  = pack('H*', $A);
        $gcm = $this->getGCM($K);
        list($outC, $outT) = $gcm->ae($IV, pack('H*', $P), $A);
        $this->assertSame($C, bin2hex($outC));
        $this->assertSame($T, bin2hex($outT));

        $outP = $gcm->ad($IV, $outC, $A, $outT);
        $this->assertSame($P, bin2hex($outP));
    }
}
