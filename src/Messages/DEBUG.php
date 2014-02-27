<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Messages;

/**
 * SSH_MSG_DEBUG message (RFC 4253).
 */
class DEBUG implements \Clicky\Pssht\MessageInterface
{
    /// Whether to always display the message or not.
    protected $alwaysDisplay;

    /// Actual debug message.
    protected $message;

    /// Language the debug message is written into (from RFC 3066).
    protected $language;


    /**
     * Construct a new debug message.
     *
     *  \param bool $alwaysDisplay
     *      Indicates whether the debug message should always be shown
     *      to users (\b true) or if it may be hidden (\b false).
     *
     *  \param string $message
     *      The actual debug message, in ISO-10646 UTF-8 encoding.
     *
     *  \param string $language
     *      Language tag the debug message is written into,
     *      in RFC 3066 format.
     */
    public function __construct($alwaysDisplay, $message, $language)
    {
        if (!is_bool($alwaysDisplay)) {
            throw new \InvalidArgumentException();
        }
        if (!is_string($message)) {
            throw new \InvalidArgumentException();
        }
        if (!is_string($language)) {
            throw new \InvalidArgumentException();
        }

        $this->alwaysDisplay    = $alwaysDisplay;
        $this->message          = $message;
        $this->language         = $language;
    }

    public static function getMessageId()
    {
        return 4;
    }

    public function serialize(\Clicky\Pssht\Wire\Encoder $encoder)
    {
        $encoder->encodeBoolean($this->alwaysDisplay);
        $encoder->encodeString($this->message);
        $encoder->encodeString($this->language);
        return $this;
    }

    public static function unserialize(\Clicky\Pssht\Wire\Decoder $decoder)
    {
        return new static(
            $decoder->decodeBoolean(),
            $decoder->decodeString(),
            $decoder->decodeString()
        );
    }

    /**
     * Retrieve the flag indicating whether this debug message
     * should always be displayed to users or not.
     *
     *  \retval bool
     *      \b true if the message should always be displayed,
     *      \b false otherwise.
     */
    public function mustAlwaysDisplay()
    {
        return $this->alwaysDisplay;
    }

    /**
     * Get the actual debug message.
     *
     *  \retval string
     *      Debug message.
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get the language for the debug message.
     *
     *  \retval string
     *      Language tag the debug message is written into,
     *      in RFC 3066 format.
     */
    public function getLanguage()
    {
        return $this->language;
    }
}
