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
 * Abstract SSH message.
 */
abstract class Base implements \Clicky\Pssht\MessageInterface
{
    /// Construct the message.
    final public function __construct()
    {
    }

    final public function serialize(\Clicky\Pssht\Wire\Encoder $encoder)
    {
        return $this;
    }

    final public static function unserialize(\Clicky\Pssht\Wire\Decoder $encoder)
    {
        return new static();
    }
}
