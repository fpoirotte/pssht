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

use Clicky\Pssht\Messages\CHANNEL\REQUEST\Base;
use Clicky\Pssht\Wire\Encoder;
use Clicky\Pssht\Wire\Decoder;

/**
 * SSH_MSG_CHANNEL_REQUEST message (RFC 4254)
 * for the "exec" request type.
 */
class Exec extends Base
{
    protected $command;

    public function __construct($channel, $type, $wantReply, $command)
    {
        parent::__construct($channel, $type, $wantReply);
        $this->command = $command;
    }

    public function serialize(Encoder $encoder)
    {
        parent::serialize($encoder);
        $encoder->encodeString($this->command);
        return $this;
    }

    protected static function unserializeSub(Decoder $decoder)
    {
        return array($decoder->decodeString());
    }

    public function getCommand()
    {
        return $this->command;
    }
}
