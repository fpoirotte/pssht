<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Messages\KEXDH;

use Clicky\Pssht\MessageInterface;
use Clicky\Pssht\Wire\Encoder;
use Clicky\Pssht\Wire\Decoder;

class       INIT
implements  MessageInterface
{
    protected $_e;

    public function __construct($e)
    {
        $this->_e = $e;
    }

    static public function getMessageId()
    {
        return 30;
    }

    public function serialize(Encoder $encoder)
    {
        $encoder->encode_mpint($this->_e);
    }

    static public function unserialize(Decoder $decoder)
    {
        return new self($decoder->decode_mpint());
    }

    public function getE()
    {
        return $this->_e;
    }
}

