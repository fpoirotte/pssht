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

/**
 * Transport layer for the SSH protocol (RFC 4253).
 */
class Transport
{
    /// Address (ip:port) of the client.
    protected $address;

    /// Input sequence number.
    protected $inSeqNo;

    /// Output sequence number.
    protected $outSeqNo;

    /// SSH encoder.
    protected $encoder;

    /// SSH decoder.
    protected $decoder;

    /// Output cipher.
    protected $encryptor;

    /// Input cipher.
    protected $decryptor;

    /// Output compression.
    protected $compressor;

    /// Input compression.
    protected $uncompressor;

    /// Input MAC.
    protected $inMAC;

    /// Output MAC.
    protected $outMAC;

    /// Context for this SSH connection.
    protected $context;

    /// Registered handlers for this SSH connection.
    protected $handlers;

    /// Factory for the application.
    protected $appFactory;

    /// SSH banner.
    protected $banner;


    /**
     * Construct a new SSH transport layer.
     *
     *  \param array $serverKeys
     *      Keys presented by the server as an associated array where:
     *      -   keys indicate the key's algorithm (eg. "ssh-dss")
     *      -   values are an associative array with the following keys:
     *          -   "file": a PEM-encoded private key or path to a PEM-encoded
     *                      private key, in "file:///path/to/key.pem" format
     *          -   "passphrase": (optional) passphrase for the key
     *
     *  \param REQUEST $authMethods
     *      Allowed authentication methods.
     *
     *  \param Encoder $encoder
     *      (optional) Encoder to use when sending SSH messages.
     *      If omitted, a new encoder is automatically created.
     *
     *  \param Decoder $decoder
     *      (optional) Decoder to use when sending SSH messages.
     *      If omitted, a new decoder is automatically created.
     *
     *  \note
     *      Once this class' constructor has been called,
     *      you are advised to call the setAddress() method
     *      to register the client's IP address.
     *      This is required for some authentication methods
     *      to work properly.
     */
    public function __construct(
        array $serverKeys,
        \Clicky\Pssht\Handlers\SERVICE\REQUEST $authMethods,
        \Clicky\Pssht\Wire\Encoder $encoder = null,
        \Clicky\Pssht\Wire\Decoder $decoder = null
    ) {
        if ($encoder === null) {
            $encoder = new \Clicky\Pssht\Wire\Encoder();
        }
        if ($decoder === null) {
            $decoder = new \Clicky\Pssht\Wire\Decoder();
        }

        $algos  = \Clicky\Pssht\Algorithms::factory();
        $keys   = array();
        foreach ($serverKeys as $keyType => $params) {
            $cls = $algos->getClass('PublicKey', $keyType);
            if ($cls === null) {
                throw new \InvalidArgumentException();
            }

            $passphrase = '';
            if (isset($params['passphrase'])) {
                $passphrase = $params['passphrase'];
            }

            $keys[$keyType] = $cls::loadPrivate($params['file'], $passphrase);
        }

        $this->address      = null;
        $this->appFactory   = null;
        $this->banner       = null;
        $this->context      = array();

        $this->inSeqNo      = 0;
        $this->outSeqNo     = 0;

        $this->encoder      = $encoder;
        $this->decoder      = $decoder;

        $this->compressor   = new \Clicky\Pssht\Compression\None(
            CompressionInterface::MODE_COMPRESS
        );

        $this->uncompressor = new \Clicky\Pssht\Compression\None(
            CompressionInterface::MODE_UNCOMPRESS
        );

        $this->encryptor    = new \Clicky\Pssht\Encryption\None(null, null);
        $this->decryptor    = new \Clicky\Pssht\Encryption\None(null, null);

        $this->inMAC        = new \Clicky\Pssht\MAC\None(null);
        $this->outMAC       = new \Clicky\Pssht\MAC\None(null);

        $this->handlers     = array(
            \Clicky\Pssht\Messages\DISCONNECT::getMessageId() =>
                new \Clicky\Pssht\Handlers\DISCONNECT(),

            \Clicky\Pssht\Messages\IGNORE::getMessageId() =>
                new \Clicky\Pssht\Handlers\IGNORE(),

            \Clicky\Pssht\Messages\DEBUG::getMessageId() =>
                new \Clicky\Pssht\Handlers\DEBUG(),

            \Clicky\Pssht\Messages\SERVICE\REQUEST::getMessageId() =>
                $authMethods,

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
        $this->context['serverKeys'] = $keys;
        $this->encoder->encodeBytes($ident . "\r\n");
    }

    /**
     * Set the IP address of the client associated
     * with this transport layer.
     *
     *  \param string $address
     *      IP address of the client.
     *
     *  \retval Transport
     *      Returns this transport layer.
     *
     *  \note
     *      This method is intended for use with
     *      hostbased authentication methods.
     *      Moreover, this method may only be called
     *      once. Subsequent calls will result in a
     *      RuntimeException being raised.
     */
    public function setAddress($address)
    {
        if (!is_string($address)) {
            throw new \InvalidArgumentException();
        }

        if ($this->address !== null) {
            throw new \RuntimeException();
        }

        $this->address = $address;
        return $this;
    }

    /**
     * Get the client's IP address.
     *
     *  \retval string
     *      The client's IP address, as set.
     *
     *  \retval null
     *      The client's IP has not been set yet.
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Get the object used to encode outgoing packets.
     *
     *  \retval Encoder
     *      Encoder used for sending SSH messages.
     */
    public function getEncoder()
    {
        return $this->encoder;
    }

    /**
     * Get the object used to decode incoming packets.
     *
     *  \retval Decoder
     *      Decoder used for receiving SSH messages.
     */
    public function getDecoder()
    {
        return $this->decoder;
    }

    /**
     * Get the object used to compress outgoing packets.
     *
     *  \retval CompressionInterface
     *      Outgoing packets' compressor.
     */
    public function getCompressor()
    {
        return $this->compressor;
    }

    /**
     * Set the object used to compress outgoing packets.
     *
     *  \param CompressionInterface $compressor
     *      Outgoing packets' compressor.
     *
     *  \retval Transport
     *      Return this transport layer.
     */
    public function setCompressor(CompressionInterface $compressor)
    {
        if ($compressor->getMode() !== CompressionInterface::MODE_COMPRESS) {
            throw new \InvalidArgumentException();
        }

        $this->compressor = $compressor;
        return $this;
    }

    /**
     * Get the object used to uncompress incoming packets.
     *
     *  \retval CompressionInterface
     *      Incoming packets' uncompressor.
     */
    public function getUncompressor()
    {
        return $this->uncompressor;
    }

    /**
     * Set the object used to uncompress incoming packets.
     *
     *  \param CompressionInterface $uncompressor
     *      Incoming packets' uncompressor.
     *
     *  \retval Transport
     *      Return this transport layer.
     */
    public function setUncompressor(CompressionInterface $uncompressor)
    {
        if ($uncompressor->getMode() !== CompressionInterface::MODE_UNCOMPRESS) {
            throw new \InvalidArgumentException();
        }

        $this->uncompressor = $uncompressor;
        return $this;
    }

    /**
     * Get the object used to encrypt outgoing packets.
     *
     *  \retval EncryptionInterface
     *      Outgoing packets' encryptor.
     */
    public function getEncryptor()
    {
        return $this->encryptor;
    }

    /**
     * Set the object used to encrypt outgoing packets.
     *
     *  \param EncryptionInterface $encryptor
     *      Outgoing packets' encryptor.
     *
     *  \retval Transport
     *      Return this transport layer.
     */
    public function setEncryptor(EncryptionInterface $encryptor)
    {
        $this->encryptor = $encryptor;
        return $this;
    }

    /**
     * Get the object used to decrypt incoming packets.
     *
     *  \retval EncryptionInterface
     *      Incoming packets' decryptor.
     */
    public function getDecryptor()
    {
        return $this->decryptor;
    }

    /**
     * Set the object used to decrypt incoming packets.
     *
     *  \param EncryptionInterface $decryptor
     *      Incoming packets' decryptor.
     *
     *  \retval Transport
     *      Return this transport layer.
     */
    public function setDecryptor(EncryptionInterface $decryptor)
    {
        $this->decryptor = $decryptor;
        return $this;
    }

    /**
     * Get the object used to check integrity of incoming packets.
     *
     *  \retval MACInterface
     *      Incoming packets' MAC checker.
     */
    public function getInputMAC()
    {
        return $this->inMAC;
    }

    /**
     * Set the object used to check integrity of incoming packets.
     *
     *  \param MACInterface $inputMAC
     *      Incoming packets' MAC checker.
     *
     *  \retval Transport
     *      Return this transport layer.
     */
    public function setInputMAC(MACInterface $inputMAC)
    {
        $this->inMAC = $inputMAC;
        return $this;
    }

    /**
     * Get the object used to check integrity of outgoing packets.
     *
     *  \retval MACInterface
     *      Outgoing packets' MAC generator.
     */
    public function getOutputMAC()
    {
        return $this->outMAC;
    }

    /**
     * Set the object used to generate MACs for outgoing packets.
     *
     *  \param MACInterface $outputMAC
     *      Outgoing packets' MAC generator.
     *
     *  \retval Transport
     *      Return this transport layer.
     */
    public function setOutputMAC(MACInterface $outputMAC)
    {
        $this->outMAC = $outputMAC;
        return $this;
    }

    /**
     * Get the factory used to create instances of the application layer.
     *
     *  \retval callable
     *      Factory for the application layer.
     */
    public function getApplicationFactory()
    {
        return $this->applicationFactory;
    }

    /**
     * Set the factory to use to create instances of the application layer.
     *
     *  \param $factory
     *      Factory for the application layer.
     *
     *  \retval Transport
     *      Returns this transport layer.
     */
    public function setApplicationFactory($factory)
    {
        $this->applicationFactory = $factory;
        return $this;
    }

    /**
     * Get the SSH banner displayed to clients.
     *
     *  \retval string
     *      SSH banner.
     *
     *  \retval null
     *      No SSH banner has been set.
     */
    public function getBanner()
    {
        return $this->banner;
    }

    /**
     * Set the SSH banner presented by the server.
     *
     *  \param string $message
     *      SSH banner to display during connection.
     *
     *  \retval Transport
     *      Returns this transport layer.
     */
    public function setBanner($message)
    {
        if (!is_string($message)) {
            throw new \InvalidArgumentException();
        }

        $this->banner = $message;
        return $this;
    }

    /**
     * Retrieve the current handler for a given message type.
     *
     *  \param int $type
     *      Message type.
     *
     *  \retval HandlerInterface
     *      Handler associated with the given message type.
     *
     *  \retval null
     *      There is no handler currently registered
     *      for the given message type.
     */
    public function getHandler($type)
    {
        if (!is_int($type) || $type < 0 || $type > 255) {
            throw new \InvalidArgumentException();
        }

        if (isset($this->handlers[$type])) {
            return $this->handlers[$type];
        }
        return null;
    }

    /**
     * Register a handler for a specific SSH message type.
     *
     *  \param int $type
     *      Message type.
     *
     *  \param HandlerInterface $handler
     *      Handler to register for that message type.
     *
     *  \retval Transport
     *      Returns this transport layer.
     *
     *  \note
     *      The given handler will overwrite any previously
     *      registered handler for that message type.
     */
    public function setHandler($type, \Clicky\Pssht\HandlerInterface $handler)
    {
        if (!is_int($type) || $type < 0 || $type > 255) {
            throw new \InvalidArgumentException();
        }

        $this->handlers[$type] = $handler;
        return $this;
    }

    /**
     * Unregister a handler for a specific SSH message type.
     *
     *  \param int $type
     *      Message type.
     *
     *  \param HandlerInterface $handler
     *      Handler to unregister for that message type.
     *
     *  \retval Transport
     *      Returns this transport layer.
     */
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

    /**
     * Write an SSH message into the output buffer.
     *
     *  \param MessageInterface $message
     *      Message to write into the output buffer.
     *
     *  \retval Transport
     *      Returns this transport layer.
     */
    public function writeMessage(\Clicky\Pssht\MessageInterface $message)
    {
        $logging = \Plop::getInstance();

        // Serialize the message.
        $encoder    = new Encoder();
        $encoder->encodeBytes(chr($message::getMessageId()));
        $message->serialize($encoder);
        $payload    = $encoder->getBuffer()->get(0);
        $logging->debug('Sending payload: %s', array(\escape($payload)));

        // Compress the payload if necessary.
        $payload    = $this->compressor->update($payload);
        $size       = strlen($payload);
        $blockSize  = max(8, $this->encryptor->getBlockSize());

        // Compute padding requirements.
        // See http://api.libssh.org/rfc/PROTOCOL
        // for more information on EtM (Encrypt-then-MAC).
        if ($this->outMAC instanceof \Clicky\Pssht\MAC\OpensshCom\EtM\EtMInterface) {
            $padSize    = $blockSize - ((1 + $size) % $blockSize);
        } else {
            $padSize    = $blockSize - ((1 + 4 + $size) % $blockSize);
        }
        if ($padSize < 4) {
            $padSize = ($padSize + $blockSize) % 256;
        }
        $padding = openssl_random_pseudo_bytes($padSize);

        // Create the packet. Every content passed to $encoder
        // will be encrypted, except possibly for the packet
        // length (see below).
        $encoder->encodeUint32(1 + $size + $padSize);
        if ($this->outMAC instanceof \Clicky\Pssht\MAC\OpensshCom\EtM\EtMInterface) {
            // Send the packet length in plaintext.
            $encSize = $encoder->getBuffer()->get(0);
            $this->encoder->encodeBytes($encSize);
        }
        $encoder->encodeBytes(chr($padSize));
        $encoder->encodeBytes($payload);
        $encoder->encodeBytes($padding);
        $packet     = $encoder->getBuffer()->get(0);
        $encrypted  = $this->encryptor->encrypt($packet);

        // Compute the MAC.
        if ($this->outMAC instanceof \Clicky\Pssht\MAC\OpensshCom\EtM\EtMInterface) {
            $mac = $this->outMAC->compute(pack('N', $this->outSeqNo) . $encSize . $encrypted);
        } else {
            $mac = $this->outMAC->compute(pack('N', $this->outSeqNo) . $packet);
        }

        // Send the packet on the wire.
        $this->encoder->encodeBytes($encrypted);
        $this->encoder->encodeBytes($mac);
        $this->outSeqNo = ++$this->outSeqNo & 0xFFFFFFFF;

        $logging->debug(
            'Sending %(type)s packet ' .
            '(size: %(size)d, payload: %(payload)d, ' .
            'block: %(block)d, padding: %(padding)d)',
            array(
                'type' => get_class($message),
                'size' => strlen($encrypted),
                'payload' => $size,
                'block' => $blockSize,
                'padding' => $padSize,
            )
        );
        return $this;
    }

    /**
     * Try to read and handle a single SSH message.
     *
     *  \retval bool
     *      \b true if a message was successfully read and handled,
     *      \b false otherwise.
     *
     *  \note
     *      Depending on the circumstances, messages may be successfully
     *      read but left unhandled (eg. because the message was incomplete).
     *      In such cases, the message will be reinjected and \b false
     *      returned, making it possible for a future call to this method
     *      to handle the (full) message again.
     */
    public function readMessage()
    {
        $logging = \Plop::getInstance();

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
        $firstRead  = $blockSize;

        // See http://api.libssh.org/rfc/PROTOCOL
        // for more information on EtM (Encrypt-then-MAC).
        if ($this->inMAC instanceof \Clicky\Pssht\MAC\OpensshCom\EtM\EtMInterface) {
            $firstRead  = 4;
            $encPayload = $this->decoder->getBuffer()->get(4);
            if ($encPayload === null) {
                return false;
            }
            $unencrypted = $encPayload;
        } else {
            $encPayload = $this->decoder->getBuffer()->get($blockSize);
            if ($encPayload === null) {
                return false;
            }
            $unencrypted = $this->decryptor->decrypt($encPayload);
        }
        $buffer         = new Buffer($unencrypted);
        $decoder        = new Decoder($buffer);
        $packetLength   = $decoder->decodeUint32();

        // Read the rest of the message.
        if ($this->inMAC instanceof \Clicky\Pssht\MAC\OpensshCom\EtM\EtMInterface) {
            $toRead = $packetLength;
        } else {
            $toRead         =
                // Remove what we already read.
                // Note: we must account for the "packet length" field
                // not being included in $packetLength itself.
                4 - $blockSize +

                // Rest of the encrypted data.
                $packetLength;
        }

        if ($toRead < 0) {
            throw new \RuntimeException();
        }

        $unencrypted2 = '';
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

            if ($this->inMAC instanceof \Clicky\Pssht\MAC\OpensshCom\EtM\EtMInterface) {
                // $encPayload actually contains packet length (in plaintext).
                $macData = $encPayload . $encPayload2;
            } else {
                $macData = $unencrypted . $unencrypted2;
            }

            $expectedMAC = $this->inMAC->compute(
                pack('N', $this->inSeqNo) .
                ((string) substr($macData, 0, $packetLength + 4))
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
        $logging->debug('Received payload: %s', array(\escape($payload)));

        $res = true;
        if (isset($this->handlers[$msgType])) {
            $handler = $this->handlers[$msgType];
            $logging->debug(
                'Calling %(handler)s with message type #%(msgType)d',
                array(
                    'handler' => get_class($handler) . '::handle',
                    'msgType' => $msgType,
                )
            );
            try {
                $res = $handler->handle($msgType, $decoder, $this, $this->context);
            } catch (\Clicky\Pssht\Messages\DISCONNECT $e) {
                if ($e->getCode() !== 0) {
                    $this->writeMessage($e);
                }
                throw $e;
            }
        } else {
            $logging->warn('Unimplemented message type (%d)', array($msgType));
            $response = new \Clicky\Pssht\Messages\UNIMPLEMENTED($this->inSeqNo);
            $this->writeMessage($response);
        }

        $this->inSeqNo = ++$this->inSeqNo & 0xFFFFFFFF;
        return $res;
    }
}
