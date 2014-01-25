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
use Clicky\Pssht\Messages\DISCONNECT;

class Client
{
    protected $_authLayer;
    protected $_inSeqNo;
    protected $_outSeqNo;
    protected $_encoder;
    protected $_decoder;
    protected $_encryptor;
    protected $_decryptor;
    protected $_compressor;
    protected $_uncompressor;
    protected $_inMAC;
    protected $_outMAC;
    protected $_context;

    public function __construct(
        \Clicky\Pssht\Wire\Encoder  $encoder,
        \Clicky\Pssht\Wire\Decoder  $decoder
    )
    {
        $this->_authLayer       = NULL;
        $this->_inSeqNo         = 0;
        $this->_outSeqNo        = 0;
        $this->_encoder         = $encoder;
        $this->_decoder         = $decoder;
        $this->_compressor      = new \Clicky\Pssht\Compression\None();
        $this->_uncompressor    = new \Clicky\Pssht\Compression\None();
        $this->_encryptor       = new \Clicky\Pssht\Encryption\None();
        $this->_decryptor       = new \Clicky\Pssht\Encryption\None();
        $this->_inMAC           = new \Clicky\Pssht\MAC\None(NULL);
        $this->_outMAC          = new \Clicky\Pssht\MAC\None(NULL);
        $this->_context         = array();

        $ident = "SSH-2.0-Pssht_1.0.x_dev";
        $this->_context['identity']['server'] = $ident;
        $this->_encoder->encode_bytes($ident . "\r\n");
    }

    public function getEncoder()
    {
        return $this->_encoder;
    }

    public function getDecoder()
    {
        return $this->_decoder;
    }

    public function writeMessage(\Clicky\Pssht\MessageInterface $message)
    {
        $buffer     = new Buffer();
        $encoder    = new Encoder($buffer);

        // Serialize the message.
        $encoder->encode_bytes(chr($message::MESSAGE_ID));
        $message->serialize($encoder);
        $payload    = $buffer->get(0);

        // Compress the payload if necessary.
        $payload    = $this->_compressor->compress($payload);
        $size       = strlen($payload);

        // Compute padding size.
        /// @FIXME: retrieve cipher block size
        $cipherSize = max(8, 0);
        $padSize    = $cipherSize - ((1 + 4 + $size) % $cipherSize);
        if ($padSize < 4)
            $padSize = ($padSize + $cipherSize) % 256;
        $padding = str_repeat("\x00", $padSize);

        // Create the packet.
        $encoder->encode_uint32(1 + $size + $padSize);
        $encoder->encode_bytes(chr($padSize));
        $encoder->encode_bytes($payload);
        $encoder->encode_bytes($padding);
        $packet = $buffer->get(0);

        // Write the encrypted packet on the wire.
        $this->_encoder->encode_bytes($this->_encryptor->encrypt($packet));

        // Write the MAC if necessary.
        $mac = $this->_outMAC->compute(pack('N', $this->_outSeqNo) . $packet);
        $this->_outSeqNo++;
        $this->_outSeqNo &= 0xFFFFFFFF;
        $this->_encoder->encode_bytes($mac);
    }

    // Initial state
    protected function _handle_INIT(Decoder $decoder)
    {
        $ident = $decoder->getBuffer()->get("\r\n");
        if ($ident === NULL)
            throw new \RuntimeException();
        $this->_context['identity']['client'] = (string) substr($ident, 0, -2);

        $random = new \Clicky\Pssht\Random\OpenSSL();
        $kex    = new \Clicky\Pssht\Messages\KEXINIT($random);
        $this->_context['kex']['server'] = $kex;
        $this->writeMessage($kex);
        return TRUE;
    }

