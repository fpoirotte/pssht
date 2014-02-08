<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Messages\USERAUTH\REQUEST;

use Clicky\Pssht\MessageInterface;
use Clicky\Pssht\Wire\Encoder;
use Clicky\Pssht\Wire\Decoder;

abstract class Base implements MessageInterface
{
    protected $user;
    protected $service;
    protected $method;

    public function __construct($user, $service, $method)
    {
        if (!is_string($user)) {
            throw new \InvalidArgumentException();
        }
        if (!is_string($service)) {
            throw new \InvalidArgumentException();
        }
        if (!is_string($method)) {
            throw new \InvalidArgumentException();
        }

        $this->user    = $user;
        $this->service = $service;
        $this->method  = $method;
    }

    public static function getMessageId()
    {
        return 50;
    }

    public function serialize(Encoder $encoder)
    {
        $encoder->encodeString($this->user);
        $encoder->encodeString($this->service);
        $encoder->encodeString($this->method);
    }

    protected static function unserializeSub(Decoder $decoder)
    {
        throw new \RuntimeException();
    }

    final public static function unserialize(Decoder $decoder)
    {
        $reflector  = new \ReflectionClass(get_called_class());
        $args       = array_merge(
            array(
                $decoder->decodeString(),
                $decoder->decodeString(),
                $decoder->decodeString()
            ),
            static::unserializeSub($decoder)
        );
        return $reflector->newInstanceArgs($args);
    }

    public function getUserName()
    {
        return $this->user;
    }

    public function getServiceName()
    {
        return $this->service;
    }

    public function getMethodName()
    {
        return $this->method;
    }
}
