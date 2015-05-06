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
 * AES-GCM with a 128-bit key.
 */
class AES128GCM implements \fpoirotte\Pssht\AEADInterface
{
    protected $iv;
    protected $gcm;

    public function __construct($iv, $key)
    {
        $this->iv = gmp_init(bin2hex($iv), 16);
        $this->gcm = new \fpoirotte\Pssht\AEAD\GCM(
            MCRYPT_RIJNDAEL_128,
            $key,
            128
        );
    }

    public static function getName()
    {
        return 'aes128-gcm@openssh.com';
    }

    public static function getKeySize()
    {
        return 128 >> 3;
    }

    public static function getIVSize()
    {
        return 12; // 96 bits
    }

    public static function getBlockSize()
    {
        return 16; // 128 bits
    }

    public static function getSize()
    {
        return 16; // 128 bits
    }

    public function encrypt($seqno, $data)
    {
        $len        = substr($data, 0, 4);
        $plain      = (string) substr($data, 4);
        $iv         = str_pad(gmp_strval($this->iv, 16), 24, '0', STR_PAD_LEFT);
        $res        = $this->gcm->ae(pack('H*', $iv), $plain, $len);
        $this->iv   = \fpoirotte\Pssht\AEAD\GCM::inc($this->iv, 64);
        return $len . $res[0] . $res[1];
    }

    public function decrypt($seqno, $data)
    {
        if (strlen($data) === 4) {
            return $data;
        }

        $len        = substr($data, 0, 4);
        $cipher     = (string) substr($data, 4, -static::getSize());
        $tag        = substr($data, -static::getSize());
        $iv         = str_pad(gmp_strval($this->iv, 16), 24, '0', STR_PAD_LEFT);
        $res        = $this->gcm->ad(pack('H*', $iv), $cipher, $len, $tag);
        $this->iv   = \fpoirotte\Pssht\AEAD\GCM::inc($this->iv, 64);
        return $res;
    }
}
