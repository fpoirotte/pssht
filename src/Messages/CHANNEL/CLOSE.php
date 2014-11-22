<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Messages\CHANNEL;

/**
 * SSH_MSG_CHANNEL_CLOSE message (RFC 4254).
 */
class CLOSE extends Base
{
    public static function getMessageId()
    {
        return 97;
    }
}
