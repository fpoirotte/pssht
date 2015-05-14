<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Key\SSH;

/**
 * Public key algorithm based on EdDSA (Edwards-curve
 * Digital Signature Algorithm) using curve "Ed25519".
 *
 * \see
 *      http://ed25519.cr.yp.to/ed25519-20110926.pdf for more information
 *      about this algorithm.
 * \see
 *      http://cvsweb.openbsd.org/cgi-bin/cvsweb/src/usr.bin/ssh/PROTOCOL.key?rev=1.1
 *      for the specification of OpenSSH's private key format.
 */
class ED25519 implements \fpoirotte\Pssht\KeyInterface, \fpoirotte\Pssht\AvailabilityInterface
{
    /// Public key.
    protected $pk;

    /// Private (secret) key.
    protected $sk;


    /**
     * Construct a new public/private EdDSA key.
     *
     *  \param string $pk
     *      Public key as a string.
     *
     *  \param string $sk
     *      (optional) Private key as a string.
     *      If omitted, only the public part of the key is
     *      loaded, meaning that signature generation will be
     *      unavailable.
     */
    public function __construct($pk, $sk = null)
    {
        if (strlen($pk) !== 32 || ($sk !== null && strlen($sk) !== 32)) {
            throw new \InvalidArgumentException();
        }
        $this->pk = $pk;
        $this->sk = $sk;
    }

    public static function getName()
    {
        return 'ssh-ed25519';
    }

    public function serialize(\fpoirotte\Pssht\Wire\Encoder $encoder)
    {
        $encoder->encodeString(self::getName());
        $encoder->encodeString($this->pk);
    }

    public static function unserialize(\fpoirotte\Pssht\Wire\Decoder $decoder, $private = null)
    {
        $pk = $decoder->decodeString();
        if ($pk === null) {
            throw new \InvalidArgumentException();
        }
        return new static($pk, $private);
    }


    public static function isAvailable()
    {
        return function_exists('hash_algos') &&
            function_exists('hash') &&
            in_array('sha512', hash_algos());
    }

    protected static function encodeint($y)
    {
        $t = str_pad(gmp_strval($y, 16), 64, 0, STR_PAD_LEFT);
        $res = strrev(pack('H*', $t));
        return $res;
    }

    protected static function encodepoint($P)
    {
        list($x, $y) = $P;
        $t = gmp_or(
            gmp_and($y, gmp_sub(gmp_pow(2, 256), 1)),
            gmp_mul(gmp_and($x, 1), gmp_pow(2, 255))
        );
        $t = str_pad(gmp_strval($t, 16), 64, 0, STR_PAD_LEFT);
        $res = strrev(pack('H*', $t));
        return $res;
    }

    protected static function Hint($m)
    {
        $h = hash('sha512', $m, true);
        $res = gmp_init(bin2hex(strrev($h)), 16);
        return $res;
    }

    protected static function decodeint($s)
    {
        return gmp_init(bin2hex(strrev($s)), 16);
    }

    protected static function decodepoint($s)
    {
        $curve = \fpoirotte\Pssht\ED25519::getInstance();
        $y = gmp_and(
            gmp_init(bin2hex(strrev($s)), 16),
            gmp_sub(gmp_pow(2, 255), 1)
        );
        $x = $curve->xrecover($y);
        if (gmp_cmp(gmp_and($x, 1), (ord(substr($s, -1)) >> 7) & 1)) {
            $x = gmp_sub($curve->q, $x);
        }
        $P = array($x, $y);
        if (!static::isOnCurve($P)) {
            return null;
        }
        return $P;
    }

    protected static function isOnCurve($P)
    {
        $curve = \fpoirotte\Pssht\ED25519::getInstance();
        list($x, $y) = $P;
        $x2 = gmp_mul($x, $x);
        $y2 = gmp_mul($y, $y);
        $t = gmp_mod(
            gmp_sub(
                gmp_sub(gmp_add(gmp_neg($x2), $y2), 1),
                gmp_mul($curve->d, gmp_mul($x2, $y2))
            ),
            $curve->q
        );
        return !gmp_cmp($t, 0);
    }

    public function sign($message)
    {
        if ($this->sk === null) {
            throw new \RuntimeException();
        }

        $curve = \fpoirotte\Pssht\ED25519::getInstance();
        $h = hash('sha512', $this->sk, true);
        $a = gmp_add(
            gmp_pow(2, 256-2),
            gmp_and(
                gmp_init(bin2hex(strrev($h)), 16),
                gmp_sub(gmp_pow(2, 254), 8)
            )
        );
        $r = static::Hint(substr($h, 32) . $message);
        $R = $curve->scalarmult($curve->B, $r);
        $t = static::encodepoint($R) . $this->pk . $message;
        $S = gmp_mod(
            gmp_add(
                $r,
                gmp_mul(
                    static::Hint($t),
                    $a
                )
            ),
            $curve->l
        );
        return static::encodepoint($R) . static::encodeint($S);
    }

    public function check($message, $signature)
    {
        $curve = \fpoirotte\Pssht\ED25519::getInstance();
        if (strlen($signature) !== 64) {
            throw new \InvalidArgumentException();
        }

        $R = static::decodepoint(substr($signature, 0, 32));
        if ($R === null) {
            return false;
        }

        $A = static::decodepoint($this->pk);
        if ($A === null) {
            return false;
        }
        $S = static::decodeint(substr($signature, 32, 64));
        $h = static::Hint(static::encodepoint($R) . $this->pk . $message);
        $res1 = $curve->scalarmult($curve->B, $S);
        $res2 = $curve->edwards($R, $curve->scalarmult($A, $h));
        return (!gmp_cmp($res1[0], $res2[0]) &&
                !gmp_cmp($res1[1], $res2[1]));
    }
}
