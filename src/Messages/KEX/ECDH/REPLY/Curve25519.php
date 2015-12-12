<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Messages\KEX\ECDH\REPLY;

/**
 * SSH_MSG_KEX_ECDH_REPLY message (RFC 5656),
 * specialized for Curve25519.
 */
class Curve25519 implements \fpoirotte\Pssht\Messages\MessageInterface
{
    /// Exchange hash.
    protected $H;

    /// Server's ephemeral public key.
    protected $Q_S;

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
     *  \param fpoirotte::Pssht::Messages::KEX::ECDH::INIT::Curve25519 $kexDHInit
     *      Client's contribution to the Diffie-Hellman Key Exchange.
     *
     *  \param fpoirotte::Pssht::KeyInterface $key
     *      Server's public key.
     *
     *  \param fpoirotte::Pssht::EncryptionInterface $encryptionAlgo
     *      Encryption algorithm in use.
     *
     *  \param fpoirotte::Pssht::KEXInterface $kexAlgo
     *      Key exchange algorithm to use.
     *
     *  \param fpoirotte::Pssht::Messages::KEXINIT $serverKEX
     *      Algorithms supported by the server.
     *
     *  \param fpoirotte::Pssht::Messages::KEXINIT $clientKEX
     *      Algorithms supported by the client.
     *
     *  \param string $serverIdent
     *      Server's identification string
     *
     *  \param string $clientIdent
     *      Client's identification string
     */
    public function __construct(
        \fpoirotte\Pssht\Messages\KEX\ECDH\INIT\Curve25519 $kexDHInit,
        \fpoirotte\Pssht\Key\KeyInterface $key,
        \fpoirotte\Pssht\Encryption\EncryptionInterface $encryptionAlgo,
        \fpoirotte\Pssht\KEX\KEXInterface $kexAlgo,
        \fpoirotte\Pssht\Messages\KEXINIT $serverKEX,
        \fpoirotte\Pssht\Messages\KEXINIT $clientKEX,
        $serverIdent,
        $clientIdent
    ) {
        if (!is_string($serverIdent)) {
            throw new \InvalidArgumentException();
        }

        if (!is_string($clientIdent)) {
            throw new \InvalidArgumentException();
        }

        $curve              = \fpoirotte\Pssht\ECC\Curve25519::getInstance();
        $d_S                = openssl_random_pseudo_bytes(32);
        $this->Q_S          = $curve->getPublic($d_S);
        $Q_C                = $kexDHInit->getQ();
        $this->K            = gmp_init(bin2hex($curve->getShared($d_S, $Q_C)), 16);
        $this->K_S          = $key;
        $this->kexDHInit    = $kexDHInit;
        $this->kexAlgo      = $kexAlgo;
        $this->serverKEX    = $serverKEX;
        $this->clientKEX    = $clientKEX;
        $this->serverIdent  = $serverIdent;
        $this->clientIdent  = $clientIdent;

        $msgId  = chr(\fpoirotte\Pssht\Messages\KEXINIT::getMessageId());
        // $sub is used to create the structure for the hashing function.
        $sub    = new \fpoirotte\Pssht\Wire\Encoder(new \fpoirotte\Pssht\Buffer());
        $this->K_S->serialize($sub);
        $K_S    = $sub->getBuffer()->get(0);
        $sub->encodeString($this->clientIdent);
        $sub->encodeString($this->serverIdent);
        // $sub2 is used to compute the value
        // of various fields inside the structure.
        $sub2   = new \fpoirotte\Pssht\Wire\Encoder(new \fpoirotte\Pssht\Buffer());
        $sub2->encodeBytes($msgId); // Add message identifier.
        $this->clientKEX->serialize($sub2);
        $sub->encodeString($sub2->getBuffer()->get(0));
        $sub2->encodeBytes($msgId); // Add message identifier.
        $this->serverKEX->serialize($sub2);
        $sub->encodeString($sub2->getBuffer()->get(0));
        $sub->encodeString($K_S);
        $sub->encodeString($Q_C);
        $sub->encodeString($this->Q_S);
        $sub->encodeMpint($this->K);

        $logging    = \Plop\Plop::getInstance();
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

    public function serialize(\fpoirotte\Pssht\Wire\Encoder $encoder)
    {
        $sub    = new \fpoirotte\Pssht\Wire\Encoder(new \fpoirotte\Pssht\Buffer());
        $this->K_S->serialize($sub);

        $encoder->encodeString($sub->getBuffer()->get(0));
        $encoder->encodeString($this->Q_S);

        $sub->encodeString($this->K_S->getName());
        $sub->encodeString($this->K_S->sign($this->H));
        $encoder->encodeString($sub->getBuffer()->get(0));
        return $this;
    }

    public static function unserialize(\fpoirotte\Pssht\Wire\Decoder $decoder)
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
