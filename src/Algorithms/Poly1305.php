<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Algorithms;

/**
 * Poly1305-AES Message Authenticator.
 *
 * \see
 *      http://cr.yp.to/mac/poly1305-20050329.pdf
 * \see
 *      http://tools.ietf.org/html/draft-irtf-cfrg-chacha20-poly1305-03
 */
class Poly1305
{
    protected $r;
    protected $s;

    public function __construct($key)
    {
        if (strlen($key) !== 32) {
            throw new \InvalidArgumentException();
        }

        $r          = substr($key, 0, 16);
        $s          = substr($key, 16);
        $t1         = "\x0F";
        $t2         = "\xFC";
        $r[ 3]      = $r[ 3] & $t1;
        $r[ 4]      = $r[ 4] & $t2;
        $r[ 7]      = $r[ 7] & $t1;
        $r[ 8]      = $r[ 8] & $t2;
        $r[11]      = $r[11] & $t1;
        $r[12]      = $r[12] & $t2;
        $r[15]      = $r[15] & $t1;
        $this->s    = gmp_init(bin2hex(strrev($s)), 16);
        $this->r    = gmp_init(bin2hex(strrev($r)), 16);
    }

    public function mac($message)
    {
        $res    = gmp_init(0);
        if ($message !== '') {
            $chunks = str_split($message, 16);
            $q      = count($chunks);
            foreach ($chunks as $i => $chunk) {
                $t = gmp_init(bin2hex(strrev($chunk . "\x01")), 16);
                $res = gmp_add($res, gmp_mul($t, gmp_pow($this->r, $q - $i)));
            }
            $res = gmp_mod($res, gmp_sub(gmp_pow(2, 130), 5));
        }

        $res    = gmp_mod(gmp_add($res, $this->s), gmp_pow(2, 128));
        return strrev(pack('H*', str_pad(gmp_strval($res, 16), 32, '0', STR_PAD_LEFT)));
    }
}
