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

class       DISCONNECT
implements  MessageInterface
{
    const SSH_DISCONNECT_HOST_NOT_ALLOWED_TO_CONNECT        =    1;
    const SSH_DISCONNECT_PROTOCOL_ERROR                     =    2;
    const SSH_DISCONNECT_KEY_EXCHANGE_FAILED                =    3;
    const SSH_DISCONNECT_RESERVED                           =    4;
    const SSH_DISCONNECT_MAC_ERROR                          =    5;
    const SSH_DISCONNECT_COMPRESSION_ERROR                  =    6;
    const SSH_DISCONNECT_SERVICE_NOT_AVAILABLE              =    7;
    const SSH_DISCONNECT_PROTOCOL_VERSION_NOT_SUPPORTED     =    8;
    const SSH_DISCONNECT_HOST_KEY_NOT_VERIFIABLE            =    9;
    const SSH_DISCONNECT_CONNECTION_LOST                    =   10;
    const SSH_DISCONNECT_BY_APPLICATION                     =   11;
    const SSH_DISCONNECT_TOO_MANY_CONNECTIONS               =   12;
    const SSH_DISCONNECT_AUTH_CANCELLED_BY_USER             =   13;
    const SSH_DISCONNECT_NO_MORE_AUTH_METHODS_AVAILABLE     =   14;
    const SSH_DISCONNECT_ILLEGAL_USER_NAME                  =   15;

    protected $_code;
    protected $_message;
    protected $_language;

    public function __construct($reasonCode, $reasonMessage, $language = '')
    {
        if (!is_int($reasonCode))
            throw new \InvalidArgumentException();
        if (!is_string($reasonMessage))
            throw new \InvalidArgumentException();
        if (!is_string($language))
            throw new \InvalidArgumentException();
        $this->_code        = $reasonCode;
        $this->_message     = $reasonMessage;
        $this->_language    = $language;
    }

    static public function getMessageId()
    {
        return 1;
    }

    public function serialize(Encoder $encoder)
    {
        $encoder->encode_uint32($this->_code);
        $encoder->encode_string($this->_message);
        $encoder->encode_string($this->_language);
    }

    static public function unserialize(Decoder $decoder)
    {
        return new self(
            $decoder->decode_uint32(),
            $decoder->decode_string(),
            $decoder->decode_string()
        );
    }
}

