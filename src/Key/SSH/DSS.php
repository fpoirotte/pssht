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
 * Public key using the Digital Signature Algorithm (DSA),
 * as used in the Digital Signature Standard (DSS).
 */
class DSS implements \fpoirotte\Pssht\KeyInterface
{
    /// DER header for DSA.
    const DER_HEADER = "\x30\x20\x30\x0c\x06\x08\x2a\x86\x48\x86\xf7\x0d\x02\x05\x05\x00\x04\x10";

    /// DSA parameter p.
    protected $p;

    /// DSA prime number.
    protected $q;

    /// DSA parameter g.
    protected $g;

    /// Public key.
    protected $y;

    /// Private key.
    protected $x;


    /**
     * Construct a new public/private DSA key.
     *
     *  \param resource $p
     *      GMP resource containing the p parameter for DSA.
     *
     *  \param resource $q
     *      GMP resource containing the q parameter for DSA.
     *
     *  \param resource $g
     *      GMP resource containing the g parameter for DSA.
     *
     *  \param resource $y
     *      GMP resource containing the public key.
     *
     *  \param resource $x
     *      (optional) GMP resource containing the private key.
     *      If omitted, only the public part of the key is
     *      loaded, meaning that signature generation will be
     *      unavailable.
     */
    public function __construct($p, $q, $g, $y, $x = null)
    {
        $this->p = $p;
        $this->q = $q;
        $this->g = $g;
        $this->y = $y;
        $this->x = $x;
    }

    public static function getName()
    {
        return 'ssh-dss';
    }

    public function serialize(\fpoirotte\Pssht\Wire\Encoder $encoder)
    {
        $encoder->encodeString(self::getName());
        $encoder->encodeMpint($this->p);
        $encoder->encodeMpint($this->q);
        $encoder->encodeMpint($this->g);
        $encoder->encodeMpint($this->y);
    }

    public static function unserialize(\fpoirotte\Pssht\Wire\Decoder $decoder, $private = null)
    {
        $p = $decoder->decodeMpint();
        $q = $decoder->decodeMpint();
        $g = $decoder->decodeMpint();
        $y = $decoder->decodeMpint();
        if (!isset($p, $q, $g, $y)) {
            throw new \InvalidArgumentException();
        }
        return new static($p, $q, $g, $y, $private);
    }

    public function sign($message)
    {
        if ($this->x === null) {
            throw new \RuntimeException();
        }

        $H = gmp_init(sha1($message, false), 16);
        do {
            do {
                do {
                    $k = openssl_random_pseudo_bytes(20);

                    // If $k = 0, we generate a new random number.
                    if (ltrim($k, "\0") === '') {
                         continue;
                    }

                    // We reduce entropy, but since 2^159 < $this->q < 2^160,
                    // and we must have 0 < $k < $this->q, this is garanteed
                    // to work.
                    if (ord($k[0]) & 0x80) {
                        $k[0] = chr(ord($k[0]) & 0x7F);
                    }

                    $k      = gmp_init(bin2hex($k), 16);
                    $k_1    = gmp_invert($k, $this->q);
                } while ($k_1 === false);

                $r = gmp_mod(gmp_powm($this->g, $k, $this->p), $this->q);
                $s = gmp_mod(gmp_mul($k_1, gmp_add($H, gmp_mul($this->x, $r))), $this->q);
            } while ($r === 0 || $s === 0);

            $r = str_pad(gmp_strval($r, 16), 20, '0', STR_PAD_LEFT);
            $s = str_pad(gmp_strval($s, 16), 20, '0', STR_PAD_LEFT);
        } while ($this->check($message, pack('H*H*', $r, $s)) === false);

        return pack('H*H*', $r, $s);
    }

    public function check($message, $signature)
    {
        // The signature is the concatenation of $r & $s,
        // each being a 160 bits integer, hence this check.
        if (strlen($signature) != 2 * 20) {
            throw new \InvalidArgumentException();
        }

        $H      = gmp_init(sha1($message, false), 16);
        $rp     = gmp_init(bin2hex(substr($signature, 0, 20)), 16);
        $sp     = gmp_init(bin2hex(substr($signature, 20)), 16);
        $w      = gmp_invert($sp, $this->q);
        $g_u1   = gmp_powm($this->g, gmp_mod(gmp_mul($H, $w), $this->q), $this->p);
        $y_u2   = gmp_powm($this->y, gmp_mod(gmp_mul($rp, $w), $this->q), $this->p);
        $v      = gmp_mod(gmp_mod(gmp_mul($g_u1, $y_u2), $this->p), $this->q);
        return (gmp_cmp($v, $rp) === 0);
    }
}
