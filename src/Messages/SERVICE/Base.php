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

/**
 * Abstract SSH_MSG_SERVICE_* message (RFC 4253).
 */
abstract class Base implements MessageInterface
{
    protected $service;

    public function __construct($service)
    {
        if (!is_string($service)) {
            throw new \InvalidArgumentException();
        }

        $this->service = $service;
    }

    public function serialize(Encoder $encoder)
    {
        $encoder->encodeString($this->service);
        return $this;
    }

    public static function unserialize(Decoder $decoder)
    {
        return new static($decoder->decodeString());
    }

    public function getServiceName()
    {
        return $this->service;
    }
}
