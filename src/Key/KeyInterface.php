<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Key;

/**
 * Interface for a public key algorithm.
 */
interface KeyInterface extends \fpoirotte\Pssht\Algorithms\AlgorithmInterface
{
    /**
     * Serialize a key.
     *
     *  \param fpoirotte::Pssht::Wire::Encoder $encoder
     *      Encoder to use to serialize the key.
     *
     *  \retval string
     *      Serialized representation of the key.
     */
    public function serialize(\fpoirotte\Pssht\Wire\Encoder $encoder);

    public static function unserialize(\fpoirotte\Pssht\Wire\Decoder $decoder, $private = null);

    /**
     * Sign a message using the key.
     *
     *  \param string $message
     *      Message to sign.
     *
     *  \retval string
     *      Message signature.
     */
    public function sign($message);

    /**
     * Check the signature for a message.
     *
     *  \param string $message
     *      Signed message.
     *
     *  \param string $signature
     *      Signature to check.
     *
     *  \retval bool
     *      \b true if the signature is valid for
     *      the given message, \b false otherwise.
     */
    public function check($message, $signature);
}
