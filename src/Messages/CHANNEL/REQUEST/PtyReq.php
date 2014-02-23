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
 * for the "pty-req" request type.
 */
class PtyReq extends Base
{
    protected $term;
    protected $widthInCols;
    protected $heightInRows;
    protected $widthInPixels;
    protected $heightInPixels;
    protected $modes;

    public function __construct(
        $channel,
        $type,
        $wantReply,
        $term,
        $widthInCols,
        $heightInRows,
        $widthInPixels,
        $heightInPixels,
        $modes
    ) {
        parent::__construct($channel, $type, $wantReply);
        $this->term             = $term;
        $this->widthInCols      = $widthInCols;
        $this->heightInRows     = $heightInRows;
        $this->widthInPixels    = $widthInPixels;
        $this->heightInPixels   = $heightInPixels;
        $this->modes            = $modes;
    }

    public function serialize(Encoder $encoder)
    {
        parent::serialize($encoder);
        $encoder->encodeString($this->term);
        $encoder->encodeUint32($this->widthInCols);
        $encoder->encodeUint32($this->heightInRows);
        $encoder->encodeUint32($this->widthInPixels);
        $encoder->encodeUint32($this->heightInPixels);
        $encoder->encodeString($this->modes);
        return $this;
    }

    protected static function unserializeSub(Decoder $decoder)
    {
        return array(
            $decoder->decodeString(),
            $decoder->decodeUint32(),
            $decoder->decodeUint32(),
            $decoder->decodeUint32(),
            $decoder->decodeUint32(),
            $decoder->decodeString(),
        );
    }
}
