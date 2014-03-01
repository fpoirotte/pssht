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

/**
 * SSH_MSG_KEXINIT message (RFC 4253).
 */
class KEXINIT implements \Clicky\Pssht\MessageInterface
{
    /// Random cookie for the Key Exchange.
    protected $cookie;

    /// Supported key exchange algorithms.
    protected $kexAlgos;

    /// Supported server host key algorithms.
    protected $serverHostKeyAlgos;

    /// Supported encryption algorithms (client to server).
    protected $encAlgosC2S;

    /// Supported encryption algorithms (server to client).
    protected $encAlgosS2C;

    /// Supported MAC algorithms (client to server).
    protected $MACAlgosC2S;

    /// Supported MAC algorithms (server to client).
    protected $MACAlgosS2C;

    /// Supported compression algorithms (client to server).
    protected $compAlgosC2S;

    /// Supported compression algorithms (server to client).
    protected $compAlgosS2C;

    /// Supported languages (client to server) in RFC 3066 format.
    protected $langC2S;

    /// Supported languages (server to client) in RFC 3066 format.
    protected $langS2C;

    /// Indicates whether a KEX packet was sent right after this one.
    protected $firstKexPacket;


    /**
     * Construction a new SSH_MSG_KEXINIT message.
     *
     *  \param Clicky::Pssht::RandomInterface $random
     *      RNG from which the KEX cookie will be generated.
     *
     *  \param array $kexAlgos
     *      List of supported key exchange algorithm names.
     *
     *  \param array $serverHostKeyAlgos
     *      List of supported server host key algorithm names.
     *
     *  \param array $encAlgosC2S
     *      List of supported encryption algorithm names
     *      (client to server).
     *
     *  \param array $encAlgosS2C
     *      List of supported encryption algorithm names
     *      (server to client).
     *
     *  \param array $macAlgosC2S
     *      List of supported MAC algorithm names
     *      (client to server).
     *
     *  \param array $macAlgosS2C
     *      List of supported MAC algorithm names
     *      (server to client).
     *
     *  \param array $compAlgosC2S
     *      List of supported compression algorithm names
     *      (client to server).
     *
     *  \param array $compAlgosS2C
     *      List of supported compression algorithm names
     *      (server to client).
     *
     *  \param array $langC2S
     *      (optional) List of supported languages in RFC 3066 format
     *      (client to server). If omitted, the human-readable messages
     *      are assumed to be language-neutral.
     *
     *  \param array $langS2C
     *      (optional) List of supported languages in RFC 3066 format
     *      (server to client). If omitted, the human-readable messages
     *      are assumed to be language-neutral.
     *
     *  \param bool $firstKexPacket
     *      (optional) Indicates whether a KEX packet was immediately
     *      sent after this packet (\b true) or not (\b false).
     */
    public function __construct(
        \Clicky\Pssht\RandomInterface $random,
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

    public function serialize(\Clicky\Pssht\Wire\Encoder $encoder)
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

    public static function unserialize(\Clicky\Pssht\Wire\Decoder $decoder)
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

    /**
     * Get the list of supported key exchange algorithms.
     *
     *  \retval array
     *      List of supported key exchange algorithm names.
     */
    public function getKEXAlgos()
    {
        return $this->kexAlgos;
    }

    /**
     * Get the list of supported server host key algorithms.
     *
     *  \retval array
     *      List of supported server host key algorithm names.
     */
    public function getServerHostKeyAlgos()
    {
        return $this->serverHostKeyAlgos;
    }

    /**
     * Get the list of supported encryption algorithms
     * (client to server direction).
     *
     *  \retval array
     *      List of supported encryption algorithm names.
     */
    public function getC2SEncryptionAlgos()
    {
        return $this->encAlgosC2S;
    }

    /**
     * Get the list of supported MAC algorithms
     * (client to server direction).
     *
     *  \retval array
     *      List of supported MAC algorithm names.
     */
    public function getC2SMACAlgos()
    {
        return $this->macAlgosC2S;
    }

    /**
     * Get the list of supported compression algorithms
     * (client to server direction).
     *
     *  \retval array
     *      List of supported compression algorithm names.
     */
    public function getC2SCompressionAlgos()
    {
        return $this->compAlgosC2S;
    }

    /**
     * Get the list of supported encryption algorithms
     * (server to client direction).
     *
     *  \retval array
     *      List of supported encryption algorithm names.
     */
    public function getS2CEncryptionAlgos()
    {
        return $this->encAlgosS2C;
    }

    /**
     * Get the list of supported MAC algorithms
     * (server to client direction).
     *
     *  \retval array
     *      List of supported MAC algorithm names.
     */
    public function getS2CMACAlgos()
    {
        return $this->macAlgosS2C;
    }

    /**
     * Get the list of supported compression algorithms
     * (server to client direction).
     *
     *  \retval array
     *      List of supported compression algorithm names.
     */
    public function getS2CCompressionAlgos()
    {
        return $this->compAlgosS2C;
    }
}
