<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Messages\SERVICE;

/**
 * SSH_MSG_SERVICE_REQUEST message (RFC 4253).
 */
class REQUEST extends \Clicky\Pssht\Messages\SERVICE\Base
{
    public static function getMessageId()
    {
        return 5;
    }
}
