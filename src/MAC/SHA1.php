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

class   SHA1
extends Base
{
    static public function getName()
    {
        return 'hmac-sha1';
    }

    static public function getHash()
    {
        return 'sha1';
    }
}

