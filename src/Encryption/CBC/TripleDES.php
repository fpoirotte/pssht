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
 * 3DES cipher in CBC mode
 * (REQUIRED in RFC 4253).
 */
class TripleDES extends \Clicky\Pssht\Encryption\Base
{
    public static function getName()
    {
        return '3des-cbc';
    }

    public static function getKeySize()
    {
        return 24;
    }
}
