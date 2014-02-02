<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht;

use Clicky\Pssht\Buffer;
use Clicky\Pssht\Wire\Encoder;
use Clicky\Pssht\Wire\Decoder;
use Clicky\Pssht\Messages\Disconnect;
use Clicky\Pssht\CompressionInterface;

class Client
{
    protected $authLayer;
    protected $inSeqNo;
    protected $outSeqNo;
    protected $encoder;
    protected $decoder;
    protected $encryptor;
    protected $ecryptor;
    protected $compressor;
    protected $uncompressor;
    protected $inMAC;
    protected $outMAC;
    protected $context;

    public function __construct(
        \Clicky\Pssht\Wire\Encoder $encoder = null,
        \Clicky\Pssht\Wire\Decoder $decoder = null
    ) {
        if ($encoder === null) {
            $encoder = new \Clicky\Pssht\Wire\Encoder();
        }
        if ($decoder === null) {
            $decoder = new \Clicky\Pssht\Wire\Decoder();
        }

        $this->authLayer    = null;
        $this->inSeqNo      = 0;
        $this->outSeqNo     = 0;
        $this->encoder      = $encoder;
        $this->decoder      = $decoder;
        $this->compressor   = new \Clicky\Pssht\Compression\None(CompressionInterface::MODE_COMPRESS);
        $this->uncompressor = new \Clicky\Pssht\Compression\None(CompressionInterface::MODE_UNCOMPRESS);
        $this->encryptor    = new \Clicky\Pssht\Encryption\None(null, null);
        $this->decryptor    = new \Clicky\Pssht\Encryption\None(null, null);
        $this->inMAC        = new \Clicky\Pssht\MAC\None(null);
        $this->outMAC       = new \Clicky\Pssht\MAC\None(null);
        $this->context      = array();

        $ident = "SSH-2.0-pssht_1.0.x_dev";
        $this->context['identity']['server'] = $ident;
        $this->encoder->encodeBytes($ident . "\r\n");
    }

    public function getEncoder()
    {
        return $this->encoder;
    }

    public function getDecoder()
    {
        return $this->decoder;
    }

    public function writeMessage(\Clicky\Pssht\MessageInterface $message)
    {
        $buffer     = new Buffer();
        $encoder    = new Encoder($buffer);

        // Serialize the message.
        $encoder->encodeBytes(chr($message::getMessageId()));
        $message->serialize($encoder);
        $payload    = $buffer->get(0);

        // Compress the payload if necessary.
        $payload    = $this->compressor->update($payload);
        $size       = strlen($payload);

        // Compute padding size.
        $blockSize  = max(8, $this->encryptor->getBlockSize());
        $padSize    = $blockSize - ((1 + 4 + $size) % $blockSize);
        if ($padSize < 4) {
            $padSize = ($padSize + $blockSize) % 256;
        }
        $padding = openssl_random_pseudo_bytes($padSize);

        // Create the packet.
        $encoder->encodeUint32(1 + $size + $padSize);
        $encoder->encodeBytes(chr($padSize));
        $encoder->encodeBytes($payload);
        $encoder->encodeBytes($padding);
        $packet = $buffer->get(0);

        // Write the encrypted packet on the wire.
        $this->encoder->encodeBytes($this->encryptor->encrypt($packet));

        // Write the MAC if necessary.
        $mac = $this->outMAC->compute(pack('N', $this->outSeqNo) . $packet);
        $this->outSeqNo++;
        $this->outSeqNo &= 0xFFFFFFFF;
        $this->encoder->encodeBytes($mac);
    }

    // Initial state
    protected function handleINIT(Decoder $decoder)
    {
        $ident = $decoder->getBuffer()->get("\r\n");
        if ($ident === null) {
            throw new \RuntimeException();
        }

        $this->context['identity']['client'] = (string) substr($ident, 0, -2);

        /// @FIXME: implement disconnect method (with reason/code).
        if (strncmp($ident, 'SSH-2.0-', 8) !== 0) {
            $this->disconnect(null);
        }

        $random = new \Clicky\Pssht\Random\OpenSSL();
        $kex    = new \Clicky\Pssht\Messages\KEXINIT($random);
        $this->context['kex']['server'] = $kex;
        $this->writeMessage($kex);
        return true;
    }

