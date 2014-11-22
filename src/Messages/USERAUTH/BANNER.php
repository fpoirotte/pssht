<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Messages\USERAUTH;

/**
 * SSH_MSG_USERAUTH_BANNER message (RFC 4252).
 */
class BANNER implements \fpoirotte\Pssht\MessageInterface
{
    /// Banner to display.
    protected $message;

    /// Language the banner is written into, in RFC 3066 format.
    protected $language;


    /**
     * Construct a new SSH_MSG_USERAUTH_BANNER message.
     *
     *  \param string $message
     *      Banner to display.
     *
     *  \param string $language
     *      (optional) Language for the banner, in RFC 3066 format.
     *      If omitted, the banner is assumed to be language-neutral.
     */
    public function __construct($message, $language = '')
    {
        if (!is_string($message)) {
            throw new \InvalidArgumentException();
        }
        if (!is_string($language)) {
            throw new \InvalidArgumentException();
        }

        $this->message  = $message;
        $this->language = $language;
    }

    public static function getMessageId()
    {
        return 53;
    }

    public function serialize(\fpoirotte\Pssht\Wire\Encoder $encoder)
    {
        $encoder->encodeString($this->message);
        $encoder->encodeString($this->language);
        return $this;
    }

    public static function unserialize(\fpoirotte\Pssht\Wire\Decoder $decoder)
    {
        return new static(
            $decoder->decodeString(),
            $decoder->decodeString()
        );
    }

    /**
     * Get the banner.
     *
     *  \retval string
     *      Banner to dislay.
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get the language for the banner.
     *
     *  \retval string
     *      Language for the banner, in RFC 3066 format.
     *
     *  \note
     *      An empty string is returned if the language
     *      is unknown or irrelevant (language-neutral
     *      banner).
     */
    public function getLanguage()
    {
        return $this->language;
    }
}
