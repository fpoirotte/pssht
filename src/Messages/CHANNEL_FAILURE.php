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

class       CHANNEL_FAILURE
implements  MessageInterface
{
    const MESSAGE_ID = 100;

    protected $_channel;

    public function __construct($channel)
    {
        $this->_channel = $channel;
    }

    public function serialize(Encoder $encoder)
    {
        $encoder->encode_uint32($this->_channel);
    }

    static public function unserialize(Decoder $decoder)
    {
        return new self($decoder->decode_uint32());
    }

    public function getChannel()
    {
        return $this->_channel;
    }
}

