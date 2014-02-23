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

/**
 * SSH_MSG_CHANNEL_OPEN_FAILURE message (RFC 4254).
 */
class FAILURE implements MessageInterface
{
    const SSH_OPEN_ADMINISTRATIVELY_PROHIBITED  = 1;
    const SSH_OPEN_CONNECT_FAILED               = 2;
    const SSH_OPEN_UNKNOWN_CHANNEL_TYPE         = 3;
    const SSH_OPEN_RESOURCE_SHORTAGE            = 4;

    protected $recipientChannel;
    protected $reasonCode;
    protected $reasonMessage;
    protected $language;

    public function __construct($recipientChannel, $reasonCode, $reasonMessage, $language = '')
    {
        $this->recipientChannel = $recipientChannel;
        $this->reasonCode       = $reasonCode;
        $this->reasonMessage    = $reasonMessage;
        $this->language         = $language;
    }

    public static function getMessageId()
    {
        return 92;
    }

    public function serialize(Encoder $encoder)
    {
        $encoder->encodeUint32($this->recipientChannel);
        $encoder->encodeUint32($this->senderChannel);
        $encoder->encodeUint32($this->initialWindowSize);
        $encoder->encodeUint32($this->maximumPacketSize);
        return $this;
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
