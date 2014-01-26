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

class       UNIMPLEMENTED
implements  \Clicky\Pssht\MessageInterface
{
    protected $_sequenceNo;

    public function __construct($sequenceNo)
    {
        $this->_sequenceNo = $sequenceNo;
    }

    static public function getMessageId()
    {
        return 3;
    }

    public function serialize(\Clicky\Pssht\Wire\Encoder $encoder)
    {
        $encoder->encode_uint32($this->_sequenceNo);
    }

    static public function unserialize(\Clicky\Pssht\Wire\Decoder $decoder)
    {
        return new self($decoder->decode_uint32());
    }

    public function getSequenceNo()
    {
        return $this->_sequenceNo;
    }
}

