<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Messages\SERVICE;

use Clicky\Pssht\MessageInterface;
use Clicky\Pssht\Wire\Encoder;
use Clicky\Pssht\Wire\Decoder;

class       REQUEST
implements  MessageInterface
{
    protected $_service;

    public function __construct($service)
    {
        if (!is_string($service))
            throw new \InvalidArgumentException();
        $this->_service = $service;
    }

    static public function getMessageId()
    {
        return 5;
    }

    public function serialize(Encoder $encoder)
    {
        $encoder->encode_string($this->_service);
    }

    static public function unserialize(Decoder $decoder)
    {
        return new self($decoder->decode_string());
    }

    public function getServiceName()
    {
        return $this->_service;
    }
}

