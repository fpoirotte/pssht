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

class UNIMPLEMENTED implements \Clicky\Pssht\MessageInterface
{
    protected $sequenceNo;

    public function __construct($sequenceNo)
    {
        $this->sequenceNo = $sequenceNo;
    }

    public static function getMessageId()
    {
        return 3;
    }

    public function serialize(\Clicky\Pssht\Wire\Encoder $encoder)
    {
        $encoder->encodeUint32($this->sequenceNo);
    }

    public static function unserialize(\Clicky\Pssht\Wire\Decoder $decoder)
    {
        return new self($decoder->decodeUint32());
    }

    public function getSequenceNo()
    {
        return $this->sequenceNo;
    }
}
