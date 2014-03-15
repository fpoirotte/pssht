<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\ECC;

/**
 * Representation of a point on an elliptic curve.
 */
class Point implements \ArrayAccess
{
    protected $coordinates;

    public function __construct($x = null, $y = null)
    {
        $this->coordinates  = array(
            'x' => null,
            'y' => null
        );

        $this['x'] = $x;
        $this['y'] = $y;
    }

    public function __get($name)
    {
        return $this[$name];
    }

    public function __set($name, $value)
    {
        $this[$name] = $value;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->coordinates);
    }

    public function offsetGet($offset)
    {
        if (!array_key_exists($offset, $this->coordinates)) {
            throw new \InvalidArgumentException();
        }

        return $this->coordinates[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if (!array_key_exists($offset, $this->coordinates)) {
            throw new \InvalidArgumentException();
        }

        if (is_int($value) || is_string($value) || is_float($value)) {
            $value = gmp_init($value);
        }

        if (!is_resource($value) && $value !== null) {
            throw new \InvalidArgumentException();
        }

        $this->coordinates[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        throw new \InvalidArgumentException();
    }

    public function serialize(\Clicky\Pssht\ECC\Curve $curve)
    {
        if ($this->coordinates['x'] === null ||
            $this->coordinates['y'] === null) {
            return "\x00";
        }

        $mlen = gmp_init(strlen(gmp_strval($curve->getModulus(), 2)));
        $mlen = gmp_intval(gmp_div_q($mlen, 8, GMP_ROUND_PLUSINF)) * 2;

        $x = gmp_strval($this->coordinates['x'], 16);
        $x = pack('H*', str_pad($x, $mlen, '0', STR_PAD_LEFT));

        $y = gmp_strval($this->coordinates['y'], 16);
        $y = pack('H*', str_pad($y, $mlen, '0', STR_PAD_LEFT));

        return "\x04" . $x . $y;
    }

    public static function unserialize(\Clicky\Pssht\ECC\Curve $curve, $s)
    {
        $len = strlen($s);

        if ($len === 0) {
            throw new \InvalidArgumentException();
        }

        if ($s[0] === "\x00" && $len === 1) {
            return new static(null, null);
        }

        /// @TODO Support point compression?

        if ($s[0] !== "\x04") {
            throw new \InvalidArgumentException();
        }

        $mod    = $curve->getModulus();
        $mlen   = gmp_init(strlen(gmp_strval($mod, 2)));
        $mlen   = gmp_intval(gmp_div_q($mlen, 8, GMP_ROUND_PLUSINF)) * 2;

        if ($len !== $mlen + 1) {
            throw new \InvalidArgumentException();
        }

        $x = gmp_init(bin2hex(substr($s, 1, $mlen / 2)), 16);
        $y = gmp_init(bin2hex(substr($s, 1 + $mlen / 2)), 16);
        if (gmp_cmp($x, $mod) >= 0 || gmp_cmp($y, $mod) >= 0) {
            throw new \InvalidArgumentException();
        }

        return new static($x, $y);
    }

    public static function add(
        \Clicky\Pssht\ECC\Curve $curve,
        \Clicky\Pssht\ECC\Point $P,
        \Clicky\Pssht\ECC\Point $Q
    ) {
        $xP     = gmp_strval($P->x);
        $yP     = gmp_strval($P->y);
        $xQ     = gmp_strval($Q->x);
        $yQ     = gmp_strval($Q->y);
        $mod = $curve->getModulus();

        if ($P == $Q) {
            $alphanum = gmp_add(gmp_mul(3, gmp_mul($P->x, $P->x)), $curve->getA());
            $alphaden = gmp_mul(2, $P->y);
        } else {
            $alphanum = gmp_sub($Q->y, $P->y);
            $alphaden = gmp_sub($Q->x, $P->x);
        }

        $bezout = gmp_gcdext($alphaden, $mod);
        $alpha  = gmp_mod(gmp_mul($alphanum, $bezout['s']), $mod);
        $xR     = gmp_sub(gmp_sub(gmp_mul($alpha, $alpha), $P->x), $Q->x);
        $yR     = gmp_sub(gmp_mul($alpha, gmp_sub($P->x, $xR)), $P->y);

        return new static(
            gmp_mod(gmp_add($xR, $mod), $mod),
            gmp_mod(gmp_add($yR, $mod), $mod)
        );
    }

    public function multiply(\Clicky\Pssht\ECC\Curve $curve, $n)
    {
        if (is_int($n) || is_string($n)) {
            $n = gmp_init($n);
        }
        if (!is_resource($n)) {
            throw new \InvalidArgumentException();
        }

        if (gmp_cmp($n, '1') === 0) {
            return $this;
        }

        $s = gmp_strval($n, 2);
        $len = strlen($s);

        $res = $this;
        for ($i = 1; $i < $len; $i++) {
            $res = static::add($curve, $res, $res);
            if ($s[$i] === '1' && $i > 0) {
                $res = static::add(
                    $curve,
                    $res,
                    $this
                );
            }
        }
        return $res;
    }
}
