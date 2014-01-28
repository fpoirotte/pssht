<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Messages\CHANNEL\REQUEST;

use Clicky\Pssht\MessageInterface;
use Clicky\Pssht\Wire\Encoder;
use Clicky\Pssht\Wire\Decoder;

abstract class  Base
implements      MessageInterface
{
    protected $_channel;
    protected $_type;
    protected $_wantReply;

    public function __construct($senderChannel, $type, $wantReply)
    {
        $this->_channel     = $senderChannel;
        $this->_type        = $type;
        $this->_wantReply   = $wantReply;
    }

    static public function getMessageId()
    {
        return 98;
    }

    public function serialize(Encoder $encoder)
    {
        $encoder->encode_uint32($this->_channel);
        $encoder->encode_string($this->_type);
        $encoder->encode_boolean($this->_wantReply);
    }

    static protected function _unserialize(Decoder $decoder)
    {
        throw new \RuntimeException();
    }

    final static public function unserialize(Decoder $decoder)
    {
        $reflector  = new \ReflectionClass(get_called_class());
        $args       = array_merge(
            array(
                $decoder->decode_uint32(),
                $decoder->decode_string(),
                $decoder->decode_boolean()
            ),
            static::_unserialize($decoder)
        );
        return $reflector->newInstanceArgs($args);
    }

    public function getType()
    {
        return $this->_type;
    }

    public function getChannel()
    {
        return $this->_channel;
    }

    public function wantsReply()
    {
        return $this->_wantReply;
    }
}

