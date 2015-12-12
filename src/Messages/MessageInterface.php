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
 * Interface for an SSH message.
 */
interface MessageInterface
{
    /**
     * Retrieve the message's identifier.
     *
     *  \retval int
     *      Message identifier.
     */
    public static function getMessageId();

    /**
     * Serialize the message.
     *
     *  \param fpoirotte::Pssht::Wire::Encoder $encoder
     *      Encoder to use to perform serialization.
     *
     *  \retval MessageInterface
     *      Returns this message.
     */
    public function serialize(\fpoirotte\Pssht\Wire\Encoder $encoder);

    /**
     * Unserialize some data into a message.
     *
     *  \param fpoirotte::Pssht::Wire::Decoder $decoder
     *      Decoder to use to perform unserialization.
     *
     *  \retval MessageInterface
     *      Unserialized message.
     */
    public static function unserialize(\fpoirotte\Pssht\Wire\Decoder $decoder);
}