    // SSH_MSG_KEXINIT
    protected function _handle_20(Decoder $decoder)
    {
        $algos      = \Clicky\Pssht\Algorithms::factory();
        $kex        = \Clicky\Pssht\Messages\KEXINIT::unserialize($decoder);
        $this->_context['kex']['client'] = $kex;

        // KEX method
        $this->_context['kexAlgo'] = NULL;
        foreach ($kex->getKEXAlgos() as $algo) {
            if ($algos->getClass('KEX', $algo) !== NULL) {
                $this->_context['kexAlgo'] = $algos->getClass('KEX', $algo);
                break;
            }
        }
        // No suitable KEX algorithm found.
        if (!$this->_context['kexAlgo'])
            throw new \RuntimeException();


        // C2S encryption
        $this->_context['C2S']['Encryption'] = NULL;
        foreach ($kex->getC2SEncryptionAlgos() as $algo) {
            if ($algos->getClass('Encryption', $algo) !== NULL) {
                $this->_context['C2S']['Encryption'] = $algos->getClass('Encryption', $algo);
                break;
            }
        }
        // No suitable C2S encryption cipher found.
        if (!$this->_context['C2S']['Encryption'])
            throw new \RuntimeException();

        // C2S compression
        $this->_context['C2S']['Compression'] = NULL;
        foreach ($kex->getC2SCompressionAlgos() as $algo) {
            if ($algos->getClass('Compression', $algo) !== NULL) {
                $this->_context['C2S']['Compression'] = $algos->getClass('Compression', $algo);
                break;
            }
        }
        // No suitable C2S compression found.
        if (!$this->_context['C2S']['Compression'])
            throw new \RuntimeException();

        // C2S MAC
        $this->_context['C2S']['MAC'] = NULL;
        foreach ($kex->getC2SMACAlgos() as $algo) {
            if ($algos->getClass('MAC', $algo) !== NULL) {
                $this->_context['C2S']['MAC'] = $algos->getClass('MAC', $algo);
                break;
            }
        }
        // No suitable C2S MAC found.
        if (!$this->_context['C2S']['MAC'])
            throw new \RuntimeException();

        // S2C encryption
        $this->_context['S2C']['Encryption'] = NULL;
        foreach ($kex->getS2CEncryptionAlgos() as $algo) {
            if ($algos->getClass('Encryption', $algo) !== NULL) {
                $this->_context['S2C']['Encryption'] = $algos->getClass('Encryption', $algo);
                break;
            }
        }
        // No suitable S2C encryption cipher found.
        if (!$this->_context['S2C']['Encryption'])
            throw new \RuntimeException();

        // S2C compression
        $this->_context['S2C']['Compression'] = NULL;
        foreach ($kex->getS2CCompressionAlgos() as $algo) {
            if ($algos->getClass('Compression', $algo) !== NULL) {
                $this->_context['S2C']['Compression'] = $algos->getClass('Compression', $algo);
                break;
            }
        }
        // No suitable S2C compression found.
        if (!$this->_context['S2C']['Compression'])
            throw new \RuntimeException();

        // S2C MAC
        $this->_context['S2C']['MAC'] = NULL;
        foreach ($kex->getS2CMACAlgos() as $algo) {
            if ($algos->getClass('MAC', $algo) !== NULL) {
                $this->_context['S2C']['MAC'] = $algos->getClass('MAC', $algo);
                break;
            }
        }
        // No suitable S2C MAC found.
        if (!$this->_context['S2C']['MAC'])
            throw new \RuntimeException();

        return TRUE;
    }

    // SSH_MSG_KEXDH_INIT
    protected function _handle_30(Decoder $decoder)
    {
        $message    = \Clicky\Pssht\Messages\KEXDH_INIT::unserialize($decoder);
        $kexAlgo    = $this->_context['kexAlgo'];
        $kexAlgo    = new $kexAlgo();
        $response   = new \Clicky\Pssht\Messages\KEXDH_REPLY(
            $message,
            new \Clicky\Pssht\PublicKey\RSA(
                'file://' .
                dirname(__DIR__) .
                '/tests/data/rsa2048'
            ),
            $this->_encryptor,
            $this->_decryptor,
            $kexAlgo,
            $this->_context['kex']['server'],
            $this->_context['kex']['client'],
            $this->_context['identity']['server'],
            $this->_context['identity']['client']
        );
        $this->writeMessage($response);

        if (!isset($this->_context['sessionIdentifier']))
            $this->_context['sessionIdentifier'] = $response->getExchangeHash();
        $this->_context['DH'] = $response;
        return TRUE;
    }

    // SSH_MSG_NEWKEYS
    protected function _handle_21(Decoder $decoder)
    {
        $response = new \Clicky\Pssht\Messages\NEWKEYS();
        $this->writeMessage($response);

        // Reset the various keys.
        $kexAlgo    = $this->_context['kexAlgo'];
        $kexAlgo    = new $kexAlgo();
        $encoder    = new Encoder(new Buffer());
        $encoder->encode_mpint($this->_context['DH']->getSharedSecret());
        $sharedSecret   = $encoder->getBuffer()->get(0);
        $exchangeHash   = $this->_context['DH']->getExchangeHash();
        $sessionId      = $this->_context['sessionIdentifier'];
        $limiters       = array(
            'A' => array($this->_context['C2S']['Encryption'], 'getIVSize'),
            'B' => array($this->_context['S2C']['Encryption'], 'getIVSize'),
            'C' => array($this->_context['C2S']['Encryption'], 'getKeySize'),
            'D' => array($this->_context['S2C']['Encryption'], 'getKeySize'),
        );
        foreach (array('A', 'B', 'C', 'D', 'E', 'F') as $keyIndex) {
            $keys   = array($kexAlgo->hash($sharedSecret . $exchangeHash . $keyIndex . $sessionId));
            $limit  = isset($limiters[$keyIndex])
                    ? call_user_func($limiters[$keyIndex])
                    : 0;
            while (strlen(implode('', $keys)) < $limit) {
                $key    = $kexAlgo->hash($sharedSecret . $exchangeHash . implode('', $keys));
                $keys[] = $key;
            }
            $key = implode('', $keys);
            if ($limit !== 0)
                $key = substr($key, 0, $limit);
            $this->_context['keys'][$keyIndex] = $key;
        }

        /// @FIXME: Reset compression & encryption contexts.

        $cls = $this->_context['C2S']['Encryption'];
        $this->_decryptor = new $cls(
            $this->_context['keys']['A'],
            $this->_context['keys']['C']
        );

        $cls = $this->_context['S2C']['Encryption'];
        $this->_encryptor = new $cls(
            $this->_context['keys']['B'],
            $this->_context['keys']['D']
        );

        $cls            = $this->_context['C2S']['MAC'];
        $this->_inMAC   = new $cls($this->_context['keys']['E']);

        $cls            = $this->_context['S2C']['MAC'];
        $this->_outMAC  = new $cls($this->_context['keys']['F']);

        return TRUE;
    }

