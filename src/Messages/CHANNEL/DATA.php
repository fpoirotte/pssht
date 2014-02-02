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

class DATA implements MessageInterface
{
    protected $channel;
    protected $data;

    public function __construct($channel, $data)
    {
        $this->channel  = $channel;
        $this->data     = $data;
    }

    public static function getMessageId()
    {
        return 94;
    }

    public function serialize(Encoder $encoder)
    {
        $encoder->encodeUint32($this->channel);
        $encoder->encodeString($this->data);
    }

    public static function unserialize(Decoder $decoder)
    {
        return new self(
            $decoder->decodeUint32(),
            $decoder->decodeString()
        );
    }

    public function getChannel()
    {
        return $this->channel;
    }

    public function getData()
    {
        return $this->data;
    }
}
