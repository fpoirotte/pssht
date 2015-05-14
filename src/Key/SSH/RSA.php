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
class RSA implements \fpoirotte\Pssht\KeyInterface
{
    /// DER header for RSA.
    const DER_HEADER = "\x30\x21\x30\x09\x06\x05\x2b\x0e\x03\x02\x1a\x05\x00\x04\x14";

    /// Size of the key in bits.
    protected $bits;

    /// Modulus.
    protected $n;

    /// Public exponent.
    protected $e;

    /// Private exponent.
    protected $d;

    /**
     * Construct a new public/private RSA key.
     *
     *  \param int $bits
     *      Key size in bits.
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
    protected function __construct($bits, $n, $e, $d = null)
    {
        $this->bits = $bits;
        $this->n    = $n;
        $this->e    = $e;
        $this->d    = $d;
    }

    public static function loadPrivate($pem, $passphrase = '')
    {
        if (!is_string($pem)) {
            throw new \InvalidArgumentException();
        }

        if (!is_string($passphrase)) {
            throw new \InvalidArgumentException();
        }

        $key        = openssl_pkey_get_private($pem, $passphrase);
        $details    = openssl_pkey_get_details($key);
        if ($details['type'] !== OPENSSL_KEYTYPE_RSA) {
            throw new \InvalidArgumentException();
        }
        return new static(
            $details['bits'],
            gmp_init(bin2hex($details['rsa']['n']), 16),
            gmp_init(bin2hex($details['rsa']['e']), 16),
            gmp_init(bin2hex($details['rsa']['d']), 16)
        );
    }

    public static function loadPublic($b64)
    {
        $decoder = new \fpoirotte\Pssht\Wire\Decoder();
        $decoder->getBuffer()->push(base64_decode($b64));
        $type       = $decoder->decodeString();
        if ($type !== static::getName()) {
            throw new \InvalidArgumentException();
        }

        $e          = $decoder->decodeMpint();
        $decoder2   = new \fpoirotte\Pssht\Wire\Decoder(clone $decoder->getBuffer());
        $n          = $decoder->decodeMpint();
        $raw        = $decoder2->decodeString();
        if ($raw[0] === "\x00") {
            $raw = (string) substr($raw, 1);
        }

        if (!isset($e, $n)) {
            throw new \InvalidArgumentException();
        }
        return new static(strlen($raw) << 3, $n, $e);
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

    public function sign($message)
    {
        if ($this->d === null) {
            throw new \RuntimeException();
        }

        $H      = sha1($message, true);
        $T      = self::DER_HEADER . $H;
        $tLen   = strlen($T);
        $emLen  = ($this->bits + 7) >> 3;
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
        $emLen = ($this->bits + 7) >> 3;
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
