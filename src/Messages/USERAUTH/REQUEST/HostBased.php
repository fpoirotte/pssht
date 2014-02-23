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

use Clicky\Pssht\Wire\Encoder;
use Clicky\Pssht\Wire\Decoder;

/**
 * SSH_MSG_USERAUTH_REQUEST message (RFC 4252)
 * for the "hostbased" method.
 */
class HostBased extends \Clicky\Pssht\Messages\USERAUTH\REQUEST\Base
{
    protected $algorithm;
    protected $key;
    protected $hostname;
    protected $remoteUser;
    protected $signature;

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

    public function serialize(Encoder $encoder)
    {
        parent::serialize($encoder);
        $encoder->encodeString($this->algorithm);
        $encoder->encodeString($this->key);
        $encoder->encodeString($this->hostname);
        $encoder->encodeString($this->remoteUser);

        // Special handling of the signature.
        $encoder2 = new Encoder();
        $encoder2->encodeString($this->algorithm);
        $encoder2->encodeString($this->signature);
        $encoder->encodeString($encoder2->getBuffer()->get(0));

        return $this;
    }

    protected static function unserializeSub(Decoder $decoder)
    {
        $algorithm = $decoder->decodeString();
        $res = array(
            $algorithm,
            $decoder->decodeString(),
            $decoder->decodeString(),
            $decoder->decodeString(),
        );

        // Special handling for signature.
        $decoder2 = new Decoder(new \Clicky\Pssht\Buffer($decoder->decodeString()));
        if ($decoder2->decodeString() !== $algorithm) {
            throw new \InvalidArgumentException();
        }
        $res[] = $decoder2->decodeString();

        return $res;
    }

    public function getAlgorithm()
    {
        return $this->algorithm;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getHostname()
    {
        return $this->hostname;
    }

    public function getRemoteUser()
    {
        return $this->remoteUser;
    }

    public function getSignature()
    {
        return $this->signature;
    }
}
