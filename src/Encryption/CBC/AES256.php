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
 * AES cipher in CBC mode with a 256-bit key
 * (OPTIONAL in RFC 4253).
 */
class AES256 extends \fpoirotte\Pssht\Encryption\Base
{
    public static function getAlgorithm()
    {
        return 'MCRYPT_RIJNDAEL_128';
    }

    public static function getKeySize()
    {
        return 256 >> 3;
    }
}
