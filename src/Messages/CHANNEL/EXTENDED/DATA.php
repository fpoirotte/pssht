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

/**
 * SSH_MSG_CHANNEL_EXTENDED_DATA message (RFC 4254).
 */
class DATA implements MessageInterface
{
    const SSH_EXTENDED_DATA_STDERR = 1;

    protected $channel;
    protected $code;
    protected $data;

    public function __construct($channel, $code, $data)
    {
        $this->channel  = $channel;
        $this->code     = $code;
        $this->data     = $data;
    }

    public static function getMessageId()
    {
        return 95;
    }

    public function serialize(Encoder $encoder)
    {
        $encoder->encodeUint32($this->channel);
        $encoder->encodeUint32($this->code);
        $encoder->encodeString($this->data);
        return $this;
    }

    public static function unserialize(Decoder $decoder)
    {
        return new static(
            $decoder->decodeUint32(),
            $decoder->decodeUint32(),
            $decoder->decodeString()
        );
    }

    public function getChannel()
    {
        return $this->channel;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getData()
    {
        return $this->data;
    }
}
