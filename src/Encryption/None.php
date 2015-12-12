<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Encryption;

/**
 * Null cipher (OPTIONAL and NOT RECOMMENDED in RFC 4253).
 */
class None implements \fpoirotte\Pssht\Encryption\EncryptionInterface
{
    public function __construct($iv, $key)
    {
    }

    public static function getName()
    {
        return 'none';
    }

    public static function getKeySize()
    {
        return 0;
    }

    public static function getIVSize()
    {
        return 0;
    }

    public static function getBlockSize()
    {
        return 0;
    }

    public function encrypt($seqno, $data)
    {
        return $data;
    }

    public function decrypt($seqno, $data)
    {
        return $data;
    }
}
