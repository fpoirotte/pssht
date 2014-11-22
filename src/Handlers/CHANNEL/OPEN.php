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
class OPEN extends Base
{
    // SSH_MSG_CHANNEL_OPEN = 90
    public function handle(
        $msgType,
        \fpoirotte\Pssht\Wire\Decoder $decoder,
        \fpoirotte\Pssht\Transport $transport,
        array &$context
    ) {
        $message            = \fpoirotte\Pssht\Messages\CHANNEL\OPEN::unserialize($decoder);
        $recipientChannel   = $message->getChannel();

        if ($message->getType() === 'session') {
            $response = new \fpoirotte\Pssht\Messages\CHANNEL\OPEN\CONFIRMATION(
                $recipientChannel,
                $this->connection->allocateChannel($message),
                0x200000,
                0x800000
            );
        } else {
            $response = new \fpoirotte\Pssht\Messages\CHANNEL\OPEN\FAILURE(
                $recipientChannel,
                \fpoirotte\Pssht\Messages\CHANNEL\OPEN\FAILURE::SSH_OPEN_UNKNOWN_CHANNEL_TYPE,
                'No such channel type'
            );
        }
        $transport->writeMessage($response);
        return true;
    }
}
