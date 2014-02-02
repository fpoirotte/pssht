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
use Clicky\Pssht\CompressionInterface;
use Clicky\Pssht\EncryptionInterface;
use Clicky\Pssht\MACInterface;

class Transport
{
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
    protected $handlers;

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
        $this->handlers     = array(
            \Clicky\Pssht\Messages\IGNORE::getMessageId() =>
                new \Clicky\Pssht\Handlers\IGNORE(),

            \Clicky\Pssht\Messages\DEBUG::getMessageId() =>
                new \Clicky\Pssht\Handlers\DEBUG(),

            \Clicky\Pssht\Messages\SERVICE\REQUEST::getMessageId() =>
                new \Clicky\Pssht\Handlers\SERVICE\REQUEST(),

            \Clicky\Pssht\Messages\KEXINIT::getMessageId() =>
                new \Clicky\Pssht\Handlers\KEXINIT(),

            \Clicky\Pssht\Messages\NEWKEYS::getMessageId() =>
                new \Clicky\Pssht\Handlers\NEWKEYS(),

            \Clicky\Pssht\Messages\KEXDH\INIT::getMessageId() =>
                new \Clicky\Pssht\Handlers\KEXDH\INIT(),

            256 => new \Clicky\Pssht\Handlers\InitialState(),
        );

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

    public function getCompressor()
    {
        return $this->compressor;
    }

    public function setCompressor(CompressionInterface $compressor)
    {
        if ($compressor->getMode() !== CompressionInterface::MODE_COMPRESS) {
            throw new \InvalidArgumentException();
        }

        $this->compressor = $compressor;
        return $this;
    }

    public function getUncompressor()
    {
        return $this->uncompressor;
    }

    public function setUncompressor(CompressionInterface $uncompressor)
    {
        if ($uncompressor->getMode() !== CompressionInterface::MODE_UNCOMPRESS) {
            throw new \InvalidArgumentException();
        }

        $this->uncompressor = $uncompressor;
        return $this;
    }

    public function getEncryptor()
    {
        return $this->encryptor;
    }

    public function setEncryptor(EncryptionInterface $encryptor)
    {
        $this->encryptor = $encryptor;
        return $this;
    }

    public function getDecryptor()
    {
        return $this->decryptor;
    }

    public function setDecryptor(EncryptionInterface $decryptor)
    {
        $this->decryptor = $decryptor;
        return $this;
    }

    public function getInputMAC()
    {
        return $this->inMAC;
    }

    public function setInputMAC(MACInterface $inputMAC)
    {
        $this->inMAC = $inputMAC;
        return $this;
    }

    public function getOutputMAC()
    {
        return $this->outMAC;
    }

    public function setOutputMAC(MACInterface $outputMAC)
    {
        $this->outMAC = $outputMAC;
        return $this;
    }

    public function setHandler($type, \Clicky\Pssht\HandlerInterface $handler)
    {
        if (!is_int($type) || $type < 0 || $type > 255) {
            throw new \InvalidArgumentException();
        }

        $this->handlers[$type] = $handler;
        return $this;
    }

    public function unsetHandler($type, \Clicky\Pssht\HandlerInterface $handler)
    {
        if (!is_int($type) || $type < 0 || $type > 255) {
            throw new \InvalidArgumentException();
        }

        if (isset($this->handlers[$type]) && $this->handlers[$type] === $handler) {
            unset($this->handlers[$type]);
        }
        return $this;
    }

    public function writeMessage(\Clicky\Pssht\MessageInterface $message)
    {
        // Serialize the message.
        $encoder    = new Encoder();
        $encoder->encodeBytes(chr($message::getMessageId()));
        $message->serialize($encoder);
        $payload    = $encoder->getBuffer()->get(0);

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
        $packet = $encoder->getBuffer()->get(0);

        // Write the encrypted packet on the wire.
        $this->encoder->encodeBytes($this->encryptor->encrypt($packet));

        // Write the MAC if necessary.
        $mac = $this->outMAC->compute(pack('N', $this->outSeqNo) . $packet);
        $this->outSeqNo = ++$this->outSeqNo & 0xFFFFFFFF;
        $this->encoder->encodeBytes($mac);
    }

    public function readMessage()
    {
        // Initial state: expect the client's identification string.
        if (!isset($this->context['identity']['client'])) {
            return $this->handlers[256]->handle(
                null,
                $this->decoder,
                $this,
                $this->context
            );
        }

        $blockSize  = max($this->decryptor->getBlockSize(), 8);
        $encPayload = $this->decoder->getBuffer()->get($blockSize);
        if ($encPayload === null) {
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

        $res = true;
        if (isset($this->handlers[$msgType])) {
            $handler = $this->handlers[$msgType];
            $logging = \Plop::getInstance();
            $logging->debug(
                'Calling %(handler)s',
                array('handler' => get_class($handler) . '::handle')
            );
            $res = $handler->handle($msgType, $decoder, $this, $this->context);
        } else {
            $response = new \Clicky\Pssht\Messages\UNIMPLEMENTED($this->inSeqNo);
            $this->writeMessage($response);
        }

        $this->inSeqNo = ++$this->inSeqNo & 0xFFFFFFFF;
        return $res;
    }
}
