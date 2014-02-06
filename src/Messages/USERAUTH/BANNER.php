<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Messages\USERAUTH;

use Clicky\Pssht\MessageInterface;
use Clicky\Pssht\Wire\Encoder;
use Clicky\Pssht\Wire\Decoder;

class BANNER implements MessageInterface
{
    protected $message;
    protected $language;

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

    public function serialize(Encoder $encoder)
    {
        $encoder->encodeString($this->message);
        $encoder->encodeString($this->language);
    }

    public static function unserialize(Decoder $decoder)
    {
        return new static(
            $decoder->decodeString(),
            $decoder->decodeString()
        );
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
