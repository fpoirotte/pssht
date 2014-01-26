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

use Clicky\Pssht\MessageInterface;
use Clicky\Pssht\Wire\Encoder;
use Clicky\Pssht\Wire\Decoder;

class       USERAUTH_REQUEST
implements  MessageInterface
{
    protected $_user;
    protected $_service;
    protected $_method;

    public function __construct($user, $service, $method)
    {
        if (!is_string($user))
            throw new \InvalidArgumentException();
        if (!is_string($service))
            throw new \InvalidArgumentException();
        if (!is_string($method))
            throw new \InvalidArgumentException();
        $this->_user    = $user;
        $this->_service = $service;
        $this->_method  = $method;
    }

    static public function getMessageId()
    {
        return 50;
    }

    public function serialize(Encoder $encoder)
    {
        $encoder->encode_string($this->_user);
        $encoder->encode_string($this->_service);
        $encoder->encode_string($this->_method);
    }

    static public function unserialize(Decoder $decoder)
    {
        return new self(
            $decoder->decode_string(),
            $decoder->decode_string(),
            $decoder->decode_string()
        );
    }

    public function getUserName()
    {
        return $this->_user;
    }

    public function getServiceName()
    {
        return $this->_service;
    }

    public function getMethodName()
    {
        return $this->_method;
    }
}

