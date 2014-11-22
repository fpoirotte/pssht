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
 * SSH_MSG_CHANNEL_DATA message (RFC 4254).
 */
class DATA extends Base
{
    /// Payload for this message.
    protected $data;


    /**
     * Construct a new SSH_MSG_CHANNEL_DATA message.
     *
     *  \copydetails Base::__construct
     *
     *  \param string $data
     *      Actual payload.
     */
    public function __construct($channel, $data)
    {
        parent::__construct($channel);
        $this->data     = $data;
    }

    public static function getMessageId()
    {
        return 94;
    }

    public function serialize(\fpoirotte\Pssht\Wire\Encoder $encoder)
    {
        parent::serialize($encoder);
        $encoder->encodeString($this->data);
        return $this;
    }

    public static function unserialize(\fpoirotte\Pssht\Wire\Decoder $decoder)
    {
        return new static(
            $decoder->decodeUint32(),   // channel
            $decoder->decodeString()
        );
    }

    /**
     * Get the message's payload.
     *
     *  \retval string
     *      Payload for the message.
     */
    public function getData()
    {
        return $this->data;
    }
}
