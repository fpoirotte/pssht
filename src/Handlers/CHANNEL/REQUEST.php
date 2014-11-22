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
 * Handler for SSH_MSG_CHANNEL_REQUEST messages.
 */
class REQUEST extends Base
{
    // SSH_MSG_CHANNEL_REQUEST = 98
    public function handle(
        $msgType,
        \fpoirotte\Pssht\Wire\Decoder $decoder,
        \fpoirotte\Pssht\Transport $transport,
        array &$context
    ) {
        $encoder    = new \fpoirotte\Pssht\Wire\Encoder();
        $channel    = $decoder->decodeUint32();
        $type       = $decoder->decodeString();
        $wantsReply = $decoder->decodeBoolean();

        $encoder->encodeUint32($channel);
        $encoder->encodeString($type);
        $encoder->encodeBoolean($wantsReply);
        $decoder->getBuffer()->unget($encoder->getBuffer()->get(0));
        $remoteChannel = $this->connection->getChannel($channel);

        switch ($type) {
            case 'exec':
            case 'shell':
            case 'pty-req':
                // Normalize the name.
                // Eg. "pty-req" becomes "PtyReq".
                $cls = str_replace(' ', '', ucwords(str_replace('-', ' ', $type)));
                $cls = '\\fpoirotte\\Pssht\\Messages\\CHANNEL\\REQUEST\\' . $cls;
                $message = $cls::unserialize($decoder);
                break;

            default:
                if ($wantsReply) {
                    $response = new \fpoirotte\Pssht\Messages\CHANNEL\FAILURE($remoteChannel);
                    $transport->writeMessage($response);
                }
                return true;
        }

        if (!$wantsReply) {
            return true;
        }

        if (in_array($type, array('shell', 'exec'), true)) {
            $response = new \fpoirotte\Pssht\Messages\CHANNEL\SUCCESS($remoteChannel);
        } else {
            $response = new \fpoirotte\Pssht\Messages\CHANNEL\FAILURE($remoteChannel);
        }
        $transport->writeMessage($response);

        if (in_array($type, array('shell', 'exec'), true)) {
            $callable = $transport->getApplicationFactory();
            if ($callable !== null) {
                call_user_func($callable, $transport, $this->connection, $message);
            }
        }

        return true;
    }
}
