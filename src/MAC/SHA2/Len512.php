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

use Clicky\Pssht\MAC\Base;

class   Len512
extends Base
{
    static public function getName()
    {
        return 'hmac-sha2-512';
    }

    static public function getHash()
    {
        return 'sha512';
    }
}

