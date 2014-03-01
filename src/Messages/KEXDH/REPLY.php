<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Messages\KEXDH;

/**
 * SSH_MSG_KEXDH_REPLY message (RFC 4253).
 */
class REPLY implements \Clicky\Pssht\MessageInterface
{
    /// Exchange hash.
    protected $H;

    /// Server's public exponent as a GMP resource.
    protected $f;

    /// Shared secret.
    protected $K;

    /// Server's public host key.
    protected $K_S;

    /// Client's contribution to the Diffie-Hellman Key Exchange.
    protected $kexDHInit;

    /// Key exchange algorithm to use.
    protected $kexAlgo;

    /// Algorithms supported by the server.
    protected $serverKEX;

    /// Algorithms supported by the client.
    protected $clientKEX;

    /// Server's identification string.
    protected $serverIdent;

    /// Client's identification string.
    protected $clientIdent;


    /**
     * Construct a new SSH_MSG_KEXDH_REPLY message.
     *
     *  \param Clicky::Pssht::Messages::KEXDH::INIT $kexDHInit
     *      Client's contribution to the Diffie-Hellman Key Exchange.
     *
     *  \param Clicky::Pssht::PublicKeyInterface $key
     *      Server's public key.
     *
     *  \param Clicky::Pssht::EncryptionInterface $encryptionAlgo
     *      Encryption algorithm in use.
     *
     *  \param Clicky::Pssht::KEXInterface $kexAlgo
     *      Key exchange algorithm to use.
     *
     *  \param Clicky::Pssht::Messages::KEXINIT $serverKEX
     *      Algorithms supported by the server.
     *
     *  \param Clicky::Pssht::Messages::KEXINIT $clientKEX
     *      Algorithms supported by the client.
     *
     *  \param string $serverIdent
     *      Server's identification string
     *
     *  \param string $clientIdent
     *      Client's identification string
     */
    public function __construct(
        \Clicky\Pssht\Messages\KEXDH\INIT $kexDHInit,
        \Clicky\Pssht\PublicKeyInterface $key,
        \Clicky\Pssht\EncryptionInterface $encryptionAlgo,
        \Clicky\Pssht\KEXInterface $kexAlgo,
        \Clicky\Pssht\Messages\KEXINIT $serverKEX,
        \Clicky\Pssht\Messages\KEXINIT $clientKEX,
        $serverIdent,
        $clientIdent
    ) {
        if (!is_string($serverIdent)) {
            throw new \InvalidArgumentException();
        }

        if (!is_string($clientIdent)) {
            throw new \InvalidArgumentException();
        }

        $keyLength          = min(20, max($encryptionAlgo->getKeySize(), 16));
        $randBytes          = openssl_random_pseudo_bytes(2 * $keyLength);
        $y                  = gmp_init(bin2hex($randBytes), 16);
        $prime              = gmp_init($kexAlgo::getPrime(), 16);
        $this->f            = gmp_powm($kexAlgo::getGenerator(), $y, $prime);
        $this->K            = gmp_powm($kexDHInit->getE(), $y, $prime);
        $this->K_S          = $key;
        $this->kexDHInit    = $kexDHInit;
        $this->kexAlgo      = $kexAlgo;
        $this->serverKEX    = $serverKEX;
        $this->clientKEX    = $clientKEX;
        $this->serverIdent  = $serverIdent;
        $this->clientIdent  = $clientIdent;

        $msgId  = chr(\Clicky\Pssht\Messages\KEXINIT::getMessageId());
        // $sub is used to create the structure for the hashing function.
        $sub    = new \Clicky\Pssht\Wire\Encoder(new \Clicky\Pssht\Buffer());
        $this->K_S->serialize($sub);
        $K_S    = $sub->getBuffer()->get(0);
        $sub->encodeString($this->clientIdent);
        $sub->encodeString($this->serverIdent);
        // $sub2 is used to compute the value
        // of various fields inside the structure.
        $sub2   = new \Clicky\Pssht\Wire\Encoder(new \Clicky\Pssht\Buffer());
        $sub2->encodeBytes($msgId); // Add message identifier.
        $this->clientKEX->serialize($sub2);
        $sub->encodeString($sub2->getBuffer()->get(0));
        $sub2->encodeBytes($msgId); // Add message identifier.
        $this->serverKEX->serialize($sub2);
        $sub->encodeString($sub2->getBuffer()->get(0));
        $sub->encodeString($K_S);
        $sub->encodeMpint($this->kexDHInit->getE());
        $sub->encodeMpint($this->f);
        $sub->encodeMpint($this->K);

        $logging    = \Plop::getInstance();
        $origData   = $sub->getBuffer()->get(0);
        $data       = wordwrap(bin2hex($origData), 4, ' ', true);
        $data       = wordwrap($data, 32 + 7, PHP_EOL, true);
        $logging->debug("Signature payload:\r\n%s", array($data));

        $this->H    = $this->kexAlgo->hash($origData);
    }

    public static function getMessageId()
    {
        return 31;
    }

    public function serialize(\Clicky\Pssht\Wire\Encoder $encoder)
    {
        $sub    = new \Clicky\Pssht\Wire\Encoder(new \Clicky\Pssht\Buffer());
        $this->K_S->serialize($sub);

        $encoder->encodeString($sub->getBuffer()->get(0));
        $encoder->encodeMpint($this->f);

        $sub->encodeString($this->K_S->getName());
        $sub->encodeString($this->K_S->sign($this->H, true));
        $encoder->encodeString($sub->getBuffer()->get(0));
        return $this;
    }

    public static function unserialize(\Clicky\Pssht\Wire\Decoder $decoder)
    {
        /// @FIXME: we should at least try a little...
        throw new \RuntimeException();
    }

    /**
     * Get the shared secret.
     *
     *  \retval string
     *      Shared secret generated from this Diffie Hellman
     *      key exchange.
     */
    public function getSharedSecret()
    {
        return $this->K;
    }

    /**
     * Get the exchange hash.
     *
     *  \retval string
     *      Exchange hash.
     */
    public function getExchangeHash()
    {
        return $this->H;
    }
}
