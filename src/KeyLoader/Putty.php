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

class Putty
{
    private static function parseHeaderAndBody($line)
    {
        $res = explode(': ', $line, 2);
        if (count($res) !== 2) {
            return array(null, null);
        }
        return $res;
    }

    private static function parsePPK($data)
    {
        // Replace "\r" with "\n" THEN replace "\n\n" with "\n"
        // so that "\r\n" becomes just "\n", and split the result.
        $data       = explode(
            "\n",
            str_replace(array("\r", "\n\n"), "\n", $data)
        );
        $len        = count($data);
        $pos        = 0;
        $metadata   = array();

        // Key type
        if ($len <= $pos) {
            throw new \InvalidArgumentException('Invalid PPK');
        }
        list($header, $body) = self::parseHeaderAndBody($data[$pos++]);
        if ($header === 'PuTTY-User-Key-File-2') {
            $metadata['type'] = $body;
        } elseif (!strncasecmp($header, 'PuTTY-User-Key-File-', 20)) {
            throw new \InvalidArgumentException('PuTTY key format too new');
        } else {
            throw new \InvalidArgumentException('not a PuTTY SSH-2 private key');
        }

        // Encryption
        if ($len <= $pos) {
            throw new \InvalidArgumentException('Invalid PPK');
        }
        list($header, $body) = self::parseHeaderAndBody($data[$pos++]);
        if ($header !== 'Encryption' || !in_array($body, array('none', 'aes256-cbc'))) {
            throw new \InvalidArgumentException('Invalid PPK');
        }
        $metadata['cipher'] = $body;

        // Comment
        if ($len <= $pos) {
            throw new \InvalidArgumentException('Invalid PPK');
        }
        list($header, $body) = self::parseHeaderAndBody($data[$pos++]);
        if ($header !== 'Comment') {
            throw new \InvalidArgumentException('Invalid PPK');
        }
        $metadata['comment'] = $body;

        // Public-Lines
        if ($len <= $pos) {
            throw new \InvalidArgumentException('Invalid PPK');
        }
        list($header, $body) = self::parseHeaderAndBody($data[$pos++]);
        if ($header !== 'Public-Lines' || !$body || !ctype_digit($body)) {
            throw new \InvalidArgumentException('Invalid PPK');
        }
        $lines  = (int) $body;
        if ($len < $pos + $lines) {
            throw new \InvalidArgumentException('Invalid PPK');
        }
        $publicKey              = array_slice($data, $pos, $lines);
        $metadata['pub_key']    = base64_decode(implode('', $publicKey));
        $pos    += $lines;

        // Private-Lines
        if ($len <= $pos) {
            throw new \InvalidArgumentException('Invalid PPK');
        }
        list($header, $body) = self::parseHeaderAndBody($data[$pos++]);
        if ($header !== 'Private-Lines' || !$body || !ctype_digit($body)) {
            throw new \InvalidArgumentException('Invalid PPK');
        }
        $lines  = (int) $body;
        if ($len < $pos + $lines) {
            throw new \InvalidArgumentException('Invalid PPK');
        }
        $metadata['priv_key']   = base64_decode(
            implode('', array_slice($data, $pos, $lines))
        );
        $pos    += $lines;

        // Private-MAC
        if ($len <= $pos) {
            throw new \InvalidArgumentException('Invalid PPK');
        }
        list($header, $body) = self::parseHeaderAndBody($data[$pos++]);
        if ($header !== 'Private-MAC' || !$body || !ctype_xdigit($body)) {
            throw new \InvalidArgumentException('Invalid PPK');
        }
        $metadata['mac'] = pack('H*', $body);


        return $metadata;
    }

    public static function loadPublic($data)
    {
        if (!is_string($data)) {
            throw new \InvalidArgumentException();
        }

        $metadata   = self::parsePPK($data);
        $decoder    = new \fpoirotte\Pssht\Wire\Decoder();
        $decoder->getBuffer()->push($metadata['pub_key']);

        $algos  = \fpoirotte\pssht\Algorithms::factory();
        $cls    = $algos->getClass('Key', $decoder->decodeString());
        if ($cls === null) {
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

        $metadata = self::parsePPK($data);

        // Decrypt the private key.
        if ($metadata['cipher'] !== 'none') {
            $blockSize = 16; // 256 bits for AES.
            if ($passphrase === '' ||
                strlen($metadata['priv_key']) % $blockSize) {
                throw new \InvalidArgumentException('Invalid PPK');
            }

            $key =
                sha1("\x00\x00\x00\x00" . $passphrase, true) .
                sha1("\x00\x00\x00\x01" . $passphrase, true);

            $res = mcrypt_module_open(
                MCRYPT_RIJNDAEL_128,
                null,
                MCRYPT_MODE_CBC,
                null
            );

            mcrypt_generic_init(
                $res,
                substr($key, 0, mcrypt_enc_get_key_size($res)),
                str_repeat("\x00", mcrypt_enc_get_iv_size($res))
            );

            $metadata['priv_key'] = mdecrypt_generic($res, $metadata['priv_key']);
            mcrypt_generic_deinit($res);
            mcrypt_module_close($res);
        }

        // Verify the MAC.
        $blob   = '';
        $fields = array(
            $metadata['type'],
            $metadata['cipher'],
            $metadata['comment'],
            $metadata['pub_key'],
            $metadata['priv_key'],
        );
        foreach ($fields as $value) {
            $blob .= pack('N', strlen($value)) . $value;
        }
        $key = 'putty-private-key-file-mac-key';
        if ($metadata['cipher'] !== 'none' && $passphrase !== '') {
            $key .= $passphrase;
        }
        $mac = hash_hmac('sha1', $blob, sha1($key, true), true);
        if ($mac !== $metadata['mac']) {
            // Burn the memory.
            $metadata['priv_key'] = $blob = $key = $passphrase = null;
            if ($metadata['cipher'] !== 'none') {
                throw new \InvalidArgumentException('Wrong passphrase');
            } else {
                throw new \InvalidArgumentException('MAC failed');
            }
        }

        // Decode private key.
        $decoder = new \fpoirotte\Pssht\Wire\Decoder();
        $decoder->getBuffer()->push($type['priv_key']);
        switch ($metadata['type']) {
            case 'ssh-rsa':
                // Decode RSA private parameter (d).
                $private = $decoder->decodeMpint();
                break;

            case 'ssh-dss':
                // Decode DSA private parameter (x).
                $private = $decoder->decodeMpint();
                break;

            default:
                $metadata['priv_key'] = null;
                throw new \InvalidArgumentException();
        }

        if ($private === null) {
            $metadata['priv_key'] = null;
            throw new \InvalidArgumentException();
        }

        // Decode the public key and create the final object.
        $decoder    = new \fpoirotte\Pssht\Wire\Decoder();
        $decoder->getBuffer()->push($metadata['pub_key']);

        $algos  = \fpoirotte\pssht\Algorithms::factory();
        $cls    = $algos->getClass('Key', $decoder->decodeString());
        if ($cls === null) {
            $metadata['priv_key'] = null;
            throw new \InvalidArgumentException();
        }
        return $cls::unserialize($decoder, $private);
    }
}
