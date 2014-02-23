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

use Clicky\Pssht\Wire\Encoder;
use Clicky\Pssht\Wire\Decoder;

/**
 * SSH_MSG_IGNORE message (RFC 4253).
 */
class IGNORE implements \Clicky\Pssht\MessageInterface
{
    protected $data;

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

    public function serialize(Encoder $encoder)
    {
        $encoder->encodeString($this->data);
        return $this;
    }

    public static function unserialize(Decoder $decoder)
    {
        return new static($decoder->decodeString());
    }

    public function getData()
    {
        return $this->data;
    }
}
