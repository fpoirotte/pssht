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
 * for the "exit-signal" request type.
 */
class ExitSignal extends \Clicky\Pssht\Messages\CHANNEL\REQUEST\Base
{
    /// Name of the signal that caused the process to exit.
    protected $signal;

    /// Whether a core file was dumped or not.
    protected $coreDumped;

    /// Textual explanation of the error.
    protected $error;

    /// Language the error message in written into, in RFC 3066 format.
    protected $language;


    /**
     * Construct a new SSH_MSG_CHANNEL_REQUEST message
     * for the "exit-signal" type.
     *
     *  \copydetails Base::__construct
     *
     *  \param string $signal
     *      Signal name, without the "SIG" prefix.
     *
     *  \param bool $coreDumped
     *      Whether a core file was dumped (\b true)
     *      or not (\b false).
     *
     *  \param string $error
     *      Text explaining the error in more details.
     *
     *  \param string $language
     *      Language the error message is written into,
     *      in RFC 3066 format.
     */
    public function __construct($channel, $type, $wantReply, $signal, $coreDumped, $error, $language)
    {
        parent::__construct($channel, $type, $wantReply);
        $this->signal       = $signal;
        $this->coreDumped   = $coreDumped;
        $this->error        = $error;
        $this->language     = $language;
    }

    public function serialize(\Clicky\Pssht\Wire\Encoder $encoder)
    {
        parent::serialize($encoder);
        $encoder->encodeString($this->signal);
        $encoder->encodeBoolean($this->coreDumped);
        $encoder->encodeString($this->error);
        $encoder->encodeString($this->language);
        return $this;
    }

    protected static function unserializeSub(\Clicky\Pssht\Wire\Decoder $decoder)
    {
        return array(
            $decoder->decodeString(),
            $decoder->decodeBoolean(),
            $decoder->decodeString(),
            $decoder->decodeString(),
        );
    }
}
