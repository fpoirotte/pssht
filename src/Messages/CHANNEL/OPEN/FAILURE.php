<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Messages\CHANNEL\OPEN;

/**
 * SSH_MSG_CHANNEL_OPEN_FAILURE message (RFC 4254).
 */
class FAILURE extends \fpoirotte\Pssht\Messages\CHANNEL\Base
{
    /// The requested action was administratively prohibited.
    const SSH_OPEN_ADMINISTRATIVELY_PROHIBITED  = 1;

    /// The connection failed.
    const SSH_OPEN_CONNECT_FAILED               = 2;

    /// The requested channel type is unsupported.
    const SSH_OPEN_UNKNOWN_CHANNEL_TYPE         = 3;

    /// The requested action was aborted due to a resource shortage.
    const SSH_OPEN_RESOURCE_SHORTAGE            = 4;


    /// Reason for the failure (as a code).
    protected $reasonCode;

    /// Reason for the failure (as a human-readable description).
    protected $reasonMessage;

    /// Language the message is written into, in RFC 3066 format.
    protected $language;


    /**
     * Construct a new SSH_MSG_CHANNEL_OPEN_FAILURE message.
     *
     *  \copydetails Base::__construct
     *
     *  \param int $reasonCode
     *      Reason for the failure, as a code.
     *
     *  \param string $reasonMessage
     *      Reason for the failure, as a human-readable description
     *      in RFC 3066 format.
     *
     *  \param string $language
     *      (optional) Language the message in written into,
     *      in RFC 3066 format. If omitted, the description
     *      is assumed to be language-neutral.
     */
    public function __construct($channel, $reasonCode, $reasonMessage, $language = '')
    {
        parent::__construct($channel);
        $this->reasonCode       = $reasonCode;
        $this->reasonMessage    = $reasonMessage;
        $this->language         = $language;
    }

    public static function getMessageId()
    {
        return 92;
    }

    public function serialize(\fpoirotte\Pssht\Wire\Encoder $encoder)
    {
        parent::serialize($encoder);
        $encoder->encodeUint32($this->reasonCode);
        $encoder->encodeString($this->reasonMessage);
        $encoder->encodeString($this->language);
        return $this;
    }

    public static function unserialize(\fpoirotte\Pssht\Wire\Decoder $decoder)
    {
        return new static(
            $decoder->decodeUint32(),   // channel
            $decoder->decodeUint32(),
            $decoder->decodeString(),
            $decoder->decodeString()
        );
    }
}
