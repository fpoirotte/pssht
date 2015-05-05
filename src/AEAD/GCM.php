<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\AEAD;

/**
 * Galois/Counter Mode (GCM) and GMAC implementation
 * based on NIST SP 800-38D.
 */
class GCM
{
    /// Block H, the hash subkey
    protected $H;

    /// Approved block cipher with a 128-bit block size
    protected $cipher;

    /// Output tag length
    protected $taglen;

    /// Pre-computation table for GF(2**128)
    protected $table;

    public function __construct($cipher, $key, $taglen)
    {
        $logging = \Plop\Plop::getInstance();

        $this->cipher   = mcrypt_module_open($cipher, null, 'ecb', null);
        mcrypt_generic_init($this->cipher, $key, str_repeat("\x00", 16));
        $this->taglen   = $taglen;

        $logging->debug('Pre-computing GCM table');
        $H  = gmp_init(
            bin2hex(mcrypt_generic($this->cipher, str_repeat("\x00", 16))),
            16
        );
        $H  = str_pad(gmp_strval($H, 2), 128, '0', STR_PAD_LEFT);
        $R  = gmp_init('E1000000000000000000000000000000', 16);

        $this->table = array();
        for ($i = 0; $i < 16; $i++) {
            $this->table[$i] = array();
            for ($j = 0; $j < 256; $j++) {
                $V = gmp_init(dechex($j) . str_repeat("00", $i), 16);
                $Z = gmp_init(0);
                for ($k = 0; $k < 128; $k++) {
                    // Compute Z_n+1
                    if ($H[$k]) {
                        $Z = gmp_xor($Z, $V);
                    }

                    // Compute V_n+1
                    $odd    = gmp_testbit($V, 0);
                    $V      = gmp_div_q($V, 2);
                    if ($odd) {
                        $V = gmp_xor($V, $R);
                    }
                }
                $this->table[$i][$j] = pack('H*', str_pad(gmp_strval($Z, 16), 32, 0, STR_PAD_LEFT));
            }
        }
        $logging->debug('Done pre-computing GCM table');
    }

    public function __destruct()
    {
        mcrypt_generic_deinit($this->cipher);
    }

    public static function inc($X, $n)
    {
        $s  = gmp_strval($X, 2);
        $s1 = (string) substr($s, 0, -$n);
        $s  = gmp_add(gmp_init(substr($s, -$n), 2), 1);
        $s  = gmp_mod($s, gmp_pow(2, $n));
        $s2 = str_pad(gmp_strval($s, 2), $n, '0', STR_PAD_LEFT);
        return gmp_init($s1 . $s2, 2);
    }

    protected function ghash($X)
    {
        $Xn = str_split($X, 16);
        $m  = count($Xn);
        if (strlen($Xn[$m - 1]) != 16) {
            throw new \InvalidArgumentException();
        }

        // Inline lookup.
        $Y = "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00";
        $Y2 = $Y;
        for ($i = 0; $i < $m; $i++) {
            $res = $Y2;
            $val = $Y ^ $Xn[$i];
            for ($j = 0; $j < 16; $j++) {
                $res = $res ^ $this->table[$j][ord($val[15 - $j])];
            }
            $Y = $res;
        }
        return $Y;
    }

