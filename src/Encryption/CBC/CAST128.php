<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Encryption\CBC;

/**
 * CAST-128 cipher in CBC mode
 * (OPTIONAL in RFC 4253).
 */
class CAST128 extends \fpoirotte\Pssht\Encryption\Base
{
    public static function getAlgorithm()
    {
        return 'MCRYPT_CAST_128';
    }

    public static function getKeySize()
    {
        return 128 >> 3;
    }
}
