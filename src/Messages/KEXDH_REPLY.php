<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Messages;

use Clicky\Pssht\MessageInterface;
use Clicky\Pssht\Wire\Encoder;
use Clicky\Pssht\Wire\Decoder;
use Clicky\Pssht\PublicKeyInterface;
use Clicky\Pssht\EncryptionInterface;
use Clicky\Pssht\KEXInterface;
use Clicky\Pssht\Messages\KEXINIT;
use Clicky\Pssht\Messages\KEXDH_INIT;

class       KEXDH_REPLY
implements  MessageInterface
{
    protected $_H;
    protected $_f;
    protected $_K;
    protected $_K_S;
    protected $_kexDHInit;
    protected $_kexAlgo;
    protected $_localKEX;
    protected $_remoteKEX;
    protected $_localIdent;
    protected $_remoteIdent;

    public function __construct(
        KEXDH_INIT          $kexDHInit,
        PublicKeyInterface  $key,
        EncryptionInterface $encryptionAlgo,
        EncryptionInterface $decryptionAlgo,
        KEXInterface        $kexAlgo,
        KEXINIT             $localKEX,
        KEXINIT             $remoteKEX,
                            $localIdent,
                            $remoteIdent
    )
    {
        $keyLength          = min(20, max($encryptionAlgo->getKeySize(), 16));
        $randBytes          = openssl_random_pseudo_bytes(2 * $keyLength);
        $y                  = gmp_init(bin2hex($randBytes), 16);
        $prime              = gmp_init($kexAlgo::getPrime(), 16);
        $this->_f           = gmp_powm($kexAlgo::getGenerator(), $y, $prime);
        $this->_K           = gmp_powm($kexDHInit->getE(), $y, $prime);
        $this->_K_S         = $key;
        $this->_kexDHInit   = $kexDHInit;
        $this->_kexAlgo     = $kexAlgo;
        $this->_localKEX    = $localKEX;
        $this->_remoteKEX   = $remoteKEX;
        $this->_localIdent  = $localIdent;
        $this->_remoteIdent = $remoteIdent;
    }

    static public function getMessageId()
    {
        return 31;
    }

    public function serialize(Encoder $encoder)
    {
        $sub = new Encoder(new \Clicky\Pssht\Buffer());
        $this->_K_S->serialize($sub);
        $K_S = $sub->getBuffer()->get(0);
        $encoder->encode_string($K_S);
        $encoder->encode_mpint($this->_f);

        // $sub is used to create the structure for the hashing function.
        $sub->encode_string($this->_remoteIdent);
        $sub->encode_string($this->_localIdent);
        // $sub2 is used to compute the value
        // of various fields inside the structure.
        $sub2   = new Encoder(new \Clicky\Pssht\Buffer());
        $msgId  = chr(\Clicky\Pssht\Messages\KEXINIT::getMessageId());
        $sub2->encode_bytes($msgId); // Add message identifier.
        $this->_remoteKEX->serialize($sub2);
        $sub->encode_string($sub2->getBuffer()->get(0));
        $sub2->encode_bytes($msgId); // Add message identifier.
        $this->_localKEX->serialize($sub2);
        $sub->encode_string($sub2->getBuffer()->get(0));
        $sub->encode_string($K_S);
        $sub->encode_mpint($this->_kexDHInit->getE());
        $sub->encode_mpint($this->_f);
        $sub->encode_mpint($this->_K);

        $H = $this->_kexAlgo->hash($sub->getBuffer()->get(0));
        $this->_H = $H;

        $sub->encode_string($this->_K_S->getName());
        $signature = $this->_K_S->sign($H, TRUE);
        $sub->encode_string($signature);
        $encoder->encode_string($sub->getBuffer()->get(0));
    }

    static public function unserialize(Decoder $decoder)
    {
        /// @FIXME: we should at least try a little...
        throw new \RuntimeException();
    }

    public function getSharedSecret()
    {
        return $this->_K;
    }

    public function getExchangeHash()
    {
        return $this->_H;
    }
}

