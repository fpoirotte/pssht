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

use Clicky\Pssht\Messages\Base;
use Clicky\Pssht\Wire\Encoder;
use Clicky\Pssht\Wire\Decoder;

class   DEBUG
extends Base
{
    protected $_alwaysDisplay;
    protected $_message;
    protected $_language;

    public function __construct($alwaysDisplay, $message, $language)
    {
        if (!is_bool($alwaysDisplay))
            throw new \InvalidArgumentException();
        if (!is_string($message))
            throw new \InvalidArgumentException();
        if (!is_string($language))
            throw new \InvalidArgumentException();

        $this->_alwaysDisplay   = $alwaysDisplay;
        $this->_message         = $message;
        $this->_language        = $language;
    }

    static public function getMessageId()
    {
        return 4;
    }

    public function serialize(Encoder $encoder)
    {
        $encoder->encode_boolean($this->_alwaysDisplay);
        $encoder->encode_string($this->_message);
        $encoder->encode_string($this->_language);
    }

    static public function unserialize(Decoder $decoder)
    {
        return new self(
            $decoder->decode_boolean(),
            $decoder->decode_string(),
            $decoder->decode_string()
        );
    }

    public function mustAlwaysDisplay()
    {
        return $this->_alwaysDisplay;
    }

    public function getMessage()
    {
        return $this->_message;
    }

    public function getLanguage()
    {
        return $this->_language;
    }
}

