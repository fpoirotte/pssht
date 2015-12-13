<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\KeyLoader;

/**
 * OpenSSH key loader.
 *
 * This class can read OpenSSH's Private Key format.
 * See http://cvsweb.openbsd.org/cgi-bin/cvsweb/src/usr.bin/ssh/PROTOCOL.key?rev=1.1
 * for more information on this format.
 */
class Openssh
{
    /// Magic value used to identity OpenSSH private keys.
    const AUTH_MAGIC = "openssh-key-v1\x00";

    public static function loadPublic($data)
    {
        if (!is_string($data)) {
            throw new \InvalidArgumentException();
        }

        // Normalize spaces.
        while (strpos($data, '  ') !== false) {
            $data = str_replace('  ', ' ', $data);
        }

        $algos  = \fpoirotte\pssht\Algorithms::factory();

        // First, assume a key with no options.
        $fields = explode(' ', $data, 3);
        if (count($fields) < 2) {
            throw new \InvalidArgumentException($data);
        }
        $type   = strtolower($fields[0]);
        $cls    = $algos->getClass('Key', $type);

        // Try again, this time with options parsing.
        if ($cls === null) {
            while ($data !== '') {
                $pos        = strcspn($data, ' "');
                $skipped    = substr($data, 0, $pos);
                $token      = $data[$pos];
                $data       = (string) substr($data, $pos + 1);

                if ($data === '') {
                    throw new \InvalidArgumentException();
                }

                if ($token === ' ') {
                    break;
                }

                // Eat away everything until the closing '"'.
                $pos = strpos($data, '"');
                if ($pos === false) {
                    throw new \InvalidArgumentException();
                }
                $data = (string) substr($data, $pos + 1);
            }
            if ($data === '') {
                throw new \InvalidArgumentException();
            }

            // Remaining data: "<type> <key> [comment]".
            $fields = explode(' ', $data, 3);
            if (count($fields) < 2) {
                throw new \InvalidArgumentException();
            }

            $type   = strtolower($fields[0]);
            $cls    = $algos->getClass('Key', $type);
        }

        if ($cls === null) {
            throw new \InvalidArgumentException();
        }

        $key = base64_decode($fields[1]);
        if ($key === false) {
            throw new \InvalidArgumentException();
        }

        $decoder    = new \fpoirotte\Pssht\Wire\Decoder();
        $decoder->getBuffer()->push($key);

        // Eat away the key type.
        if ($decoder->decodeString() !== $type) {
            throw new \InvalidArgumentException();
        }
        return $cls::unserialize($decoder);
    }

    public static function loadPrivate($data, $passphrase = '')
    {
        if (!is_string($data)) {
            throw new \InvalidArgumentException();
        }

        if (!is_string($passphrase)) {
            throw new \InvalidArgumentException();
        }

        $key        = openssl_pkey_get_private($data, $passphrase);
        $details    = false;
        if ($key !== false) {
            $details = openssl_pkey_get_details($key);
        }

        if ($key === false || $details === false || $details['type'] === -1) {
            // Either this is not an OpenSSH private key or it uses a format
            // which is not recognized by the PHP OpenSSL extension.
            // Eg. it is an Ed25519 private key, encoded using
            // the new OpenSSH private key file format.
            //
            // So, we try to parse it ourselves...
            // Encrypted private keys are not yet supported.
            return self::parseUnknown(
                $data,
                $passphrase
            );
        }

        $algos = \fpoirotte\pssht\Algorithms::factory();
        switch ($details['type']) {
            case OPENSSL_KEYTYPE_EC:
                // The PHP OpenSSL extension does not handle ECDSA keys
                // properly yet ($details does not actually contain any
                // useful information about the public/private key).
                //
                // So we have to parse it manually for now.
                // Again, encrypted private keys are not yet supported.
                return self::parseECDSA($data, $passphrase);

            case OPENSSL_KEYTYPE_RSA:
                $cls = $algos->getClass('Key', 'ssh-rsa');
                return new $cls(
                    gmp_init(bin2hex($details['rsa']['n']), 16),
                    gmp_init(bin2hex($details['rsa']['e']), 16),
                    gmp_init(bin2hex($details['rsa']['d']), 16)
                );

            case OPENSSL_KEYTYPE_DSA:
                $cls = $algos->getClass('Key', 'ssh-dss');
                return new $cls(
                    gmp_init(bin2hex($details['dsa']['p']), 16),
                    gmp_init(bin2hex($details['dsa']['q']), 16),
                    gmp_init(bin2hex($details['dsa']['g']), 16),
                    gmp_init(bin2hex($details['dsa']['pub_key']), 16),
                    gmp_init(bin2hex($details['dsa']['priv_key']), 16)
                );

            default:
                throw new \InvalidArgumentException('Invalid key');
        }
    }

