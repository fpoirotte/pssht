<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht;

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
     *  \param Encoder $encoder
     *      Encoder to use to serialize the key.
     *
     *  \retval string
     *      Serialized representation of the key.
     */
    public function serialize(\Clicky\Pssht\Wire\Encoder $encoder);

    /**
     * Sign a message using the key.
     *
     *  \param string $message
     *      Message to sign.
     *
     *  \param bool $raw_output
     *      (optional) Whether to return the signature
     *      in raw form (\c true) or in hexadecimal
     *      form (\c false).
     *
     *  \retval string
     *      Message signature in raw or hexadecimal form
     *      depending on the value of $raw_output.
     */
    public function sign($message, $raw_output = false);

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
     *      \c true if the signature is valid for
     *      the given message, \c false otherwise.
     */
    public function check($message, $signature);
}
