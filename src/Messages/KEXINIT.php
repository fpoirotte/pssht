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
use Clicky\Pssht\RandomInterface;
use Clicky\Pssht\Wire\Encoder;
use Clicky\Pssht\Wire\Decoder;
use Clicky\Pssht\Algorithms;

/**
 * SSH_MSG_KEXINIT message (RFC 4253).
 */
class KEXINIT implements MessageInterface
{
    protected $cookie;
    protected $kexAlgos;
    protected $serverHostKeyAlgos;
    protected $encAlgosC2S;
    protected $encAlgosS2C;
    protected $MACAlgosC2S;
    protected $MACAlgosS2C;
    protected $compAlgosC2S;
    protected $compAlgosS2C;
    protected $langC2S;
    protected $langS2C;
    protected $firstKexPacket;

    public function __construct(
        RandomInterface $random,
        array $kexAlgos,
        array $serverHostKeyAlgos,
        array $encAlgosC2S,
        array $encAlgosS2C,
        array $macAlgosC2S,
        array $macAlgosS2C,
        array $compAlgosC2S,
        array $compAlgosS2C,
        array $langC2S = array(),
        array $langS2C = array(),
        $firstKexPacket = false
    ) {
        if (!is_bool($firstKexPacket)) {
            throw new \InvalidArgumentException();
        }

        $algos = Algorithms::factory();

        $this->cookie               = $random->getBytes(16);
        $this->kexAlgos             = $kexAlgos;
        $this->serverHostKeyAlgos   = $serverHostKeyAlgos;
        $this->encAlgosC2S          = $encAlgosC2S;
        $this->encAlgosS2C          = $encAlgosS2C;
        $this->macAlgosC2S          = $macAlgosC2S;
        $this->macAlgosS2C          = $macAlgosS2C;
        $this->compAlgosC2S         = $compAlgosC2S;
        $this->compAlgosS2C         = $compAlgosS2C;
        $this->langC2S              = $langC2S;
        $this->langS2C              = $langS2C;
        $this->firstKexPacket       = $firstKexPacket;
    }

    public static function getMessageId()
    {
        return 20;
    }

    public function serialize(Encoder $encoder)
    {
        $encoder->encodeBytes($this->cookie);
        $encoder->encodeNameList($this->kexAlgos);
        $encoder->encodeNameList($this->serverHostKeyAlgos);
        $encoder->encodeNameList($this->encAlgosC2S);
        $encoder->encodeNameList($this->encAlgosS2C);
        $encoder->encodeNameList($this->macAlgosC2S);
        $encoder->encodeNameList($this->macAlgosS2C);
        $encoder->encodeNameList($this->compAlgosC2S);
        $encoder->encodeNameList($this->compAlgosS2C);
        $encoder->encodeNameList($this->langC2S);
        $encoder->encodeNameList($this->langS2C);
        $encoder->encodeBoolean($this->firstKexPacket);
        $encoder->encodeUint32(0); // Reserved for future extension.
        return $this;
    }

    public static function unserialize(Decoder $decoder)
    {
        $res = new static(
            new \Clicky\Pssht\Random\Fixed(
                $decoder->decodeBytes(16)   // cookie
            ),
            $decoder->decodeNameList(),     // keyAlgos
            $decoder->decodeNameList(),     // serverHostKeyAlgos
            $decoder->decodeNameList(),     // encAlgosC2S
            $decoder->decodeNameList(),     // encAlgosS2C
            $decoder->decodeNameList(),     // macAlgosC2S
            $decoder->decodeNameList(),     // macAlgosS2C
            $decoder->decodeNameList(),     // compAlgosC2S
            $decoder->decodeNameList(),     // compAlgosS2C
            $decoder->decodeNameList(),     // langC2S
            $decoder->decodeNameList(),     // langS2C
            $decoder->decodeBoolean()       // firstKexPacket
        );
        $decoder->decodeUint32();           // Reserved
        return $res;
    }

    public function getKEXAlgos()
    {
        return $this->kexAlgos;
    }

    public function getServerHostKeyAlgos()
    {
        return $this->serverHostKeyAlgos;
    }

    public function getC2SEncryptionAlgos()
    {
        return $this->encAlgosC2S;
    }

    public function getC2SMACAlgos()
    {
        return $this->macAlgosC2S;
    }

    public function getC2SCompressionAlgos()
    {
        return $this->compAlgosC2S;
    }

    public function getS2CEncryptionAlgos()
    {
        return $this->encAlgosS2C;
    }

    public function getS2CMACAlgos()
    {
        return $this->macAlgosS2C;
    }

    public function getS2CCompressionAlgos()
    {
        return $this->compAlgosS2C;
    }
}
