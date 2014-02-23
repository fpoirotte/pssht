<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\MAC;

/**
 * MAC generation using the RIPEMD160 hashing algorithm.
 */
class RIPEMD160 extends Base
{
    public static function getName()
    {
        return 'hmac-ripemd160';
    }

    public static function getHash()
    {
        return 'ripemd160';
    }
}
