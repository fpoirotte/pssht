<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Messages\KEX\ECDH\INIT;

/**
 * SSH_MSG_KEX_ECDH_INIT message (RFC 5656).
 */
abstract class RFC5656 implements \fpoirotte\Pssht\MessageInterface
{
    /// Client's ephemeral public key as an EC Point.
    protected $Q;


    /**
     * Construct a new SSH_MSG_KEX_ECDH_INIT message.
     *
     *  \param fpoirotte::Pssht::ECC::Point $Q
     *      EC Point representing the client's ephemeral public key.
     */
    public function __construct(
        \fpoirotte\Pssht\ECC\Point $Q
    ) {
        $this->Q = $Q;
    }

    public static function getMessageId()
    {
        return 30;
    }

    public function serialize(\fpoirotte\Pssht\Wire\Encoder $encoder)
    {
        $encoder->encodeString($this->Q->serialize(static::getCurve()));
        return $this;
    }

    public static function unserialize(\fpoirotte\Pssht\Wire\Decoder $decoder)
    {
        $point = \fpoirotte\Pssht\ECC\Point::unserialize(
            static::getCurve(),
            $decoder->decodeString()
        );
        return new static($point);
    }

    /**
     * Get the client's ephemeral public key.
     *
     *  \retval fpoirotte::Pssht::ECC::Point
     *      EC Point representing the client's
     *      ephemeral public key.
     */
    public function getQ()
    {
        return $this->Q;
    }
}
