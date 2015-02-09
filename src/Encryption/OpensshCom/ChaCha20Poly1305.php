<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Encryption\OpensshCom;

/**
 * ChaCha20 & Poly1305 combined to achieve AEAD.
 */
class ChaCha20Poly1305 implements \fpoirotte\Pssht\AEADInterface
{
    protected $aead;

    public function __construct($iv, $key)
    {
        $this->aead = new \fpoirotte\Pssht\AEAD\ChaCha20Poly1305($key);
    }

    public static function getName()
    {
        return 'chacha20-poly1305@openssh.com';
    }

    public static function getKeySize()
    {
        return 512 >> 3;
    }

    public static function getIVSize()
    {
        return 0;
    }

    public static function getBlockSize()
    {
        return 1;
    }

    public static function getSize()
    {
        return 16;
    }

    public function encrypt($seqno, $data)
    {
        $len        = substr($data, 0, 4);
        $plain      = (string) substr($data, 4);
        $iv         = pack('N*', 0, $seqno);
        return $this->aead->ae($iv, $plain, $len);
    }

    public function decrypt($seqno, $data)
    {
        $iv = pack('N*', 0, $seqno);
        if (strlen($data) === 4) {
            return $this->aead->ad($iv, null, $data, null);
        }

        return $this->aead->ad(
            $iv,
            (string) substr($data, 4, -static::getSize()),  // Cipher
            substr($data, 0, 4),                            // AD (length)
            substr($data, -static::getSize())               // Tag
        );
    }
}
