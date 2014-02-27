<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Messages\USERAUTH\REQUEST;

/**
 * SSH_MSG_USERAUTH_REQUEST message (RFC 4252)
 * for the "publickey" method.
 */
class PublicKey extends \Clicky\Pssht\Messages\USERAUTH\REQUEST\Base
{
    /// Public key algorithm in use (eg. "ssh-rsa" or "ssh-dss").
    protected $algorithm;

    /// Key blob.
    protected $key;

    /// Signature to prove key ownership.
    protected $signature;


    /**
     *  \copydetails Base::__construct
     *
     *  \param string $algorithm
     *      Public key algorithm to use.
     *
     *  \param string $key
     *      Key blob.
     *
     *  \param string $signature
     *      (optional) Signature proving ownership of the key.
     *      This parameter MUST be omitted during the first phase
     *      of authentication and MUST be given during the second
     *      phase.
     */
    public function __construct($user, $service, $method, $algorithm, $key, $signature = null)
    {
        if (!is_string($algorithm)) {
            throw new \InvalidArgumentException();
        }

        if (!is_string($key)) {
            throw new \InvalidArgumentException();
        }

        if (!is_string($signature) && $signature !== null) {
            throw new \InvalidArgumentException();
        }

        parent::__construct($user, $service, $method);
        $this->algorithm    = $algorithm;
        $this->key          = $key;
        $this->signature    = $signature;
    }

    public function serialize(\Clicky\Pssht\Wire\Encoder $encoder)
    {
        parent::serialize($encoder);
        $encoder->encodeBoolean($this->signature !== null);
        $encoder->encodeString($this->algorithm);
        $encoder->encodeString($this->key);
        if ($this->signature !== null) {
            $encoder2 = new \Clicky\Pssht\Wire\Encoder();
            $encoder2->encodeString($this->algorithm);
            $encoder2->encodeString($this->signature);
            $encoder->encodeString($encoder2->getBuffer()->get(0));
        }
        return $this;
    }

    protected static function unserializeSub(\Clicky\Pssht\Wire\Decoder $decoder)
    {
        $signature  = $decoder->decodeBoolean();
        $algorithm  = $decoder->decodeString();
        $res        = array(
            $algorithm,
            $decoder->decodeString(),   // key
        );

        if ($signature === true) {
            $decoder2 = new \Clicky\Pssht\Wire\Decoder(
                new \Clicky\Pssht\Buffer($decoder->decodeString())
            );
            if ($decoder2->decodeString() !== $algorithm) {
                throw new \InvalidArgumentException();
            }
            $res[] = $decoder2->decodeString();
        }
        return $res;
    }

    /**
     * Get public key algorithm in use.
     *
     *  \retval string
     *      Public key algorithm in use.
     */
    public function getAlgorithm()
    {
        return $this->algorithm;
    }

    /**
     * Get the key blob.
     *
     *  \retval string
     *      Key blob.
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Get the signature proving key ownership.
     *
     *  \retval string
     *      Signature proving key ownership.
     *
     *  \retval null
     *      No signature data was available.
     *
     *  \note
     *      The SSH protocol uses a two-steps method
     *      for public key authentication.
     *      The signature will be \b null during the first step.
     */
    public function getSignature()
    {
        return $this->signature;
    }
}
