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
 * SSH_MSG_KEX_ECDH_INIT message (RFC 5656),
 * specialized for Curve25519.
 */
class Curve25519 implements \fpoirotte\Pssht\Messages\MessageInterface
{
    /// Client's ephemeral public key as a string.
    protected $Q;


    /**
     * Construct a new SSH_MSG_KEX_ECDH_INIT message.
     *
     *  \param string $Q
     *      The client's ephemeral public key.
     */
    public function __construct($Q) {
        $this->Q = $Q;
    }

    public static function getMessageId()
    {
        return 30;
    }

    public function serialize(\fpoirotte\Pssht\Wire\Encoder $encoder)
    {
        $encoder->encodeString($this->Q);
        return $this;
    }

    public static function unserialize(\fpoirotte\Pssht\Wire\Decoder $decoder)
    {
        $pubkey = $decoder->decodeString();
        if (strlen($pubkey) !== 32) {
            throw new \InvalidArgumentException();
        }
        return new static($pubkey);
    }

    /**
     * Get the client's ephemeral public key.
     *
     *  \retval string
     *      The client's ephemeral public key.
     */
    public function getQ()
    {
        return $this->Q;
    }
}
