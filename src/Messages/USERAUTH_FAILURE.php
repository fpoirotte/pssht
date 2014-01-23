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

use Clicky\Pssht\MessageInterface;
use Clicky\Pssht\Wire\Encoder;
use Clicky\Pssht\Wire\Decoder;

class       USERAUTH_FAILURE
implements  MessageInterface
{
    const MESSAGE_ID = 51;

    protected $_methods;
    protected $_partial;

    public function __construct($methods, $partial)
    {
        if (!is_bool($partial))
            throw new \InvalidArgumentException();
        if (!is_array($methods))
            throw new \InvalidArgumentException();
        foreach ($methods as $method) {
            if (!is_string($method))
                throw new \InvalidArgumentException();
        }

        $this->_methods = $methods;
        $this->_partial = $partial;
    }

    public function serialize(Encoder $encoder)
    {
        $encoder->encode_name_list($this->_methods);
        $encoder->encode_boolean($this->_partial);
    }

    static public function unserialize(Decoder $decoder)
    {
        return new self(
            $decoder->decode_name_list(),
            $decoder->decode_boolean()
        );
    }
}

