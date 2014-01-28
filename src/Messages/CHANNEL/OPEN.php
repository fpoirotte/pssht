<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Messages\CHANNEL;

use Clicky\Pssht\MessageInterface;
use Clicky\Pssht\Wire\Encoder;
use Clicky\Pssht\Wire\Decoder;

class       OPEN
implements  MessageInterface
{
    protected $_type;
    protected $_senderChannel;
    protected $_initialWindowSize;
    protected $_maximumPacketSize;

    public function __construct($type, $senderChannel, $initialWindowSize, $maximumPacketSize)
    {
        $this->_type                = $type;
        $this->_senderChannel       = $senderChannel;
        $this->_initialWindowSize   = $initialWindowSize;
        $this->_maximumPacketSize   = $maximumPacketSize;
    }

    static public function getMessageId()
    {
        return 90;
    }

    public function serialize(Encoder $encoder)
    {
        $encoder->encode_string($this->_type);
        $encoder->encode_uint32($this->_senderChannel);
        $encoder->encode_uint32($this->_initialWindowSize);
        $encoder->encode_uint32($this->_maximumPacketSize);
    }

    static public function unserialize(Decoder $decoder)
    {
        return new self(
            $decoder->decode_string(),
            $decoder->decode_uint32(),
            $decoder->decode_uint32(),
            $decoder->decode_uint32()
        );
    }

    public function getType()
    {
        return $this->_type;
    }

    public function getSenderChannel()
    {
        return $this->_senderChannel;
    }
}

