<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Encryption\CBC;

/**
 * Serpent cipher in CBC mode with a 128-bit key
 * (OPTIONAL in RFC 4253).
 */
class Serpent128 extends Serpent256
{
    public static function getKeySize()
    {
        return 128 >> 3;
    }
}
