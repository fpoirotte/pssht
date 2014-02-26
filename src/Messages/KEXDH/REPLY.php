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

use Clicky\Pssht\MessageInterface;
use Clicky\Pssht\Wire\Encoder;
use Clicky\Pssht\Wire\Decoder;
use Clicky\Pssht\PublicKeyInterface;
use Clicky\Pssht\EncryptionInterface;
use Clicky\Pssht\KEXInterface;
use Clicky\Pssht\Messages\KEXINIT;
use Clicky\Pssht\Messages\KEXDH\INIT;

/**
 * SSH_MSG_KEXDH_REPLY message (RFC 4253).
 */
class REPLY implements MessageInterface
{
    protected $H;
    protected $f;
    protected $K;
    protected $K_S;
    protected $kexDHInit;
    protected $kexAlgo;
    protected $serverKEX;
    protected $clientKEX;
    protected $serverIdent;
    protected $clientIdent;

    public function __construct(
        INIT $kexDHInit,
        PublicKeyInterface  $key,
        EncryptionInterface $encryptionAlgo,
        EncryptionInterface $decryptionAlgo,
        KEXInterface $kexAlgo,
        KEXINIT $serverKEX,
        KEXINIT $clientKEX,
        $serverIdent,
        $clientIdent
    ) {
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
        $sub    = new Encoder(new \Clicky\Pssht\Buffer());
        $this->K_S->serialize($sub);
        $K_S    = $sub->getBuffer()->get(0);
        $sub->encodeString($this->clientIdent);
        $sub->encodeString($this->serverIdent);
        // $sub2 is used to compute the value
        // of various fields inside the structure.
        $sub2   = new Encoder(new \Clicky\Pssht\Buffer());
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

    public function serialize(Encoder $encoder)
    {
        $sub    = new Encoder(new \Clicky\Pssht\Buffer());
        $this->K_S->serialize($sub);

        $encoder->encodeString($sub->getBuffer()->get(0));
        $encoder->encodeMpint($this->f);

        $sub->encodeString($this->K_S->getName());
        $sub->encodeString($this->K_S->sign($this->H, true));
        $encoder->encodeString($sub->getBuffer()->get(0));
        return $this;
    }

    public static function unserialize(Decoder $decoder)
    {
        /// @FIXME: we should at least try a little...
        throw new \RuntimeException();
    }

    public function getSharedSecret()
    {
        return $this->K;
    }

    public function getExchangeHash()
    {
        return $this->H;
    }
}
