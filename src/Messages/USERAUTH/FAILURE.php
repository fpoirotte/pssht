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

class FAILURE implements MessageInterface
{
    protected $methods;
    protected $partial;

    public function __construct(array $methods, $partial)
    {
        if (!is_bool($partial)) {
            throw new \InvalidArgumentException();
        }

        foreach ($methods as $method) {
            if (!is_string($method)) {
                throw new \InvalidArgumentException();
            }
        }

        $this->methods = $methods;
        $this->partial = $partial;
    }

    public static function getMessageId()
    {
        return 51;
    }

    public function serialize(Encoder $encoder)
    {
        $encoder->encodeNameList($this->methods);
        $encoder->encodeBoolean($this->partial);
    }

    public static function unserialize(Decoder $decoder)
    {
        return new static(
            $decoder->decodeNameList(),
            $decoder->decodeBoolean()
        );
    }
}
