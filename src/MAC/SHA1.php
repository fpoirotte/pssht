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
 * MAC generation using the SHA1 hashing algorithm.
 */
class SHA1 extends Base
{
    public static function getName()
    {
        return 'hmac-sha1';
    }

    public static function getHash()
    {
        return 'sha1';
    }
}
