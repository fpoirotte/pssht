<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Handlers\CHANNEL;

/**
 * Handler for SSH_MSG_CHANNEL_CLOSE messages.
 */
class CLOSE extends Base
{
    // SSH_MSG_CHANNEL_CLOSE = 97
    public function handle(
        $msgType,
        \fpoirotte\Pssht\Wire\Decoder $decoder,
        \fpoirotte\Pssht\Transport $transport,
        array &$context
    ) {
        $message = \fpoirotte\Pssht\Messages\CHANNEL\CLOSE::unserialize($decoder);
        $channel = $message->getChannel();
        $response = new \fpoirotte\Pssht\Messages\CHANNEL\CLOSE(
            $this->connection->getChannel($channel)
        );
        $transport->writeMessage($response);
        $this->connection->freeChannel($channel);
        return true;
    }
}
