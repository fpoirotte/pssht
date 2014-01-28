<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Messages\CHANNEL\OPEN;

use Clicky\Pssht\MessageInterface;
use Clicky\Pssht\Wire\Encoder;
use Clicky\Pssht\Wire\Decoder;

class       FAILURE
implements  MessageInterface
{
    const SSH_OPEN_ADMINISTRATIVELY_PROHIBITED  = 1;
    const SSH_OPEN_CONNECT_FAILED               = 2;
    const SSH_OPEN_UNKNOWN_CHANNEL_TYPE         = 3;
    const SSH_OPEN_RESOURCE_SHORTAGE            = 4;

    protected $_recipientChannel;
    protected $_reasonCode;
    protected $_reasonMessage;
    protected $_language;

    public function __construct($recipientChannel, $reasonCode, $reasonMessage, $language = '')
    {
        $this->_recipientChannel    = $recipientChannel;
        $this->_reasonCode          = $reasonCode;
        $this->_reasonMessage       = $reasonMessage;
        $this->_language            = $language;
    }

    static public function getMessageId()
    {
        return 92;
    }

    public function serialize(Encoder $encoder)
    {
        $encoder->encode_uint32($this->_recipientChannel);
        $encoder->encode_uint32($this->_senderChannel);
        $encoder->encode_uint32($this->_initialWindowSize);
        $encoder->encode_uint32($this->_maximumPacketSize);
    }

    static public function unserialize(Decoder $decoder)
    {
        return new self(
            $decoder->decode_uint32(),
            $decoder->decode_uint32(),
            $decoder->decode_uint32(),
            $decoder->decode_uint32()
        );
    }
}

