<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Messages\CHANNEL\REQUEST;

/**
 * SSH_MSG_CHANNEL_REQUEST message (RFC 4254)
 * for the "shell" request type.
 */
class Shell extends \fpoirotte\Pssht\Messages\CHANNEL\REQUEST\Base
{
    protected static function unserializeSub(\fpoirotte\Pssht\Wire\Decoder $decoder)
    {
        return array();
    }
}
