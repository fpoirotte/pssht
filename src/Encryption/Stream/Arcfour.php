<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Encryption\Stream;

/**
 * Arcfour cipher in stream mode with a 128-bit key
 * and no discarded bytes (OPTIONAL in RFC 4253).
 */
class Arcfour extends \fpoirotte\Pssht\Encryption\Base
{
    public static function getName()
    {
        return 'arcfour';
    }

    public static function getKeySize()
    {
        return 128 >> 3;
    }
}
