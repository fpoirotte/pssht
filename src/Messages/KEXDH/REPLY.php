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

class REPLY implements MessageInterface
{
    protected $H;
    protected $f;
    protected $K;
    protected $K_S;
    protected $kexDHInit;
    protected $kexAlgo;
    protected $localKEX;
    protected $remoteKEX;
    protected $localIdent;
    protected $remoteIdent;

    public function __construct(
        INIT $kexDHInit,
        PublicKeyInterface  $key,
        EncryptionInterface $encryptionAlgo,
        EncryptionInterface $decryptionAlgo,
        KEXInterface $kexAlgo,
        KEXINIT $localKEX,
        KEXINIT $remoteKEX,
        $localIdent,
        $remoteIdent
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
        $this->localKEX     = $localKEX;
        $this->remoteKEX    = $remoteKEX;
        $this->localIdent   = $localIdent;
        $this->remoteIdent  = $remoteIdent;
    }

    public static function getMessageId()
    {
        return 31;
    }

    public function serialize(Encoder $encoder)
    {
        $sub = new Encoder(new \Clicky\Pssht\Buffer());
        $this->K_S->serialize($sub);
        $K_S = $sub->getBuffer()->get(0);
        $encoder->encodeString($K_S);
        $encoder->encodeMpint($this->f);

        // $sub is used to create the structure for the hashing function.
        $sub->encodeString($this->remoteIdent);
        $sub->encodeString($this->localIdent);
        // $sub2 is used to compute the value
        // of various fields inside the structure.
        $sub2   = new Encoder(new \Clicky\Pssht\Buffer());
        $msgId  = chr(\Clicky\Pssht\Messages\KEXINIT::getMessageId());
        $sub2->encodeBytes($msgId); // Add message identifier.
        $this->remoteKEX->serialize($sub2);
        $sub->encodeString($sub2->getBuffer()->get(0));
        $sub2->encodeBytes($msgId); // Add message identifier.
        $this->localKEX->serialize($sub2);
        $sub->encodeString($sub2->getBuffer()->get(0));
        $sub->encodeString($K_S);
        $sub->encodeMpint($this->kexDHInit->getE());
        $sub->encodeMpint($this->f);
        $sub->encodeMpint($this->K);

        $H = $this->kexAlgo->hash($sub->getBuffer()->get(0));
        $this->H = $H;

        $sub->encodeString($this->K_S->getName());
        $signature = $this->K_S->sign($H, true);
        $sub->encodeString($signature);
        $encoder->encodeString($sub->getBuffer()->get(0));
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
