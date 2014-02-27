<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Messages\USERAUTH\REQUEST;

/**
 * SSH_MSG_USERAUTH_REQUEST message (RFC 4252)
 * for the "none" method.
 */
class None extends \Clicky\Pssht\Messages\USERAUTH\REQUEST\Base
{
    protected static function unserializeSub(\Clicky\Pssht\Wire\Decoder $decoder)
    {
        return array();
    }
}
