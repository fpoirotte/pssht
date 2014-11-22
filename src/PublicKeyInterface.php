<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht;

/**
 * Interface for a public key algorithm.
 */
interface PublicKeyInterface extends AlgorithmInterface
{
    /**
     * Initialize a new private key.
     *
     *  \param string $pem
     *      Either a private key encoded in PEM format
     *      or a path to a key encoded in PEM format
     *      using the syntax: "file:///path/to/key.pem".
     *
     *  \param string $passphrase
     *      (optional) Passphrase for the private key.
     *
     *  \retval PublicKeyInterface
     *      Private key as loaded from the data/file.
     */
    public static function loadPrivate($pem, $passphrase = '');

    /**
     * Initialize a new public key.
     *
     *  \param string $b64
     *      Base64-encoded representation
     *      of the public key to load.
     *
     *  \retval PublicKeyInterface
     *      Public key as loaded from the data.
     */
    public static function loadPublic($b64);

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
