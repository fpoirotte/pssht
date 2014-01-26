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

class   CHANNEL_DATA
implements  MessageInterface
{
    protected $_channel;
    protected $_data;

    public function __construct($channel, $data)
    {
        $this->_channel     = $channel;
        $this->_data        = $data;
    }

    static public function getMessageId()
    {
        return 94;
    }

    public function serialize(Encoder $encoder)
    {
        $encoder->encode_uint32($this->_channel);
        $encoder->encode_string($this->_data);
    }

    static public function unserialize(Decoder $decoder)
    {
        return new self(
            $decoder->decode_uint32(),
            $decoder->decode_string()
        );
    }

    public function getChannel()
    {
        return $this->_channel;
    }

    public function getData()
    {
        return $this->_data;
    }
}