    /**
     * Attempt to parse an OpenSSH key type which is not recognized
     * by the PHP OpenSSL extension.
     *
     *  \param string $data
     *      Raw data contained in the key file.
     *
     *  \param string $passphrase
     *      Private passphrase to use to unlock the contents.
     *
     *  \retval fpoirotte::Pssht:KeyInterface
     *      The actual key.
     */
    private static function parseUnknown($data, $passphrase)
    {
        /// @FIXME: what if it's not an OpenSSH private key?
        return self::parseOpensshPrivateKey($data, $passphrase);
    }

    /**
     * Parse a file using the new OpenSSH private key format.
     *
     *  \param string $data
     *      Raw data contained in the key file.
     *
     *  \param string $passphrase
     *      Private passphrase to use to unlock the contents.
     *
     *  \retval fpoirotte::Pssht:KeyInterface
     *      The actual key.
     *
     *  \see
     *      http://cvsweb.openbsd.org/cgi-bin/cvsweb/src/usr.bin/ssh/PROTOCOL.key?rev=1.1
     */
    private static function parseOpensshPrivateKey($data, $passphrase)
    {
        if ($passphrase !== '') {
            throw new \RuntimeException();
        }

        // For now, this is the only type of key we support.
        $type   = 'ssh-ed25519';

        $header = '-----BEGIN OPENSSH PRIVATE KEY-----';
        $footer = '-----END OPENSSH PRIVATE KEY-----';
        $data   = trim($data);
        if (strncmp($data, $header, strlen($header)) !== 0) {
            throw new \InvalidArgumentException();
        } elseif (substr($data, -strlen($footer)) !== $footer) {
            throw new \InvalidArgumentException();
        }
        $key = base64_decode(substr($data, strlen($header), -strlen($footer)));

        if (strncmp($key, static::AUTH_MAGIC, strlen(static::AUTH_MAGIC))) {
            throw new \InvalidArgumentException();
        }

        $decoder = new \fpoirotte\Pssht\Wire\Decoder();
        $decoder->getBuffer()->push(substr($key, strlen(static::AUTH_MAGIC)));

        $ciphername = $decoder->decodeString();

        /// @FIXME support encrypted private keys
        if ($ciphername !== 'none') {
            throw new \InvalidArgumentException();
        }

        $kdfname    = $decoder->decodeString();
        $kdfoptions = $decoder->decodeString();

        $numKeys    = $decoder->decodeUint32();
        $publicKeys = array();

        // Block malicious inputs
        if ($numKeys <= 0 || $numKeys >= 0x80000000) {
            throw new \InvalidArgumentException();
        }

        for ($i = 0; $i < $numKeys; $i++) {
            $tmp = new \fpoirotte\Pssht\Wire\Decoder();
            $tmp->getBuffer()->push($decoder->decodeString());

            // Reject unknown key identifiers
            if ($tmp->decodeString() !== $type) {
                continue;
            }

            $publicKeys[$i] = $tmp->decodeString();
        }

        $decoder->getBuffer()->push($decoder->decodeString());

        // Both "checkint" fields must have the same value.
        if ($decoder->decodeUint32() !== $decoder->decodeUint32()) {
            throw new \InvalidArgumentException();
        }

        // Reject unknown identifiers.
        if ($decoder->decodeString() !== $type) {
            throw new \InvalidArgumentException();
        }

        // Discard public key blob (duplicate).
        $decoder->decodeString();

        $secretKeys = array();
        for ($i = 0; $i < $numKeys; $i++) {
            $secretKeys[$i] = $decoder->decodeString();
            // Discard comment field.
            $tmp->decodeString();
        }

        // Should we also ensure that a correct padding
        // has been applied?

        $pk = reset($publicKeys);
        if (!isset($secretKeys[key($publicKeys)])) {
            throw new \InvalidArgumentException();
        }
        $sk = $secretKeys[key($publicKeys)];

        $algos  = \fpoirotte\pssht\Algorithms::factory();
        $cls    = $algos->getClass('Key', 'ssh-ed25519');
        /// @FIXME: $sk contains the private & public keys concatenated.
        return new $cls($pk, substr($sk, 0, 32));
    }

