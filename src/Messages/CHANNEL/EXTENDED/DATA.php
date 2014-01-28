<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Messages\CHANNEL\EXTENDED;

use Clicky\Pssht\MessageInterface;
use Clicky\Pssht\Wire\Encoder;
use Clicky\Pssht\Wire\Decoder;

class       DATA
implements  MessageInterface
{
    const SSH_EXTENDED_DATA_STDERR = 1;

    protected $_channel;
    protected $_code;
    protected $_data;

    public function __construct($channel, $code, $data)
    {
        $this->_channel = $channel;
        $this->_code    = $code;
        $this->_data    = $data;
    }

    static public function getMessageId()
    {
        return 95;
    }

    public function serialize(Encoder $encoder)
    {
        $encoder->encode_uint32($this->_channel);
        $encoder->encode_uint32($this->_code);
        $encoder->encode_string($this->_data);
    }

    static public function unserialize(Decoder $decoder)
    {
        return new self(
            $decoder->decode_uint32(),
            $decoder->decode_uint32(),
            $decoder->decode_string()
        );
    }

    public function getChannel()
    {
        return $this->_channel;
    }

    public function getCode()
    {
        return $this->_code;
    }

    public function getData()
    {
        return $this->_data;
    }
}

