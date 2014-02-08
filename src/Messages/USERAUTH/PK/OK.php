<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Messages\USERAUTH\PK;

use Clicky\Pssht\Wire\Encoder;
use Clicky\Pssht\Wire\Decoder;

class OK implements \Clicky\Pssht\MessageInterface
{
    protected $algorithm;
    protected $key;

    public function __construct($algorithm, $key)
    {
        if (!is_string($algorithm)) {
            throw new \InvalidArgumentException();
        }
        if (!is_string($key)) {
            throw new \InvalidArgumentException();
        }

        $this->algorithm    = $algorithm;
        $this->key          = $key;
    }

    public static function getMessageId()
    {
        return 60;
    }

    public function serialize(Encoder $encoder)
    {
        $encoder->encodeString($this->algorithm);
        $encoder->encodeString($this->key);
    }

    public static function unserialize(Decoder $decoder)
    {
        return new static(
            $decoder->decodeString(),
            $decoder->decodeString()
        );
    }

    public function getAlgorithm()
    {
        return $this->algorithm;
    }

    public function getKey()
    {
        return $this->key;
    }
}
