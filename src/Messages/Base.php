<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Messages;

/**
 * Abstract SSH message.
 */
abstract class Base implements \fpoirotte\Pssht\MessageInterface
{
    /// Construct the message.
    final public function __construct()
    {
    }

    final public function serialize(\fpoirotte\Pssht\Wire\Encoder $encoder)
    {
        return $this;
    }

    final public static function unserialize(\fpoirotte\Pssht\Wire\Decoder $encoder)
    {
        return new static();
    }
}
