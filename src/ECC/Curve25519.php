<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\ECC;

/**
 * Definition for the elliptic curve "Curve25519".
 *
 * \see
 *      http://cr.yp.to/ecdh/curve25519-20060209.pdf for more information
 *      about this curve.
 * \see
 *      http://ietfreport.isoc.org/idref/draft-josefsson-tls-curve25519/
 *      for an example implementation of this curve in the TLS protocol.
 *      Most of the gory details in our implementation are based
 *      on this document.
 */
class Curve25519
{
    /// Singleton instance.
    protected static $instance = null;

    /// Set to 2**255 - 19, hence this curve's name.
    protected $p;

    protected function __construct()
    {
        $this->p = gmp_init('7FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFED', 16);
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    protected function doubleAndAdd($P2, $P3, $X1)
    {
        list($X2, $Z2) = $P2;
        list($X3, $Z3) = $P3;
        $a24    = gmp_init(121666);

        $A  = gmp_add($X2, $Z2);
        $AA = gmp_mod(gmp_mul($A, $A), $this->p);
        $B  = gmp_add(gmp_sub($X2, $Z2), $this->p);
        $BB = gmp_mod(gmp_mul($B, $B), $this->p);
        $E  = gmp_mod(gmp_add(gmp_sub($AA, $BB), $this->p), $this->p);
        $C  = gmp_add($X3, $Z3);
        $D  = gmp_add(gmp_sub($X3, $Z3), $this->p);
        $DA = gmp_mod(gmp_mul($D, $A), $this->p);
        $CB = gmp_mod(gmp_mul($C, $B), $this->p);
        $t  = gmp_add($DA, $CB);
        $X5 = gmp_mod(gmp_mul($t, $t), $this->p);
        $t  = gmp_add(gmp_sub($DA, $CB), $this->p);
        $Z5 = gmp_mod(gmp_mul($X1, gmp_mul($t, $t)), $this->p);
        $X4 = gmp_mod(gmp_mul($AA, $BB), $this->p);
        $Z4 = gmp_mod(gmp_mul($E, gmp_add($BB, gmp_mul($a24, $E))), $this->p);
        return array(array($X4, $Z4), array($X5, $Z5));
    }

    protected function scalarmult($X, $N)
    {
        if (!((is_resource($X) && get_resource_type($X) === 'GMP integer') ||
            (is_object($X) && ($X instanceof \GMP)))) {
            throw new \InvalidArgumentException();
        }

        if (!((is_resource($N) && get_resource_type($N) === 'GMP integer') ||
            (is_object($N) && ($N instanceof \GMP)))) {
            throw new \InvalidArgumentException();
        }

        $b  = str_pad(strrev(gmp_strval($N, 2)), 256, '0');
        $P1 = array(1, 0);
        $P2 = array($X, 1);

        for ($i = 255; $i >= 0; $i--) {
            if ($b[$i] === '1') {
                list($P2, $P1) = $this->doubleAndAdd($P2, $P1, $X);
            } else {
                list($P1, $P2) = $this->doubleAndAdd($P1, $P2, $X);
            }
        }
        return $P1;
    }

    protected function pubkeyToPoint($public)
    {
        if (!is_string($public) || strlen($public) !== 32) {
            throw new \InvalidArgumentException();
        }

        return array(
            gmp_init(bin2hex($public), 16),
            1
        );
    }

    protected function pointToPubkey($P)
    {
        list($X, $Z) = $P;
        $Zinv   = gmp_powm($Z, gmp_sub($this->p, 2), $this->p);
        $x1     = gmp_mod(gmp_mul($X, $Zinv), $this->p);
        $res    = str_pad(gmp_strval($x1, 16), 64, '0', STR_PAD_LEFT);
        return strrev(pack('H*', $res));
    }

    public function getPublic($S) {
        return $this->getShared($S, $this->pointToPubkey(array(9, 1)));
    }

    public function getShared($S, $P)
    {
        if (!is_string($S) || strlen($S) !== 32) {
            throw new \InvalidArgumentException();
        }

        // Clamp the secret key.
        $n = gmp_init(bin2hex(strrev($S)), 16);
        $n = gmp_and(
            gmp_or($n, 64),
            gmp_init(
                '7FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF8',
                16
            )
        );

        $P0 = $this->pubkeyToPoint(strrev($P));
        $P1 = $this->scalarmult($P0[0], $n);
        $Q  = $this->pointToPubkey($P1);
        return $Q;
    }
}
