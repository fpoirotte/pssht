<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Messages;

/**
 * SSH_MSG_IGNORE message (RFC 4253).
 */
class IGNORE implements \Clicky\Pssht\MessageInterface
{
    /// Payload for the ignore message.
    protected $data;

    /**
     * Construct a new SSH_MSG_IGNORE message.
     *
     *  \param string $data
     *      Payload for the message.
     */
    public function __construct($data)
    {
        if (!is_string($data)) {
            throw new \InvalidArgumentException();
        }

        $this->data = $data;
    }

    public static function getMessageId()
    {
        return 2;
    }

    public function serialize(\Clicky\Pssht\Wire\Encoder $encoder)
    {
        $encoder->encodeString($this->data);
        return $this;
    }

    public static function unserialize(\Clicky\Pssht\Wire\Decoder $decoder)
    {
        return new static($decoder->decodeString());
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
