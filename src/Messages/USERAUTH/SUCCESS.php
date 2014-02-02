<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Messages\USERAUTH;

class SUCCESS extends \Clicky\Pssht\Messages\Base
{
    public static function getMessageId()
    {
        return 52;
    }
}
