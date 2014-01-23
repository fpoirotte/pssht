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

use Clicky\Pssht\MessageInterface;
use Clicky\Pssht\Wire\Encoder;
use Clicky\Pssht\Wire\Decoder;

class       CHANNEL_OPEN_CONFIRMATION
implements  MessageInterface
{
    const MESSAGE_ID = 91;

    protected $_recipientChannel;
    protected $_senderChannel;
    protected $_initialWindowSize;
    protected $_maximumPacketSize;

    public function __construct($recipientChannel, $senderChannel, $initialWindowSize, $maximumPacketSize)
    {
        $this->_recipientChannel    = $recipientChannel;
        $this->_senderChannel       = $senderChannel;
        $this->_initialWindowSize   = $initialWindowSize;
        $this->_maximumPacketSize   = $maximumPacketSize;
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