    // SSH_MSG_KEXINIT
    protected function handleCode20(Decoder $decoder)
    {
        $algos      = \Clicky\Pssht\Algorithms::factory();
        $kex        = \Clicky\Pssht\Messages\KEXINIT::unserialize($decoder);
        $this->context['kex']['client'] = $kex;

        // KEX method
        $this->context['kexAlgo'] = null;
        foreach ($kex->getKEXAlgos() as $algo) {
            if ($algos->getClass('KEX', $algo) !== null) {
                $this->context['kexAlgo'] = $algos->getClass('KEX', $algo);
                break;
            }
        }
        // No suitable KEX algorithm found.
        if (!$this->context['kexAlgo']) {
            throw new \RuntimeException();
        }


        // C2S encryption
        $this->context['C2S']['Encryption'] = null;
        foreach ($kex->getC2SEncryptionAlgos() as $algo) {
            if ($algos->getClass('Encryption', $algo) !== null) {
                $this->context['C2S']['Encryption'] = $algos->getClass('Encryption', $algo);
                break;
            }
        }
        // No suitable C2S encryption cipher found.
        if (!$this->context['C2S']['Encryption']) {
            throw new \RuntimeException();
        }

        // C2S compression
        $this->context['C2S']['Compression'] = null;
        foreach ($kex->getC2SCompressionAlgos() as $algo) {
            if ($algos->getClass('Compression', $algo) !== null) {
                $this->context['C2S']['Compression'] = $algos->getClass('Compression', $algo);
                break;
            }
        }
        // No suitable C2S compression found.
        if (!$this->context['C2S']['Compression']) {
            throw new \RuntimeException();
        }

        // C2S MAC
        $this->context['C2S']['MAC'] = null;
        foreach ($kex->getC2SMACAlgos() as $algo) {
            if ($algos->getClass('MAC', $algo) !== null) {
                $this->context['C2S']['MAC'] = $algos->getClass('MAC', $algo);
                break;
            }
        }
        // No suitable C2S MAC found.
        if (!$this->context['C2S']['MAC']) {
            throw new \RuntimeException();
        }

        // S2C encryption
        $this->context['S2C']['Encryption'] = null;
        foreach ($kex->getS2CEncryptionAlgos() as $algo) {
            if ($algos->getClass('Encryption', $algo) !== null) {
                $this->context['S2C']['Encryption'] = $algos->getClass('Encryption', $algo);
                break;
            }
        }
        // No suitable S2C encryption cipher found.
        if (!$this->context['S2C']['Encryption']) {
            throw new \RuntimeException();
        }

        // S2C compression
        $this->context['S2C']['Compression'] = null;
        foreach ($kex->getS2CCompressionAlgos() as $algo) {
            if ($algos->getClass('Compression', $algo) !== null) {
                $this->context['S2C']['Compression'] = $algos->getClass('Compression', $algo);
                break;
            }
        }
        // No suitable S2C compression found.
        if (!$this->context['S2C']['Compression']) {
            throw new \RuntimeException();
        }

        // S2C MAC
        $this->context['S2C']['MAC'] = null;
        foreach ($kex->getS2CMACAlgos() as $algo) {
            if ($algos->getClass('MAC', $algo) !== null) {
                $this->context['S2C']['MAC'] = $algos->getClass('MAC', $algo);
                break;
            }
        }
        // No suitable S2C MAC found.
        if (!$this->context['S2C']['MAC']) {
            throw new \RuntimeException();
        }

        return true;
    }

