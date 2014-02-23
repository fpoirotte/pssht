<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Handlers;

/**
 * Handler for SSH_MSG_DISCONNECT messages.
 */
class DISCONNECT implements \Clicky\Pssht\HandlerInterface
{
    // SSH_MSG_DISCONNECT = 1
    public function handle(
        $msgType,
        \Clicky\Pssht\Wire\Decoder $decoder,
        \Clicky\Pssht\Transport $transport,
        array &$context
    ) {
        $message = \Clicky\Pssht\Messages\DISCONNECT::unserialize($decoder);
        throw new $message;
    }
}
