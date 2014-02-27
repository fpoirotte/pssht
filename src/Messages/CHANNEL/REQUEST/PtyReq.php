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

/**
 * SSH_MSG_CHANNEL_REQUEST message (RFC 4254)
 * for the "pty-req" request type.
 */
class PtyReq extends \Clicky\Pssht\Messages\CHANNEL\REQUEST\Base
{
    /// Terminal to emulate.
    protected $term;

    /// Terminal width in characters.
    protected $widthInCols;

    /// Terminal height in rows.
    protected $heightInRows;

    /// Terminal width in pixels.
    protected $widthInPixels;

    /// Terminal height in pixels.
    protected $heightInPixels;

    /// Encoded terminal modes.
    protected $modes;


    /**
     * Construct a new "pty-req" SSH_MSG_CHANNEL_REQUEST message.
     *
     *  \copydetails Base::__construct
     *
     *  \param string $term
     *      Terminal to emulate.
     *
     *  \param int $widthInCols
     *      Terminal width in characters.
     *
     *  \param int $heightInRows
     *      Terminal height in rows.
     *
     *  \param int $widthInPixels
     *      Terminal width in pixels.
     *
     *  \param int $heightInPixels
     *      Terminal height in pixels.
     *
     *  \param string $modes
     *      Encoded terminal modes.
     */
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

    public function serialize(\Clicky\Pssht\Wire\Encoder $encoder)
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

    protected static function unserializeSub(\Clicky\Pssht\Wire\Decoder $decoder)
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