    // SSH_MSG_KEXDH_INIT
    protected function handleCode30(Decoder $decoder)
    {
        $message    = \Clicky\Pssht\Messages\KEXDH\INIT::unserialize($decoder);
        $kexAlgo    = $this->context['kexAlgo'];
        $kexAlgo    = new $kexAlgo();
        $response   = new \Clicky\Pssht\Messages\KEXDH\REPLY(
            $message,
            new \Clicky\Pssht\PublicKey\SSH\RSA(
                'file://' .
                dirname(__DIR__) .
                '/tests/data/rsa2048'
            ),
            $this->encryptor,
            $this->decryptor,
            $kexAlgo,
            $this->context['kex']['server'],
            $this->context['kex']['client'],
            $this->context['identity']['server'],
            $this->context['identity']['client']
        );
        $this->writeMessage($response);

        if (!isset($this->context['sessionIdentifier'])) {
            $this->context['sessionIdentifier'] = $response->getExchangeHash();
        }
        $this->context['DH'] = $response;
        return true;
    }

    // SSH_MSG_NEWKEYS
    protected function handleCode21(Decoder $decoder)
    {
        $response = new \Clicky\Pssht\Messages\NEWKEYS();
        $this->writeMessage($response);

        // Reset the various keys.
        $kexAlgo    = $this->context['kexAlgo'];
        $kexAlgo    = new $kexAlgo();
        $encoder    = new Encoder(new Buffer());
        $encoder->encodeMpint($this->context['DH']->getSharedSecret());
        $sharedSecret   = $encoder->getBuffer()->get(0);
        $exchangeHash   = $this->context['DH']->getExchangeHash();
        $sessionId      = $this->context['sessionIdentifier'];
        $limiters       = array(
            'A' => array($this->context['C2S']['Encryption'], 'getIVSize'),
            'B' => array($this->context['S2C']['Encryption'], 'getIVSize'),
            'C' => array($this->context['C2S']['Encryption'], 'getKeySize'),
            'D' => array($this->context['S2C']['Encryption'], 'getKeySize'),
            'E' => array($this->context['C2S']['MAC'], 'getSize'),
            'F' => array($this->context['C2S']['MAC'], 'getSize'),
        );
        foreach (array('A', 'B', 'C', 'D', 'E', 'F') as $keyIndex) {
            $key    = $kexAlgo->hash($sharedSecret . $exchangeHash . $keyIndex . $sessionId);
            $limit  = call_user_func($limiters[$keyIndex]);
            $keyReq = max(24, $limit);
            while (strlen($key) < $keyReq) {
                $key .= $kexAlgo->hash($sharedSecret . $exchangeHash . $key);
            }
            $key = (string) substr($key, 0, $limit);
            $this->context['keys'][$keyIndex] = $key;
        }

        // Encryption
        $cls = $this->context['C2S']['Encryption'];
        $this->decryptor = new $cls(
            $this->context['keys']['A'],
            $this->context['keys']['C']
        );
        $cls = $this->context['S2C']['Encryption'];
        $this->encryptor = new $cls(
            $this->context['keys']['B'],
            $this->context['keys']['D']
        );

        // MAC
        $cls            = $this->context['C2S']['MAC'];
        $this->inMAC   = new $cls($this->context['keys']['E']);
        $cls            = $this->context['S2C']['MAC'];
        $this->outMAC  = new $cls($this->context['keys']['F']);

        // Compression
        $cls                    = $this->context['C2S']['Compression'];
        $this->uncompressor    = new $cls(CompressionInterface::MODE_UNCOMPRESS);
        $cls                    = $this->context['S2C']['Compression'];
        $this->compressor      = new $cls(CompressionInterface::MODE_COMPRESS);

        return true;
    }

    // SSH_MSG_IGNORE
    public function handleCode2(Decoder $decoder)
    {
        return true;
    }

    // SSH_MSG_DEBUG
    public function handleCode4(Decoder $decoder)
    {
        $message = \Clicky\Pssht\Messages\DEBUG::unserialize($decoder);
        if ($message->mustAlwaysDisplay()) {
            echo escape($message->getMessage()) . PHP_EOL;
        }
        return true;
    }

