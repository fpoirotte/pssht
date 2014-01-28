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

class       FAILURE
implements  MessageInterface
{
    protected $_channel;

    public function __construct($channel)
    {
        $this->_channel = $channel;
    }

    static public function getMessageId()
    {
        return 100;
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

