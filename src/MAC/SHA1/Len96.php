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

class Len96 extends \Clicky\Pssht\MAC\Base96
{
    public static function getBaseClass()
    {
        return '\\Clicky\\Pssht\\MAC\\SHA1';
    }
}
