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

abstract class Base implements MessageInterface
{
    protected $channel;
    protected $type;
    protected $wantReply;

    public function __construct($senderChannel, $type, $wantReply)
    {
        $this->channel      = $senderChannel;
        $this->type         = $type;
        $this->wantReply    = $wantReply;
    }

    public static function getMessageId()
    {
        return 98;
    }

    public function serialize(Encoder $encoder)
    {
        $encoder->encodeUint32($this->channel);
        $encoder->encodeString($this->type);
        $encoder->encodeBoolean($this->wantReply);
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
                $decoder->decodeUint32(),
                $decoder->decodeString(),
                $decoder->decodeBoolean()
            ),
            static::unserializeSub($decoder)
        );
        return $reflector->newInstanceArgs($args);
    }

    public function getType()
    {
        return $this->type;
    }

    public function getChannel()
    {
        return $this->channel;
    }

    public function wantsReply()
    {
        return $this->wantReply;
    }
}
