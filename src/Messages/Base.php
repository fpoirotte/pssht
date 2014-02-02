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

abstract class Base implements \Clicky\Pssht\MessageInterface
{
    final public function __construct()
    {
    }

    final public function serialize(\Clicky\Pssht\Wire\Encoder $encoder)
    {
    }

    final public static function unserialize(\Clicky\Pssht\Wire\Decoder $encoder)
    {
        return new static();
    }
}
