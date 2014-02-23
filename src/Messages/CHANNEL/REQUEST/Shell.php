<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Messages\CHANNEL\REQUEST;

use Clicky\Pssht\Wire\Decoder;
use Clicky\Pssht\Messages\CHANNEL\REQUEST\Base;

/**
 * SSH_MSG_CHANNEL_REQUEST message (RFC 4254)
 * for the "shell" request type.
 */
class Shell extends Base
{
    protected static function unserializeSub(Decoder $decoder)
    {
        return array();
    }
}
