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
 * SSH_MSG_CHANNEL_FAILURE message (RFC 4254).
 */
class FAILURE extends Base
{
    public static function getMessageId()
    {
        return 100;
    }
}
