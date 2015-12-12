<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Messages\USERAUTH\PK;

/**
 * SSH_MSG_USERAUTH_PK_OK message (RFC 4252).
 */
class OK implements \fpoirotte\Pssht\Messages\MessageInterface
{
    /// Public key algorithm in use (eg. "ssh-rsa" or "ssh-dss").
    protected $algorithm;

    /// Key blob.
    protected $key;


    /**
     * Construct a new SSH_MSG_USERAUTH_PK_OK message,
     * indicating partial success of a public key authentication.
     *
     *  \param string $algorithm
     *      Algorithm to use.
     *
     *  \param string $key
     *      Key blob.
     */
    public function __construct($algorithm, $key)
    {
        if (!is_string($algorithm)) {
            throw new \InvalidArgumentException();
        }
        if (!is_string($key)) {
            throw new \InvalidArgumentException();
        }

        $this->algorithm    = $algorithm;
        $this->key          = $key;
    }

    public static function getMessageId()
    {
        return 60;
    }

    public function serialize(\fpoirotte\Pssht\Wire\Encoder $encoder)
    {
        $encoder->encodeString($this->algorithm);
        $encoder->encodeString($this->key);
        return $this;
    }

    public static function unserialize(\fpoirotte\Pssht\Wire\Decoder $decoder)
    {
        return new static(
            $decoder->decodeString(),
            $decoder->decodeString()
        );
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
}
