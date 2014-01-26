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

use Clicky\Pssht\MACInterface;

class   SHA1_96
extends Base_96
implements  MACInterface
{
    static protected function _getHash()
    {
        return '\\Clicky\\Pssht\\MAC\\SHA1';
    }
}

