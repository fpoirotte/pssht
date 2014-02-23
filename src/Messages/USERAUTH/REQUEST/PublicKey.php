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
 * for the "publickey" method.
 */
class PublicKey extends \Clicky\Pssht\Messages\USERAUTH\REQUEST\Base
{
    protected $algorithm;
    protected $key;
    protected $signature;

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

    public function serialize(Encoder $encoder)
    {
        parent::serialize($encoder);
        $encoder->encodeBoolean($this->signature !== null);
        $encoder->encodeString($this->algorithm);
        $encoder->encodeString($this->key);
        if ($this->signature !== null) {
            $encoder2 = new Encoder();
            $encoder2->encodeString($this->algorithm);
            $encoder2->encodeString($this->signature);
            $encoder->encodeString($encoder2->getBuffer()->get(0));
        }
        return $this;
    }

    protected static function unserializeSub(Decoder $decoder)
    {
        $signature  = $decoder->decodeBoolean();
        $algorithm  = $decoder->decodeString();
        $res        = array(
            $algorithm,
            $decoder->decodeString(),
        );

        if ($signature === true) {
            $decoder2 = new Decoder(new \Clicky\Pssht\Buffer($decoder->decodeString()));
            if ($decoder2->decodeString() !== $algorithm) {
                throw new \InvalidArgumentException();
            }
            $res[] = $decoder2->decodeString();
        }
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

    public function getSignature()
    {
        return $this->signature;
    }
}
