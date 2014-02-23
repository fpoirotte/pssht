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

use Clicky\Pssht\Wire\Encoder;
use Clicky\Pssht\Wire\Decoder;

/**
 * SSH_MSG_DEBUG message (RFC 4253).
 */
class DEBUG implements \Clicky\Pssht\MessageInterface
{
    protected $alwaysDisplay;
    protected $message;
    protected $language;

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

    public function serialize(Encoder $encoder)
    {
        $encoder->encodeBoolean($this->alwaysDisplay);
        $encoder->encodeString($this->message);
        $encoder->encodeString($this->language);
        return $this;
    }

    public static function unserialize(Decoder $decoder)
    {
        return new static(
            $decoder->decodeBoolean(),
            $decoder->decodeString(),
            $decoder->decodeString()
        );
    }

    public function mustAlwaysDisplay()
    {
        return $this->alwaysDisplay;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getLanguage()
    {
        return $this->language;
    }
}
