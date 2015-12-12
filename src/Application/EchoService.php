<?php
/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Application;

class EchoService implements \fpoirotte\Pssht\Handlers\HandlerInterface
{
    public function __construct(
        \fpoirotte\Pssht\Transport $transport,
        \fpoirotte\Pssht\Connection $connection,
        \fpoirotte\Pssht\Messages\MessageInterface $message
    ) {
        $transport->setHandler(\fpoirotte\Pssht\Messages\CHANNEL\DATA::getMessageId(), $this);
    }

    // SSH_MSG_CHANNEL_DATA = 94
    public function handle(
        $msgType,
        \fpoirotte\Pssht\Wire\Decoder $decoder,
        \fpoirotte\Pssht\Transport $transport,
        array &$context
    ) {
        $message    = \fpoirotte\Pssht\Messages\CHANNEL\DATA::unserialize($decoder);
        $channel    = $message->getChannel();
        $response   = new \fpoirotte\Pssht\Messages\CHANNEL\DATA($channel, $message->getData());
        $transport->writeMessage($response);
        return true;
    }
}
