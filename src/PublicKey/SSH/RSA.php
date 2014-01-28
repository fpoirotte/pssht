<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\PublicKey\SSH;

use Clicky\Pssht\PublicKeyInterface;
use Clicky\Pssht\Wire\Encoder;

class       RSA
implements  PublicKeyInterface
{
    const DER_HEADER = "\x30\x21\x30\x09\x06\x05\x2b\x0e\x03\x02\x1a\x05\x00\x04\x14";

    protected $_key;

    public function __construct($file)
    {
        $this->_key = openssl_pkey_get_private($file);
        $details    = openssl_pkey_get_details($this->_key);
        if ($details['type'] !== OPENSSL_KEYTYPE_RSA)
            throw new \InvalidArgumentException();
    }

    static public function getName()
    {
        return 'ssh-rsa';
    }

    public function serialize(Encoder $encoder)
    {
        $details = openssl_pkey_get_details($this->_key);
        $encoder->encode_string(self::getName());
        $encoder->encode_mpint(gmp_init(bin2hex($details['rsa']['e']), 16));
        $encoder->encode_mpint(gmp_init(bin2hex($details['rsa']['n']), 16));
    }

    public function sign($message, $raw_output = FALSE)
    {
        $res = openssl_sign(
            $message,
            $signature,
            $this->_key,
            OPENSSL_ALGO_SHA1
        );
        if ($res === FALSE)
            throw new \RuntimeException();
        return ($raw_output ? $signature : bin2hex($signature));
    }

    public function check($message, $signature)
    {
#        return openssl_verify(
#            $message,
#            $signature,
#            $this->_key,
#            OPENSSL_ALGO_SHA1
#        );

        // Decode given signature.
        $details = openssl_pkey_get_details($this->_key);
        $emLen = ($details['bits'] + 7) >> 3;
        if (strlen($signature) !== $emLen)
            throw new \InvalidArgumentException();
        $s = gmp_init(bin2hex($signature), 16);
        $n = gmp_init(bin2hex($details['rsa']['n']), 16);
        $e = gmp_init(bin2hex($details['rsa']['e']), 16);
        if (gmp_cmp($s, $n) >= 0)
            throw new \InvalidArgumentException();
        $m      = gmp_powm($s, $e, $n);
        $EM     = bin2hex(pack('H*', str_pad(gmp_strval($m, 16), $emLen * 2, '0', STR_PAD_LEFT)));

        // Generate actual signature.
        $H      = sha1($message, TRUE);
        $T      = self::DER_HEADER . $H;
        $tLen   = strlen($T);
        if ($emLen < $tLen + 11)
            throw new \RuntimeException();
        $PS     = str_repeat("\xFF", $emLen - $tLen - 3);
        $EMb    = bin2hex("\x00\x01" . $PS . "\x00" . $T);

        // Compare the two.
        return ($EM === $EMb);
    }
}

