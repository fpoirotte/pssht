<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Messages\CHANNEL\OPEN;

/**
 * SSH_MSG_CHANNEL_OPEN_CONFIRMATION message (RFC 4254).
 */
class CONFIRMATION implements \fpoirotte\Pssht\MessageInterface
{
    /// Recipient channel.
    protected $recipientChannel;

    /// Sender channel.
    protected $senderChannel;

    /// Initial window size for the channel.
    protected $initialWindowSize;

    /// Maximum packet size.
    protected $maximumPacketSize;


    /**
     * Construct a new SSH_MSG_CHANNEL_OPEN_CONFIRMATION message.
     *
     *  \param int $recipientChannel
     *      Recipient channel identifier.
     *
     *  \param int $senderChannel
     *      Sender channel identifier.
     *
     *  \param int $initialWindowSize
     *      Initial window size for the channel.
     *
     *  \param int $maximumPacketSize
     *      Maximum packet size.
     */
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

    public function serialize(\fpoirotte\Pssht\Wire\Encoder $encoder)
    {
        $encoder->encodeUint32($this->recipientChannel);
        $encoder->encodeUint32($this->senderChannel);
        $encoder->encodeUint32($this->initialWindowSize);
        $encoder->encodeUint32($this->maximumPacketSize);
        return $this;
    }

    public static function unserialize(\fpoirotte\Pssht\Wire\Decoder $decoder)
    {
        return new static(
            $decoder->decodeUint32(),
            $decoder->decodeUint32(),
            $decoder->decodeUint32(),
            $decoder->decodeUint32()
        );
    }
}
