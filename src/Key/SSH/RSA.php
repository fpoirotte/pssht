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
 * Public key using the RSA algorithm.
 */
class RSA implements \fpoirotte\Pssht\Key\KeyInterface
{
    /// DER header for RSA.
    const DER_HEADER = "\x30\x21\x30\x09\x06\x05\x2b\x0e\x03\x02\x1a\x05\x00\x04\x14";

    /// Modulus.
    protected $n;

    /// Public exponent.
    protected $e;

    /// Private exponent.
    protected $d;

    /**
     * Construct a new public/private RSA key.
     *
     *  \param resource $n
     *      GMP resource representing the modulus to use
     *      during computations.
     *
     *  \param resource $e
     *      GMP resource for the public exponent.
     *
     *  \param resource $d
     *      (optional) GMP resource for the private exponent.
     *      If omitted, only the public part of the key is
     *      loaded, meaning that signature generation will be
     *      unavailable.
     */
    public function __construct($n, $e, $d = null)
    {
        $this->n    = $n;
        $this->e    = $e;
        $this->d    = $d;
    }

    public static function getName()
    {
        return 'ssh-rsa';
    }

    public function serialize(\fpoirotte\Pssht\Wire\Encoder $encoder)
    {
        $encoder->encodeString(self::getName());
        $encoder->encodeMpint($this->e);
        $encoder->encodeMpint($this->n);
    }

    public static function unserialize(\fpoirotte\Pssht\Wire\Decoder $decoder, $private = null)
    {
        $e  = $decoder->decodeMpint();
        $n  = $decoder->decodeMpint();

        if (!isset($e, $n)) {
            throw new \InvalidArgumentException();
        }
        return new static($n, $e, $private);
    }

    public function sign($message)
    {
        if ($this->d === null) {
            throw new \RuntimeException();
        }

        $bits   = strlen(gmp_strval($this->n, 2));
        $H      = sha1($message, true);
        $T      = self::DER_HEADER . $H;
        $tLen   = strlen($T);
        $emLen  = ($bits + 7) >> 3;
        if ($emLen < $tLen + 11) {
             throw new \RuntimeException();
        }
        $PS     = str_repeat("\xFF", $emLen - $tLen - 3);
        $EM     = gmp_init(bin2hex("\x00\x01" . $PS . "\x00" . $T), 16);
        if (gmp_cmp($EM, $this->n) >= 0) {
            throw new \RuntimeException();
        }
        $s = str_pad(
            gmp_strval(gmp_powm($EM, $this->d, $this->n), 16),
            $emLen * 2,
            '0',
            STR_PAD_LEFT
        );
        return pack('H*', $s);
    }

    public function check($message, $signature)
    {
        // Decode given signature.
        $bits   = strlen(gmp_strval($this->n, 2));
        $emLen  = ($bits + 7) >> 3;
        if (strlen($signature) !== $emLen) {
            throw new \InvalidArgumentException();
        }
        $s = gmp_init(bin2hex($signature), 16);
        if (gmp_cmp($s, $this->n) >= 0) {
            throw new \InvalidArgumentException();
        }
        $m      = gmp_powm($s, $this->e, $this->n);
        $EM     = bin2hex(pack('H*', str_pad(gmp_strval($m, 16), $emLen * 2, '0', STR_PAD_LEFT)));

        // Generate actual signature.
        $H      = sha1($message, true);
        $T      = self::DER_HEADER . $H;
        $tLen   = strlen($T);
        if ($emLen < $tLen + 11) {
            throw new \RuntimeException();
        }
        $PS     = str_repeat("\xFF", $emLen - $tLen - 3);
        $EMb    = bin2hex("\x00\x01" . $PS . "\x00" . $T);

        // Compare the two.
        return ($EM === $EMb);
    }
}
