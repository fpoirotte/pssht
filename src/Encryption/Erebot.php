<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Encryption;

use Clicky\Pssht\Encryption\None;

class   Erebot
extends None
{
    public function __construct()
    {
    }

    static public function getName()
    {
        return 'null@erebot.net';
    }
}

