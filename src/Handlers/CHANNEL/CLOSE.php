<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Handlers\CHANNEL;

/**
 * Handler for SSH_MSG_CHANNEL_CLOSE messages.
 */
class CLOSE extends Base
{
    // SSH_MSG_CHANNEL_CLOSE = 97
    public function handle(
        $msgType,
        \Clicky\Pssht\Wire\Decoder $decoder,
        \Clicky\Pssht\Transport $transport,
        array &$context
    ) {
        $message = \Clicky\Pssht\Messages\CHANNEL\CLOSE::unserialize($decoder);
        $channel = $message->getChannel();
        $response = new \Clicky\Pssht\Messages\CHANNEL\CLOSE(
            $this->connection->getChannel($channel)
        );
        $transport->writeMessage($response);
        $this->connection->freeChannel($channel);
        return true;
    }
}
