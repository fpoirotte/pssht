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
 * for the "hostbased" method.
 */
class HostBased extends \Clicky\Pssht\Messages\USERAUTH\REQUEST\Base
{
    /// Public key algorithm in use (eg. "ssh-rsa" or "ssh-dss").
    protected $algorithm;

    /// Key blob.
    protected $key;

    /// Remote hostname.
    protected $hostname;

    /// Remote login.
    protected $remoteUser;

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
     *  \param string $hostname
     *      Hostname the user is connecting from.
     *
     *  \param string $remoteUser
     *      User's login on the remote machine.
     *
     *  \param string $signature
     *      Signature proving ownership of the key.
     */
    public function __construct(
        $user,
        $service,
        $method,
        $algorithm,
        $key,
        $hostname,
        $remoteUser,
        $signature
    ) {
        if (!is_string($algorithm)) {
            throw new \InvalidArgumentException();
        }

        if (!is_string($key)) {
            throw new \InvalidArgumentException();
        }

        if (!is_string($hostname)) {
            throw new \InvalidArgumentException();
        }

        if (!is_string($remoteUser)) {
            throw new \InvalidArgumentException();
        }

        if (!is_string($signature)) {
            throw new \InvalidArgumentException();
        }

        parent::__construct($user, $service, $method);
        $this->algorithm    = $algorithm;
        $this->key          = $key;
        $this->hostname     = $hostname;
        $this->remoteUser   = $remoteUser;
        $this->signature    = $signature;
    }

    public function serialize(\Clicky\Pssht\Wire\Encoder $encoder)
    {
        parent::serialize($encoder);
        $encoder->encodeString($this->algorithm);
        $encoder->encodeString($this->key);
        $encoder->encodeString($this->hostname);
        $encoder->encodeString($this->remoteUser);

        // Special handling of the signature.
        $encoder2 = new \Clicky\Pssht\Wire\Encoder();
        $encoder2->encodeString($this->algorithm);
        $encoder2->encodeString($this->signature);
        $encoder->encodeString($encoder2->getBuffer()->get(0));

        return $this;
    }

    protected static function unserializeSub(\Clicky\Pssht\Wire\Decoder $decoder)
    {
        $algorithm = $decoder->decodeString();
        $res = array(
            $algorithm,
            $decoder->decodeString(),
            $decoder->decodeString(),
            $decoder->decodeString(),
        );

        // Special handling for signature.
        $decoder2 = new \Clicky\Pssht\Wire\Decoder(
            new \Clicky\Pssht\Buffer($decoder->decodeString())
        );
        if ($decoder2->decodeString() !== $algorithm) {
            throw new \InvalidArgumentException();
        }
        $res[] = $decoder2->decodeString();

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
     * Get the hostname of the remote machine
     * the user is connecting from.
     *
     *  \retval string
     *      Remote hostname.
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * Get the login of the user on the remote machine.
     *
     *  \retval string
     *      Remote login.
     */
    public function getRemoteUser()
    {
        return $this->remoteUser;
    }

    /**
     * Get the signature proving key ownership.
     *
     *  \retval string
     *      Signature proving key ownership.
     */
    public function getSignature()
    {
        return $this->signature;
    }
}
