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

class       DSS
implements  PublicKeyInterface
{
    const DER_HEADER = "\x30\x20\x30\x0c\x06\x08\x2a\x86\x48\x86\xf7\x0d\x02\x05\x05\x00\x04\x10";

    protected $_key;

    public function __construct($file)
    {
        $this->_key = openssl_pkey_get_private($file);
        $details    = openssl_pkey_get_details($this->_key);
        if ($details['type'] !== OPENSSL_KEYTYPE_DSA)
            throw new \InvalidArgumentException();
    }

    static public function getName()
    {
        return 'ssh-dss';
    }

    public function serialize(Encoder $encoder)
    {
        $details = openssl_pkey_get_details($this->_key);
        $encoder->encode_string(self::getName());
        $encoder->encode_mpint(gmp_init(bin2hex($details['dsa']['p']), 16));
        $encoder->encode_mpint(gmp_init(bin2hex($details['dsa']['q']), 16));
        $encoder->encode_mpint(gmp_init(bin2hex($details['dsa']['g']), 16));
        $encoder->encode_mpint(gmp_init(bin2hex($details['dsa']['pub_key']), 16));
    }

    public function sign($message, $raw_output = FALSE)
    {
        $details = openssl_pkey_get_details($this->_key);
        $H = gmp_init(sha1($message, FALSE), 16);
        $p = gmp_init(bin2hex($details['dsa']['p']), 16);
        $q = gmp_init(bin2hex($details['dsa']['q']), 16);
        $g = gmp_init(bin2hex($details['dsa']['g']), 16);
        $x = gmp_init(bin2hex($details['dsa']['priv_key']), 16);
        do {
            do {
                do {
                    $k = openssl_random_pseudo_bytes(20);

                    // If $k = 0, we generate a new random number.
                    if (ltrim($k, "\0") === '')
                         continue;

                   // We reduce entropy, but since 2^159 < $q < 2^160,
                   // and we must have 0 < $k < $q, this is garanteed
                   // to work.
                   if (ord($k[0]) & 0x80)
                       $k[0] = chr(ord($k[0]) & 0x7F);

                    $k      = gmp_init(bin2hex($k), 16);
                    $k_1    = gmp_invert($k, $q);
                } while ($k_1 === FALSE);

                $r = gmp_mod(gmp_powm($g, $k, $p), $q);
                $s = gmp_mod(gmp_mul($k_1, gmp_add($H, gmp_mul($x, $r))), $q);
            } while ($r === 0 || $s === 0);

            $r = str_pad(gmp_strval($r, 16), 20, '0', STR_PAD_LEFT);
            $s = str_pad(gmp_strval($s, 16), 20, '0', STR_PAD_LEFT);
        } while ($this->check($message, pack('H*H*', $r, $s)) === FALSE);

        return $raw_output ? pack('H*H*', $r, $s) : ($r . $s);

#        $res = openssl_sign(
#            $message,
#            $signature,
#            $this->_key,
#            OPENSSL_ALGO_DSS1
#        );
#        if ($res === FALSE)
#            throw new \RuntimeException();
#        $signature = substr($signature, 0, 40);
#        return ($raw_output ? $signature : bin2hex($signature));
    }

    public function check($message, $signature)
    {
        // The signature is the concatenation of $r & $s,
        // each being a 160 bits integer, hence this check.
        if (strlen($signature) != 2 * 20)
            throw new \InvalidArgumentException();

        $details = openssl_pkey_get_details($this->_key);
        $H  = gmp_init(sha1($message, FALSE), 16);
        $p  = gmp_init(bin2hex($details['dsa']['p']), 16);
        $q  = gmp_init(bin2hex($details['dsa']['q']), 16);
        $g  = gmp_init(bin2hex($details['dsa']['g']), 16);
        $y  = gmp_init(bin2hex($details['dsa']['pub_key']), 16);
        $rp = gmp_init(bin2hex(substr($signature, 0, 20)), 16);
        $sp = gmp_init(bin2hex(substr($signature, 20)), 16);

        $w      = gmp_invert($sp, $q);
        $g_u1   = gmp_powm($g, gmp_mod(gmp_mul($H, $w), $q), $p);
        $y_u2   = gmp_powm($y, gmp_mod(gmp_mul($rp, $w), $q), $p);
        $v      = gmp_mod(gmp_mod(gmp_mul($g_u1, $y_u2), $p), $q);
        return (gmp_cmp($v, $rp) === 0);
    }
}