    protected function gctr($ICB, $X)
    {
        if ($X === '') {
            return '';
        }

        $Xn = str_split($X, 16);
        $n  = count($Xn);
        $CB = array(1 => $ICB);
        $Yn = array();
        for ($i = 1; $i < $n; $i++) {
            $CB[$i + 1] = static::inc($CB[$i], 32);

            $t  = mcrypt_generic(
                $this->cipher,
                // Pad CB[i] to the block size (128 bits)
                pack('H*', str_pad(gmp_strval($CB[$i], 16), 32, '0', STR_PAD_LEFT))
            );
            $t = gmp_xor(
                gmp_init(bin2hex($Xn[$i - 1]), 16),
                gmp_init(bin2hex($t), 16)
            );
            $Yn[$i] = pack('H*', str_pad(gmp_strval($t, 16), 32, '0', STR_PAD_LEFT));
        }

        // Cipher
        $t  = mcrypt_generic(
            $this->cipher,
            // Pad CB[i] to the block size (128 bits)
            pack('H*', str_pad(gmp_strval($CB[$n], 16), 32, '0', STR_PAD_LEFT))
        );
        // MSB Xn*
        $t      = str_pad(gmp_strval(gmp_init(bin2hex($t), 16), 16), 32, '0', STR_PAD_LEFT);
        $nn     = strlen($Xn[$n - 1]) << 1;
        $t      = substr($t, 0, $nn);
        // Yn*
        $t      = gmp_xor(gmp_init(bin2hex($Xn[$n - 1]), 16), gmp_init($t, 16));
        $Yn[$n] = pack('H*', str_pad(gmp_strval($t, 16), $nn, '0', STR_PAD_LEFT));
        return implode('', $Yn);
    }

    public function ae($IV, $P, $A)
    {
        /// @FIXME check length constraints on inputs.
        $ivlen = strlen($IV);
        if ($ivlen === 12) {
            $J0 = $IV . "\x00\x00\x00\x01";
        } else {
            $s  = (16 - ($ivlen % 16)) % 16;
            $t  = gmp_strval(gmp_init($ivlen << 3, 10), 16);
            $J0 = $this->ghash(
                $IV .
                str_repeat("\x00", $s) .
                pack('H*', str_pad($t, 32, '0', STR_PAD_LEFT))
            );
        }

        $J0 = gmp_init(bin2hex($J0), 16);
        $C  = $this->gctr(static::inc($J0, 32), $P);
        $Cl = strlen($C);
        $u  = (16 - ($Cl % 16)) % 16;
        $Al = strlen($A);
        $v  = (16 - ($Al % 16)) % 16;
        $S  = $this->ghash(
            $A .
            str_repeat("\x00", $v) .
            $C .
            str_repeat("\x00", $u) .
            pack('H*', str_pad(gmp_strval(gmp_init($Al << 3, 10), 16), 16, '0', STR_PAD_LEFT)) .
            pack('H*', str_pad(gmp_strval(gmp_init($Cl << 3, 10), 16), 16, '0', STR_PAD_LEFT))
        );
        $T = substr($this->gctr($J0, $S), 0, $this->taglen >> 3);
        return array($C, $T);
    }

    public function ad($IV, $C, $A, $T)
    {
        /// @FIXME check length constraints on inputs.
        if (strlen($T) !== $this->taglen >> 3) {
            return null;
        }

        $ivlen = strlen($IV);
        if ($ivlen === 12) {
            $J0 = $IV . "\x00\x00\x00\x01";
        } else {
            $s  = (16 - ($ivlen % 16)) % 16;
            $t  = gmp_strval(gmp_init($ivlen << 3, 10), 16);
            $J0 = $this->ghash(
                $IV .
                str_repeat("\x00", $s) .
                pack('H*', str_pad($t, 32, '0', STR_PAD_LEFT))
            );
        }

        $J0 = gmp_init(bin2hex($J0), 16);
        $P  = $this->gctr(static::inc($J0, 32), $C);
        $Cl = strlen($C);
        $u  = (16 - ($Cl % 16)) % 16;
        $Al = strlen($A);
        $v  = (16 - ($Al % 16)) % 16;
        $S  = $this->ghash(
            $A .
            str_repeat("\x00", $v) .
            $C .
            str_repeat("\x00", $u) .
            pack('H*', str_pad(gmp_strval(gmp_init($Al << 3, 10), 16), 16, '0', STR_PAD_LEFT)) .
            pack('H*', str_pad(gmp_strval(gmp_init($Cl << 3, 10), 16), 16, '0', STR_PAD_LEFT))
        );
        $T2 = substr($this->gctr($J0, $S), 0, $this->taglen >> 3);
        if ($T !== $T) {
            return null;
        }
        return $P;
    }
}
