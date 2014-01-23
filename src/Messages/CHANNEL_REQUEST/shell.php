<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Messages\CHANNEL_REQUEST;

use Clicky\Pssht\Messages\CHANNEL_REQUEST;

class   shell
extends CHANNEL_REQUEST
{
    static protected function _unserialize(\Clicky\Pssht\Wire\Decoder $decoder)
    {
        return array();
    }
}

