<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Messages;

/**
 * SSH_MSG_NEWKEYS message (RFC 4253).
 */
class NEWKEYS extends \Clicky\Pssht\Messages\Base
{
    public static function getMessageId()
    {
        return 21;
    }
}