    // SSH_MSG_IGNORE
    public function _handle_2(Decoder $decoder)
    {
        return TRUE;
    }

    // SSH_MSG_DEBUG
    public function _handle_4(Decoder $decoder)
    {
        $message = \Clicky\Pssht\Messages\DEBUG::unserialize($decoder);
        if ($message->mustAlwaysDisplay())
            echo escape($message->getMessage()) . PHP_EOL;
        return TRUE;
    }

    // SSH_MSG_SERVICE_REQUEST
    public function _handle_5(Decoder $decoder)
    {
        $message    = \Clicky\Pssht\Messages\SERVICE_REQUEST::unserialize($decoder);
        $algos      = Algorithms::factory();
        $service    = $message->getServiceName();
        $cls        = $algos->getClass('Services', $service);
        if ($cls !== NULL) {
            $response = new \Clicky\Pssht\Messages\SERVICE_ACCEPT($message->getServiceName());
            $this->_authLayer = new $cls($this);
        }
        else {
            $response = new DISCONNECT(
                DISCONNECT::SSH_DISCONNECT_SERVICE_NOT_AVAILABLE,
                'No such service'
            );
        }
        $this->writeMessage($response);
        return TRUE;
    }

    public function readMessage()
    {
        if (!isset($this->_context['identity']['client'])) {
            return $this->_handle_INIT($this->_decoder);
        }

        /// @FIXME: retrieve cipher block size.
        $cipherSize = max(0, 4);

        $encPayload = $this->_decoder->getBuffer()->get($cipherSize);
        if ($encPayload === NULL || $encPayload === '') {
            return FALSE;
        }
        $unencrypted    = $this->_decryptor->decrypt($encPayload);
        $buffer         = new Buffer($unencrypted);
        $decoder        = new Decoder($buffer);
        $packetLength   = $decoder->decode_uint32();

        $macSize        = $this->_inMAC->getSize() / 8;
        $encPayload2    = $this->_decoder->getBuffer()->get($packetLength + $macSize);
        if ($encPayload2 === NULL || $encPayload2 === '') {
            $this->_decoder->getBuffer()->unget($encPayload);
            return FALSE;
        }
        $unencrypted2   = $this->_decryptor->decrypt($encPayload2);
        $buffer->push($unencrypted2);

        $paddingLength  = ord($decoder->decode_bytes());
        $payload        = $decoder->decode_bytes($packetLength - $paddingLength - 1);
        $padding        = $decoder->decode_bytes($paddingLength);
        $expectedMAC    = $this->_inMAC->compute(
            pack('N', $this->_inSeqNo) .
            ((string) substr($unencrypted . $unencrypted2, 0, $packetLength + 4))
        );

        // If a MAC is used.
        $actualMAC = '';
        if ($expectedMAC !== '') {
            $actualMAC = $decoder->decode_bytes(strlen($expectedMAC));
            if ($expectedMAC !== $actualMAC)
                throw new \RuntimeException();
        }

        if (!isset($packetLength, $paddingLength, $payload, $padding, $actualMAC)) {
            $this->_decoder->getBuffer()->unget($encPayload2);
            $this->_decoder->getBuffer()->unget($encPayload);
            echo "Something went wrong during decoding" . PHP_EOL;
            return FALSE;
        }

        $payload    = $this->_uncompressor->uncompress($payload);
        $decoder    = new Decoder(new Buffer($payload));
        $msgType    = ord($decoder->decode_bytes(1));
        $func       = '_handle_' . $msgType;
        $res        = TRUE;

        try {
            if (method_exists($this, $func)) {
                $res = call_user_func(array($this, $func), $decoder);
            }
            else if ($this->_authLayer !== NULL) {
                $res = $this->_authLayer->handleMessage(
                    $msgType,
                    $decoder,
                    count($this->_decoder->getBuffer())
                );
            }
            else
                throw new \RuntimeException();
        }
        catch (RuntimeException $e) {
            echo "No such handler: $func" . PHP_EOL;
            $response = new \Clicky\Pssht\Messages\UNIMPLEMENTED($this->_inSeqNo);
            $this->writeMessage($response);
        }

        $this->_inSeqNo++;
        $this->_inSeqNo &= 0xFFFFFFFF;
        return $res;
    }
}

