<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Messages;

/**
 * SSH_MSG_UNIMPLEMENTED message (RFC 4253).
 */
class UNIMPLEMENTED implements \fpoirotte\Pssht\MessageInterface
{
    /// Sequence number for the unimplemented message.
    protected $sequenceNo;

    /**
     * Construct a SSH_MSG_UNIMPLEMENTED, indicating that some
     * received message is not (yet) implemented.
     *
     *  \param int $sequenceNo
     *      Sequence number of the received unimplemented message.
     */
    public function __construct($sequenceNo)
    {
        if (!is_int($sequenceNo)) {
            throw new \InvalidArgumentException();
        }

        $this->sequenceNo = $sequenceNo;
    }

    public static function getMessageId()
    {
        return 3;
    }

    public function serialize(\fpoirotte\Pssht\Wire\Encoder $encoder)
    {
        $encoder->encodeUint32($this->sequenceNo);
        return $this;
    }

    public static function unserialize(\fpoirotte\Pssht\Wire\Decoder $decoder)
    {
        return new static($decoder->decodeUint32());
    }

    /**
     * Get the unimplemented message's sequence number.
     *
     *  \retval int
     *      The unimplemented message's sequence number.
     */
    public function getSequenceNo()
    {
        return $this->sequenceNo;
    }
}
