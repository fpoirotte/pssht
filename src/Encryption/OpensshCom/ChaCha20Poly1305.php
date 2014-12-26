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
    protected $header;
    protected $main;

    public function __construct($iv, $key)
    {
        $this->main = new \fpoirotte\Pssht\ChaCha20(substr($key, 0, 32));
        $this->header = new \fpoirotte\Pssht\ChaCha20(substr($key, 32));
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
        $polyKey    = $this->main->encrypt(str_repeat("\x00", 32), $iv, 0);
        $poly       = new \fpoirotte\Pssht\Poly1305($polyKey);
        $aad        = $this->header->encrypt($len, $iv, 0);
        $res        = $this->main->encrypt($plain, $iv, 1);
        return $aad . $res . $poly->mac($aad . $res);
    }

    public function decrypt($seqno, $data)
    {
        $iv         = pack('N*', 0, $seqno);
        $aad        = substr($data, 0, 4);
        $len        = $this->header->decrypt($aad, $iv, 0);
        if (strlen($data) === 4) {
            return $len;
        }

        $cipher     = (string) substr($data, 4, -static::getSize());
        $tag        = substr($data, -static::getSize());
        $polyKey    = $this->main->encrypt(str_repeat("\x00", 32), $iv, 0);
        $poly       = new \fpoirotte\Pssht\Poly1305($polyKey);
        if ($poly->mac($aad . $cipher) !== $tag) {
            return null;
        }
        $res        = $this->main->decrypt($cipher, $iv, 1);
        return $res;
    }
}
