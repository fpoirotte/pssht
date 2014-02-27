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

/**
 * SSH_MSG_CHANNEL_OPEN message (RFC 4254).
 */
class OPEN extends Base
{
    /// Channel type.
    protected $type;

    /// Initial window size for the channel.
    protected $initialWindowSize;

    /// Maximum packet size.
    protected $maximumPacketSize;


    /**
     * Construct a new SSH_MSG_CHANNEL_OPEN message.
     *
     *  \param string $type
     *      Channel type to open.
     *
     *  \copydetails Base::__construct
     *
     *  \param int $initialWindowSize
     *      Initial window size for the channel.
     *
     *  \param int $maximumPacketSize
     *      Maximum packet size.
     */
    public function __construct($type, $channel, $initialWindowSize, $maximumPacketSize)
    {
        $this->type                 = $type;
        parent::__construct($channel);
        $this->initialWindowSize    = $initialWindowSize;
        $this->maximumPacketSize    = $maximumPacketSize;
    }

    public static function getMessageId()
    {
        return 90;
    }

    public function serialize(\Clicky\Pssht\Wire\Encoder $encoder)
    {
        $encoder->encodeString($this->type);
        parent::serialize($encoder);
        $encoder->encodeUint32($this->initialWindowSize);
        $encoder->encodeUint32($this->maximumPacketSize);
        return $this;
    }

    public static function unserialize(\Clicky\Pssht\Wire\Decoder $decoder)
    {
        return new static(
            $decoder->decodeString(),
            $decoder->decodeUint32(),   // channel
            $decoder->decodeUint32(),
            $decoder->decodeUint32()
        );
    }

    /**
     * Get the channel type to open.
     *
     *  \retval string
     *      Channel type to open.
     */
    public function getType()
    {
        return $this->type;
    }
}
