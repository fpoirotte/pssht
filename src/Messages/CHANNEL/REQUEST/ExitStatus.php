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

class ExitStatus extends Base
{
    protected $status;

    public function __construct($channel, $type, $wantReply, $status)
    {
        parent::__construct($channel, $type, $wantReply);
        $this->status = $status;
    }

    public function serialize(Encoder $encoder)
    {
        parent::serialize($encoder);
        $encoder->encodeUint32($this->status);
    }

    protected static function unserializeSub(Decoder $decoder)
    {
        return array($decoder->decodeUint32());
    }

    public function getStatus()
    {
        return $this->status;
    }
}
