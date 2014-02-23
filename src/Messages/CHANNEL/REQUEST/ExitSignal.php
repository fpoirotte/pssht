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
 * for the "exit-signal" request type.
 */
class ExitSignal extends Base
{
    protected $signal;
    protected $coreDumped;
    protected $error;
    protected $language;

    public function __construct($channel, $type, $wantReply, $signal, $coreDumped, $error, $language)
    {
        parent::__construct($channel, $type, $wantReply);
        $this->signal       = $signal;
        $this->coreDumped   = $coreDumped;
        $this->error        = $error;
        $this->language     = $language;
    }

    public function serialize(Encoder $encoder)
    {
        parent::serialize($encoder);
        $encoder->encodeString($this->signal);
        $encoder->encodeBoolean($this->coreDumped);
        $encoder->encodeString($this->error);
        $encoder->encodeString($this->language);
        return $this;
    }

    protected static function unserializeSub(Decoder $decoder)
    {
        return array(
            $decoder->decodeString(),
            $decoder->decodeBoolean(),
            $decoder->decodeString(),
            $decoder->decodeString(),
        );
    }
}
