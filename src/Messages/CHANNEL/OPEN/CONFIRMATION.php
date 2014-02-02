<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Messages\CHANNEL\OPEN;

use Clicky\Pssht\MessageInterface;
use Clicky\Pssht\Wire\Encoder;
use Clicky\Pssht\Wire\Decoder;

class CONFIRMATION implements MessageInterface
{
    protected $recipientChannel;
    protected $senderChannel;
    protected $initialWindowSize;
    protected $maximumPacketSize;

    public function __construct($recipientChannel, $senderChannel, $initialWindowSize, $maximumPacketSize)
    {
        $this->recipientChannel     = $recipientChannel;
        $this->senderChannel        = $senderChannel;
        $this->initialWindowSize    = $initialWindowSize;
        $this->maximumPacketSize    = $maximumPacketSize;
    }

    public static function getMessageId()
    {
        return 91;
    }

    public function serialize(Encoder $encoder)
    {
        $encoder->encodeUint32($this->recipientChannel);
        $encoder->encodeUint32($this->senderChannel);
        $encoder->encodeUint32($this->initialWindowSize);
        $encoder->encodeUint32($this->maximumPacketSize);
    }

    public static function unserialize(Decoder $decoder)
    {
        return new static(
            $decoder->decodeUint32(),
            $decoder->decodeUint32(),
            $decoder->decodeUint32(),
            $decoder->decodeUint32()
        );
    }
}
