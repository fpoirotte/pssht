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

class   Exec
extends Base
{
    protected $_command;

    public function __construct($channel, $type, $wantReply, $command)
    {
        parent::__construct($channel, $type, $wantReply);
        $this->_command = $command;
    }

    public function serialize(Encoder $encoder)
    {
        parent::serialize($encoder);
        $encoder->encode_string($this->_command);
    }

    static protected function _unserialize(Decoder $decoder)
    {
        return array($decoder->decode_string());
    }

    public function getCommand()
    {
        return $this->_command;
    }
}

