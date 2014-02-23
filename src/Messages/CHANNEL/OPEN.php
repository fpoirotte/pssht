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

/**
 * SSH_MSG_CHANNEL_OPEN message (RFC 4254).
 */
class OPEN implements MessageInterface
{
    protected $type;
    protected $senderChannel;
    protected $initialWindowSize;
    protected $maximumPacketSize;

    public function __construct($type, $senderChannel, $initialWindowSize, $maximumPacketSize)
    {
        $this->type                 = $type;
        $this->senderChannel        = $senderChannel;
        $this->initialWindowSize    = $initialWindowSize;
        $this->maximumPacketSize    = $maximumPacketSize;
    }

    public static function getMessageId()
    {
        return 90;
    }

    public function serialize(Encoder $encoder)
    {
        $encoder->encodeString($this->type);
        $encoder->encodeUint32($this->senderChannel);
        $encoder->encodeUint32($this->initialWindowSize);
        $encoder->encodeUint32($this->maximumPacketSize);
        return $this;
    }

    public static function unserialize(Decoder $decoder)
    {
        return new static(
            $decoder->decodeString(),
            $decoder->decodeUint32(),
            $decoder->decodeUint32(),
            $decoder->decodeUint32()
        );
    }

    public function getType()
    {
        return $this->type;
    }

    public function getSenderChannel()
    {
        return $this->senderChannel;
    }
}
