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

class Twofish extends \Clicky\Pssht\Encryption\Base
{
    public static function getKeySize()
    {
        return 256 >> 3;
    }
}
