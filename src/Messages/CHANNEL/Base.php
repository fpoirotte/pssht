<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Messages\CHANNEL;

/**
 * Abstract SSH_MSG_CHANNEL_* message (RFC 4254).
 */
abstract class Base implements \fpoirotte\Pssht\MessageInterface
{
    /// Local channel identifier.
    protected $channel;

    /**
     * Abstract constructor for a channel-related SSH message.
     *
     *  \param int $channel
     *      Local channel identifier.
     */
    public function __construct($channel)
    {
        $this->channel = $channel;
    }

    public function serialize(\fpoirotte\Pssht\Wire\Encoder $encoder)
    {
        $encoder->encodeUint32($this->channel);
        return $this;
    }

    public static function unserialize(\fpoirotte\Pssht\Wire\Decoder $decoder)
    {
        return new static($decoder->decodeUint32());
    }

    /**
     * Get the local channel identifier.
     *
     *  \retval int
     *      Local channel identifier.
     */
    public function getChannel()
    {
        return $this->channel;
    }
}
