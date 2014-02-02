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

class   Connection
{
    protected $_service;
    protected $_authRequest;
    protected $_sessions;
    protected $_channels;
    protected $_application;
    protected $_applicationFactory;

    public function __construct(
        SSHUserAuth $service,
        REQUEST     $authRequest
    )
    {
        $this->_service     = $service;
        $this->_authRequest = $authRequest;
        $this->_sessions    = array();
        $this->_channels    = array();
    }

    public function getService()
    {
        return $this->_service;
    }

    public function getAuthRequest()
    {
        return $this->_authRequest;
    }

    public function writeMessage(\Clicky\Pssht\MessageInterface $message)
    {
        return $this->_service->getTransport()->writeMessage($message);
    }

    // SSH_MSG_CHANNEL_OPEN
    public function _handle_90(Decoder $decoder, $remaining)
    {
        $message            = \Clicky\Pssht\Messages\CHANNEL\OPEN::unserialize($decoder);
        $recipientChannel   = $message->getSenderChannel();

        if ($message->getType() === 'session') {
            for ($i = 0; isset($this->_sessions[$i]); $i++) ;
            $this->_sessions[$i] = $message;
            $response = new \Clicky\Pssht\Messages\CHANNEL\OPEN\CONFIRMATION(
                $recipientChannel,
                $i,
                0x200000,
                0x800000
            );
        }
        else {
            $response = new \Clicky\Pssht\Messages\CHANNEL\OPEN\FAILURE(
                $recipientChannel,
                \Clicky\Pssht\Messages\CHANNEL\OPEN\FAILURE::SSH_OPEN_UNKNOWN_CHANNEL_TYPE,
                'No such channel type'
            );
        }
        $this->writeMessage($response);
        return TRUE;
    }

    // SSH_MSG_CHANNEL_OPEN_CONFIRMATION
    public function _handle_91(Decoder $decoder, $remaining)
    {
        /// @FIXME: Support this message for clients.
        return TRUE;
    }

    // SSH_MSG_CHANNEL_OPEN_FAILURE
    public function _handle_92(Decoder $decoder, $remaining)
    {
        /// @FIXME: Support this message for clients.
        return TRUE;
    }

    // SSH_MSG_CHANNEL_WINDOW_ADJUST
    public function _handle_93(Decoder $decoder, $remaining)
    {
        return TRUE;
    }

    // SSH_MSG_CHANNEL_DATA
    public function _handle_94(Decoder $decoder, $remaining)
    {
        $message = \Clicky\Pssht\Messages\CHANNEL\DATA::unserialize($decoder);
        $this->_application->handle($message, $remaining);
        return TRUE;
    }

    // SSH_MSG_CHANNEL_EXTENDED_DATA
    public function _handle_95(Decoder $decoder, $remaining)
    {
        return TRUE;
    }

    // SSH_MSG_CHANNEL_EOF
    public function _handle_96(Decoder $decoder, $remaining)
    {
        return TRUE;
    }

    // SSH_MSG_CHANNEL_CLOSE
    public function _handle_97(Decoder $decoder, $remaining)
    {
        $message = \Clicky\Pssht\Messages\CHANNEL\CLOSE::unserialize($decoder);
        $channel = $message->getChannel();
        $response = new \Clicky\Pssht\Messages\CHANNEL\CLOSE($this->getChannel($channel));
        $this->writeMessage($response);
        unset($this->__sessions[$channel]);
        return TRUE;
    }

    // SSH_MSG_CHANNEL_REQUEST
    public function _handle_98(Decoder $decoder, $remaining)
    {
        $encoder    = new Encoder(new Buffer());
        $channel    = $decoder->decode_uint32();
        $type       = $decoder->decode_string();
        $wantsReply = $decoder->decode_boolean();

        $encoder->encode_uint32($channel);
        $encoder->encode_string($type);
        $encoder->encode_boolean($wantsReply);
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
                return TRUE;
        }

        if (!$wantsReply)
            return TRUE;

        if ($type === 'shell') {
            $response = new \Clicky\Pssht\Messages\CHANNEL\SUCCESS($this->getChannel($channel));
        }
        else {
            $response = new \Clicky\Pssht\Messages\CHANNEL\FAILURE($this->getChannel($channel));
        }
        $this->writeMessage($response);

        if ($type === 'shell') {
            $this->_application = new \Clicky\Pssht\XRL($this, $message);
        }

        return TRUE;
    }

    // SSH_MSG_CHANNEL_SUCCESS
    public function _handle_99(Decoder $decoder, $remaining)
    {
        /// @FIXME: Support this message for clients.
        return TRUE;
    }

    // SSH_MSG_CHANNEL_FAILURE
    public function _handle_100(Decoder $decoder, $remaining)
    {
        /// @FIXME: Support this message for clients.
        return TRUE;
    }

    public function handleMessage($msgType, Decoder $decoder, $remaining)
    {
        $func = '_handle_' . $msgType;
        if (method_exists($this, $func))
            return call_user_func(array($this, $func), $decoder, $remaining);
        throw new \RuntimeException();
    }

    public function getChannel($message)
    {
        if (is_int($message))
            return $this->_sessions[$message]->getSenderChannel();
        return $this->_sessions[$message->getChannel()]->getSenderChannel();
    }
}

