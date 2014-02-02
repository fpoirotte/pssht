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

class Len512 extends \Clicky\Pssht\MAC\Base
{
    public static function getName()
    {
        return 'hmac-sha2-512';
    }

    public static function getHash()
    {
        return 'sha512';
    }
}
