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
 * Definition for the elliptic curve "Ed25519".
 *
 * \see
 *      http://ed25519.cr.yp.to/ed25519-20110926.pdf
 *      for more information about this curve.
 */
class ED25519
{
    /// Singleton instance.
    protected static $instance = null;

    /// Holds various parameters for the curve (q, l, d, I, B).
    protected $params = array();

    protected function __construct()
    {
        $this->params['q'] = gmp_sub(gmp_pow(2, 255), 19);
        $this->params['l'] = gmp_add(gmp_pow(2, 252), '27742317777372353535851937790883648493');
        $this->params['d'] = gmp_mul(-121665, $this->inv(121666));
        $this->params['I'] = gmp_powm(
            2,
            gmp_div_q(gmp_sub($this->params['q'], 1), 4),
            $this->params['q']
        );
        $By = gmp_mul(4, $this->inv(5));
        $Bx = $this->xrecover($By);
        $this->params['B'] = array($Bx, $By);
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function __get($name)
    {
        return $this->params[$name];
    }

    public function __isset($name)
    {
        return isset($this->params[$name]);
    }

    protected function inv($x)
    {
        return gmp_powm($x, gmp_sub($this->params['q'], 2), $this->params['q']);
    }

    public function xrecover($y)
    {
        $xx = gmp_mul(
            gmp_sub(gmp_mul($y, $y), 1),
            $this->inv(gmp_add(gmp_mul(gmp_mul($this->params['d'], $y), $y), 1))
        );
        $x = gmp_powm($xx, gmp_div_q(gmp_add($this->params['q'], 3), 8), $this->params['q']);
        $t = gmp_mod(gmp_sub(gmp_mul($x, $x), $xx), $this->params['q']);
        if (gmp_cmp($t, 0)) {
            $x = gmp_mod(gmp_mul($x, $this->params['I']), $this->params['q']);
        }
        if (gmp_cmp(gmp_mod($x, 2), 0)) {
            $x = gmp_sub($this->params['q'], $x);
        }
        return $x;
    }

    public function edwards($P, $Q)
    {
        $x1 = $P[0];
        $y1 = $P[1];
        $x2 = $Q[0];
        $y2 = $Q[1];
        $t  = gmp_mul(
            $this->params['d'],
            gmp_mul(
                gmp_mul($x1, $x2),
                gmp_mul($y1, $y2)
            )
        );

        $x3 = gmp_mul(
            gmp_add(gmp_mul($x1, $y2), gmp_mul($x2, $y1)),
            $this->inv(gmp_add(1, $t))
        );
        $y3 = gmp_mul(
            gmp_add(gmp_mul($y1, $y2), gmp_mul($x1, $x2)),
            $this->inv(gmp_sub(1, $t))
        );

        return array(
            gmp_mod($x3, $this->params['q']),
            gmp_mod($y3, $this->params['q'])
        );
    }

    public function scalarmult($P, $e)
    {
        if (!is_array($P)) {
            throw new \InvalidArgumentException();
        }

        foreach (array($P[0], $P[1], $e) as $t) {
            if (!((is_resource($t) && get_resource_type($t) === 'GMP integer') ||
                (is_object($t) && ($t instanceof \GMP)))) {
                throw new \InvalidArgumentException();
            }
        }

        $s      = gmp_strval($e, 2);
        $len    = strlen($s);
        $res    = array(gmp_init(0), gmp_init(1));

        for ($i = 0; $i < $len; $i++) {
            $res = $this->edwards($res, $res);
            if ($s[$i] === '1') {
                $res = $this->edwards($res, $P);
            }
        }

        return $res;
    }
}
