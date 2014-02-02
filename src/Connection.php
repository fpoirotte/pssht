<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht;

use Clicky\Pssht\Services\SSHUserAuth;
use Clicky\Pssht\Messages\USERAUTH\REQUEST;
use Clicky\Pssht\Wire\Encoder;
use Clicky\Pssht\Wire\Decoder;

class Connection
{
    protected $service;
    protected $authRequest;
    protected $sessions;
    protected $channels;
    protected $application;
    protected $applicationFactory;

    public function __construct(
        SSHUserAuth $service,
        REQUEST $authRequest
    ) {
        $this->service      = $service;
        $this->authRequest  = $authRequest;
        $this->sessions     = array();
        $this->channels     = array();
    }

    public function getService()
    {
        return $this->service;
    }

    public function getAuthRequest()
    {
        return $this->authRequest;
    }

    public function writeMessage(\Clicky\Pssht\MessageInterface $message)
    {
        return $this->service->getTransport()->writeMessage($message);
    }

    // SSH_MSG_CHANNEL_OPEN
    public function handleCode90(Decoder $decoder, $remaining)
    {
        $message            = \Clicky\Pssht\Messages\CHANNEL\OPEN::unserialize($decoder);
        $recipientChannel   = $message->getSenderChannel();

        if ($message->getType() === 'session') {
            for ($i = 0; isset($this->sessions[$i]); $i++) {
                // Do nothing.
            }
            $this->sessions[$i] = $message;
            $response = new \Clicky\Pssht\Messages\CHANNEL\OPEN\CONFIRMATION(
                $recipientChannel,
                $i,
                0x200000,
                0x800000
            );
        } else {
            $response = new \Clicky\Pssht\Messages\CHANNEL\OPEN\FAILURE(
                $recipientChannel,
                \Clicky\Pssht\Messages\CHANNEL\OPEN\FAILURE::SSH_OPEN_UNKNOWN_CHANNEL_TYPE,
                'No such channel type'
            );
        }
        $this->writeMessage($response);
        return true;
    }

    // SSH_MSG_CHANNEL_OPEN_CONFIRMATION
    public function handleCode91(Decoder $decoder, $remaining)
    {
        /// @FIXME: Support this message for clients.
        return true;
    }

    // SSH_MSG_CHANNEL_OPEN_FAILURE
    public function handleCode92(Decoder $decoder, $remaining)
    {
        /// @FIXME: Support this message for clients.
        return true;
    }

    // SSH_MSG_CHANNEL_WINDOW_ADJUST
    public function handleCode93(Decoder $decoder, $remaining)
    {
        return true;
    }

    // SSH_MSG_CHANNEL_DATA
    public function handleCode94(Decoder $decoder, $remaining)
    {
        $message = \Clicky\Pssht\Messages\CHANNEL\DATA::unserialize($decoder);
        $this->application->handle($message, $remaining);
        return true;
    }

    // SSH_MSG_CHANNEL_EXTENDED_DATA
    public function handleCode95(Decoder $decoder, $remaining)
    {
        return true;
    }

    // SSH_MSG_CHANNEL_EOF
    public function handleCode96(Decoder $decoder, $remaining)
    {
        return true;
    }

    // SSH_MSG_CHANNEL_CLOSE
    public function handleCode97(Decoder $decoder, $remaining)
    {
        $message = \Clicky\Pssht\Messages\CHANNEL\CLOSE::unserialize($decoder);
        $channel = $message->getChannel();
        $response = new \Clicky\Pssht\Messages\CHANNEL\CLOSE($this->getChannel($channel));
        $this->writeMessage($response);
        unset($this->sessions[$channel]);
        return true;
    }

    // SSH_MSG_CHANNEL_REQUEST
    public function handleCode98(Decoder $decoder, $remaining)
    {
        $encoder    = new Encoder(new Buffer());
        $channel    = $decoder->decodeUint32();
        $type       = $decoder->decodeString();
        $wantsReply = $decoder->decodeBoolean();

        $encoder->encodeUint32($channel);
        $encoder->encodeString($type);
        $encoder->encodeBoolean($wantsReply);
        $decoder->getBuffer()->unget($encoder->getBuffer()->get(0));

        switch ($type) {
            case 'exec':
            case 'shell':
                $cls = '\\Clicky\\Pssht\\Messages\\CHANNEL\\REQUEST\\' . ucfirst($type);
                $message = $cls::unserialize($decoder);
                break;

            default:
                if ($wantsReply) {
                    $response = new \Clicky\Pssht\Messages\CHANNEL\FAILURE($this->getChannel($channel));
                    $this->writeMessage($response);
                }
                return true;
        }

        if (!$wantsReply) {
            return true;
        }

        if ($type === 'shell') {
            $response = new \Clicky\Pssht\Messages\CHANNEL\SUCCESS($this->getChannel($channel));
        } else {
            $response = new \Clicky\Pssht\Messages\CHANNEL\FAILURE($this->getChannel($channel));
        }
        $this->writeMessage($response);

        if ($type === 'shell') {
            $this->application = new \Clicky\Pssht\XRL($this, $message);
        }

        return true;
    }

    // SSH_MSG_CHANNEL_SUCCESS
    public function handleCode99(Decoder $decoder, $remaining)
    {
        /// @FIXME: Support this message for clients.
        return true;
    }

    // SSH_MSG_CHANNEL_FAILURE
    public function handleCode100(Decoder $decoder, $remaining)
    {
        /// @FIXME: Support this message for clients.
        return true;
    }

    public function handleMessage($msgType, Decoder $decoder, $remaining)
    {
        $func = 'handleCode' . $msgType;
        if (method_exists($this, $func)) {
            return call_user_func(array($this, $func), $decoder, $remaining);
        }
        throw new \RuntimeException();
    }

    public function getChannel($message)
    {
        if (is_int($message)) {
            return $this->sessions[$message]->getSenderChannel();
        }

        return $this->sessions[$message->getChannel()]->getSenderChannel();
    }
}
