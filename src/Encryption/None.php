<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Encryption;

use Clicky\Pssht\EncryptionInterface;

/**
 * Null cipher (OPTIONAL and NOT RECOMMENDED in RFC 4253).
 */
class None implements EncryptionInterface
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

    public function encrypt($data)
    {
        return $data;
    }

    public function decrypt($data)
    {
        return $data;
    }
}
