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

class CLOSE implements MessageInterface
{
    protected $channel;

    public function __construct($channel)
    {
        $this->channel = $channel;
    }

    public static function getMessageId()
    {
        return 97;
    }

    public function serialize(Encoder $encoder)
    {
        $encoder->encodeUint32($this->channel);
    }

    public static function unserialize(Decoder $decoder)
    {
        return new self($decoder->decodeUint32());
    }

    public function getChannel()
    {
        return $this->channel;
    }
}