    private static function parseECDSA($data, $passphrase)
    {
        if ($passphrase !== '') {
            throw new \RuntimeException();
        }

        $key    = str_replace(array("\r", "\n"), '', $data);
        $header = '-----BEGIN EC PRIVATE KEY-----';
        $footer = '-----END EC PRIVATE KEY-----';
        if (strncmp($key, $header, strlen($header)) !== 0) {
            throw new \InvalidArgumentException();
        } elseif (substr($key, -strlen($footer)) !== $footer) {
            throw new \InvalidArgumentException();
        }
        $key = base64_decode(substr($key, strlen($header), -strlen($footer)));

        if ($key === false || strncmp($key, "\x30\x77\x02\x01\x01\x04", 6) !== 0) {
            throw new \InvalidArgumentException();
        }
        $key = substr($key, 6);

        $len        = ord($key[0]);
        $privkey    = gmp_init(bin2hex(substr($key, 1, $len)), 16);
        $key        = substr($key, $len + 1);

        if ($key[0] !== "\xA0" || $key[2] !== "\x06") {
            throw new \InvalidArgumentException();
        }
        $len        = ord($key[3]);
        if ($len + 2 !== ord($key[1])) {
            throw new \InvalidArgumentException();
        }
        $oid        = substr($key, 4, $len);
        $key        = substr($key, $len + 4);

        if ($key[0] !== "\xA1" || $key[2] !== "\x03") {
            throw new \InvalidArgumentException();
        }
        $len        = ord($key[3]);
        if ($len + 2 !== ord($key[1]) || strlen($key) !== $len + 4) {
            throw new \InvalidArgumentException();
        }

        // Map each curves' OID to its curve domain parameter identifier.
        // See RFC 5656, Section 10.1 for more information.
        $curves     = array(
            self::encodeOID('1.2.840.10045.3.1.7')  => 'nistp256',
            self::encodeOID('1.3.132.0.34')         => 'nistp384',
            self::encodeOID('1.3.132.0.35')         => 'nistp521',
        );
        if (!isset($curves[$oid])) {
            throw new \InvalidArgumentException();
        }
        $curve      = \fpoirotte\Pssht\ECC\Curve::getCurve($curves[$oid]);
        $pubkey     = \fpoirotte\Pssht\ECC\Point::unserialize(
            $curve,
            ltrim(substr($key, 4), "\x00")
        );
        $pubkey2    = $curve->getGenerator()->multiply($curve, $privkey);

        if (gmp_strval($pubkey->x) !== gmp_strval($pubkey2->x) ||
            gmp_strval($pubkey->y) !== gmp_strval($pubkey2->y)) {
            throw new \InvalidArgumentException();
        }

        $algos  = \fpoirotte\pssht\Algorithms::factory();
        $cls    = $algos->getClass('Key', 'ecdsa-sha2-' . $curves[$oid]);
        return new $cls($pubkey, $privkey);
    }

    /**
     * Encode a textual OID into its ASN.1 representation.
     *
     * See https://msdn.microsoft.com/en-us/library/bb540809%28v=vs.85%29.aspx
     * for an explanation of the algorithm used.
     *
     *  \param string $oid
     *      Human-readable representation of an OID
     *      (eg. ".1.3.6.1.2.1.1.1").
     *
     *  \retval string
     *      Binary representation for the given OID.
     */
    public static function encodeOID($oid)
    {
        if (strspn($oid, '1234567890.') !== strlen($oid)) {
            throw new \InvalidArgumentException();
        }

        $parts  = explode('.', trim($oid, '.'));
        $root   = ((int) array_shift($parts)) * 40;
        $root  += (int) array_shift($parts);
        $res    = chr($root);

        foreach ($parts as $part) {
            if ($part === '') {
                throw new \InvalidArgumentException();
            }

            $nb = (int) $part;
            if ($nb >= 0 && $nb < 128) {
                $res .= chr($nb);
                continue;
            }

            $part   = gmp_strval(gmp_init($part, 10), 2);
            $len    = (int) ((strlen($part) + 6) / 7);
            $part   = str_split(str_pad($part, $len * 7, '0', STR_PAD_LEFT), 7);
            foreach ($part as $index => $bits) {
                $res .= chr((($index + 1 < $len) << 7) + bindec($bits));
            }
        }
        return $res;
    }
}
