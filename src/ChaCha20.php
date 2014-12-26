<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht;

/**
 * ChaCha20 block cipher.
 *
 * \see
 *      http://cr.yp.to/chacha/chacha-20080128.pdf
 * \see
 *      http://tools.ietf.org/html/draft-irtf-cfrg-chacha20-poly1305-03
 */
class ChaCha20
{
    protected $key;

    public function __construct($key)
    {
        $len = strlen($key);
        if ($len !== 32) {
            throw new \InvalidArgumentException();
        }

        $this->key = $key;
    }

    protected static function quarterRound(&$a, &$b, &$c, &$d)
    {
        $a += $b;
        $a &= 0xFFFFFFFF;
        $d ^= $a;
        $d = (($d & 0xFFFF) << 16) | (($d >> 16) & 0xFFFF);

        $c += $d;
        $c &= 0xFFFFFFFF;
        $b ^= $c;
        $b = (($b & 0xFFFFF) << 12) | (($b >> 20) & 0xFFF);

        $a += $b;
        $a &= 0xFFFFFFFF;
        $d ^= $a;
        $d = (($d & 0xFFFFFF) << 8) | (($d >> 24) & 0xFF);

        $c += $d;
        $c &= 0xFFFFFFFF;
        $b ^= $c;
        $b = (($b & 0x1FFFFFF) << 7) | (($b >> 25) & 0x7F);
    }

    protected function block($iv, $counter)
    {
        $block = array_values(
            unpack('V*', 'expand 32-byte k' . $this->key . $counter . $iv)
        );
        $init  = $block;

        for ($i = 0; $i < 10; $i++) {
            static::quarterRound($block[ 0], $block[ 4], $block[ 8], $block[12]);
            static::quarterRound($block[ 1], $block[ 5], $block[ 9], $block[13]);
            static::quarterRound($block[ 2], $block[ 6], $block[10], $block[14]);
            static::quarterRound($block[ 3], $block[ 7], $block[11], $block[15]);

            static::quarterRound($block[ 0], $block[ 5], $block[10], $block[15]);
            static::quarterRound($block[ 1], $block[ 6], $block[11], $block[12]);
            static::quarterRound($block[ 2], $block[ 7], $block[ 8], $block[13]);
            static::quarterRound($block[ 3], $block[ 4], $block[ 9], $block[14]);
        }

        $res = '';
        for ($i = 0; $i < 16; $i++) {
            $res .= pack('V', ($block[$i] + $init[$i]) & 0xFFFFFFFF);
        }
        return $res;
    }

    public function encrypt($plain, $iv, $counter = 0)
    {
        if (strlen($iv) !== 8) {
            throw new \InvalidArgumentException();
        }

        $len = strlen($plain);
        $m = ($len >> 6) + (($len % 64) > 0);
        $keyStream = '';
        for ($i = 0; $i < $m; $i++) {
            $c = gmp_strval(gmp_add($counter, $i), 16);
            $c = pack('H*', str_pad($c, 16, '0', STR_PAD_LEFT));
            $keyStream .= $this->block($iv, strrev($c));
        }
        return $plain ^ $keyStream;
    }

    public function decrypt($cipher, $iv, $counter = 0)
    {
        return $this->encrypt($cipher, $iv, $counter);
    }
}
