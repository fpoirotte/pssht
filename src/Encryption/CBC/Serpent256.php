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
 * Serpent cipher in CBC mode with a 256-bit key
 * (OPTIONAL in RFC 4253).
 */
class Serpent256 extends \Clicky\Pssht\Encryption\Base
{
    public static function getAlgorithm()
    {
        return 'MCRYPT_SERPENT';
    }

    public static function getKeySize()
    {
        return 256 >> 3;
    }
}