    // SSH_MSG_SERVICE_REQUEST
    public function handleCode5(Decoder $decoder)
    {
        $message    = \Clicky\Pssht\Messages\SERVICE\REQUEST::unserialize($decoder);
        $algos      = Algorithms::factory();
        $service    = $message->getServiceName();
        $cls        = $algos->getClass('Services', $service);
        if ($cls !== null) {
            $response = new \Clicky\Pssht\Messages\SERVICE\ACCEPT($message->getServiceName());
            $this->authLayer = new $cls($this);
        } else {
            $response = new Disconnect(
                Disconnect::SSH_DISCONNECT_SERVICE_NOT_AVAILABLE,
                'No such service'
            );
        }
        $this->writeMessage($response);
        return true;
    }

    public function readMessage()
    {
        if (!isset($this->context['identity']['client'])) {
            return $this->handleINIT($this->decoder);
        }

        $blockSize  = max($this->decryptor->getBlockSize(), 8);
        $encPayload = $this->decoder->getBuffer()->get($blockSize);
        if ($encPayload === null || $encPayload === '') {
            return false;
        }
        $unencrypted    = $this->decryptor->decrypt($encPayload);
        $buffer         = new Buffer($unencrypted);
        $decoder        = new Decoder($buffer);
        $packetLength   = $decoder->decodeUint32();

        // Read the rest of the message.
        $toRead         =
            // Remove what we already read.
            // Note: we must account for the "packet length" field
            // not being included in $packetLength itself.
            4 - $blockSize +

            // Rest of the encrypted data.
            $packetLength;

        if ($toRead < 0) {
            throw new \RuntimeException();
        }

        if ($toRead !== 0) {
            $encPayload2 = $this->decoder->getBuffer()->get($toRead);
            if ($encPayload2 === null) {
                $this->decoder->getBuffer()->unget($encPayload);
                return false;
            }
            $unencrypted2 = $this->decryptor->decrypt($encPayload2);
            $buffer->push($unencrypted2);
        }

        $paddingLength  = ord($decoder->decodeBytes());
        $payload        = $decoder->decodeBytes($packetLength - $paddingLength - 1);
        $padding        = $decoder->decodeBytes($paddingLength);

        // If a MAC is in use.
        $macSize    = $this->inMAC->getSize();
        $actualMAC  = '';
        if ($macSize > 0) {
            $actualMAC = $this->decoder->getBuffer()->get($macSize);
            if ($actualMAC === null) {
                $this->decoder->getBuffer()->unget($encPayload2)->unget($encPayload);
                return false;
            }

            $expectedMAC = $this->inMAC->compute(
                pack('N', $this->inSeqNo) .
                ((string) substr($unencrypted . $unencrypted2, 0, $packetLength + 4))
            );

            if ($expectedMAC !== $actualMAC) {
                throw new \RuntimeException();
            }
        }

        if (!isset($packetLength, $paddingLength, $payload, $padding, $actualMAC)) {
            $this->decoder->getBuffer()->unget($actualMAC)->unget($encPayload2)->unget($encPayload);
            echo "Something went wrong during decoding" . PHP_EOL;
            return false;
        }

        $payload    = $this->uncompressor->update($payload);
        $decoder    = new Decoder(new Buffer($payload));
        $msgType    = ord($decoder->decodeBytes(1));
        $func       = 'handleCode' . $msgType;
        $res        = true;

        try {
            if (method_exists($this, $func)) {
                $res = call_user_func(array($this, $func), $decoder);
            } elseif ($this->authLayer !== null) {
                $res = $this->authLayer->handleMessage(
                    $msgType,
                    $decoder,
                    count($this->decoder->getBuffer())
                );
            } else {
                throw new \RuntimeException();
            }
        } catch (RuntimeException $e) {
            echo "No such handler: $func" . PHP_EOL;
            $response = new \Clicky\Pssht\Messages\UNIMPLEMENTED($this->inSeqNo);
            $this->writeMessage($response);
        }

        $this->inSeqNo++;
        $this->inSeqNo &= 0xFFFFFFFF;
        return $res;
    }
}
