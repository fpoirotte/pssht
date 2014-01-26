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

class       KEXINIT
implements  MessageInterface
{
    protected $_cookie;
    protected $_kexAlgos;
    protected $_serverHostKeyAlgos;
    protected $_encAlgosC2S;
    protected $_encAlgosS2C;
    protected $_MACAlgosC2S;
    protected $_MACAlgosS2C;
    protected $_compAlgosC2S;
    protected $_compAlgosS2C;
    protected $_langC2S;
    protected $_langS2C;
    protected $_firstKexPacket;

    public function __construct(
        RandomInterface $random,
        array           $kexAlgos           = NULL,
        array           $serverHostKeyAlgos = NULL,
        array           $encAlgosC2S        = NULL,
        array           $encAlgosS2C        = NULL,
        array           $macAlgosC2S        = NULL,
        array           $macAlgosS2C        = NULL,
        array           $compAlgosC2S       = NULL,
        array           $compAlgosS2C       = NULL,
        array           $langC2S            = array(),
        array           $langS2C            = array(),
                        $firstKexPacket     = FALSE
    )
    {
        if (!is_bool($firstKexPacket))
            throw new \InvalidArgumentException();

        $algos = Algorithms::factory();

        if ($kexAlgos === NULL) {
            $kexAlgos = $algos->getAlgorithms('KEX');
            usort($kexAlgos, array('self', 'sortAlgorithms'));
        }

        if ($serverHostKeyAlgos === NULL) {
            $serverHostKeyAlgos = $algos->getAlgorithms('PublicKey');
            usort($serverHostKeyAlgos, array('self', 'sortAlgorithms'));
        }

        $encAlgos = $algos->getClasses('Encryption');
        unset($encAlgos['none']);
        $encAlgos = array_keys($encAlgos);
        usort($encAlgos, array('self', 'sortAlgorithms'));
        if ($encAlgosC2S === NULL)
            $encAlgosC2S = $encAlgos;
        if ($encAlgosS2C === NULL)
            $encAlgosS2C = $encAlgos;

        $macAlgos = $algos->getClasses('MAC');
        unset($macAlgos['none']);
        $macAlgos = array_keys($macAlgos);
        usort($macAlgos, array('self', 'sortAlgorithms'));
        if ($macAlgosC2S === NULL)
            $macAlgosC2S = $macAlgos;
        if ($macAlgosS2C === NULL)
            $macAlgosS2C = $macAlgos;

        $compAlgos = $algos->getAlgorithms('Compression');
        usort($compAlgos, array('self', 'sortAlgorithms'));
        if ($compAlgosC2S === NULL)
            $compAlgosC2S = $compAlgos;
        if ($compAlgosS2C === NULL)
            $compAlgosS2C = $compAlgos;

        $this->_cookie              = $random->getBytes(16);
        $this->_kexAlgos            = $kexAlgos;
        $this->_serverHostKeyAlgos  = $serverHostKeyAlgos;
        $this->_encAlgosC2S         = $encAlgosC2S;
        $this->_encAlgosS2C         = $encAlgosS2C;
        $this->_macAlgosC2S         = $macAlgosC2S;
        $this->_macAlgosS2C         = $macAlgosS2C;
        $this->_compAlgosC2S        = $compAlgosC2S;
        $this->_compAlgosS2C        = $compAlgosS2C;
        $this->_langC2S             = $langC2S;
        $this->_langS2C             = $langS2C;
        $this->_firstKexPacket      = $firstKexPacket;
    }

    static public function getMessageId()
    {
        return 20;
    }

    public function serialize(Encoder $encoder)
    {
        $encoder->encode_bytes($this->_cookie);
        $encoder->encode_name_list($this->_kexAlgos);
        $encoder->encode_name_list($this->_serverHostKeyAlgos);
        $encoder->encode_name_list($this->_encAlgosC2S);
        $encoder->encode_name_list($this->_encAlgosS2C);
        $encoder->encode_name_list($this->_macAlgosC2S);
        $encoder->encode_name_list($this->_macAlgosS2C);
        $encoder->encode_name_list($this->_compAlgosC2S);
        $encoder->encode_name_list($this->_compAlgosS2C);
        $encoder->encode_name_list($this->_langC2S);
        $encoder->encode_name_list($this->_langS2C);
        $encoder->encode_boolean($this->_firstKexPacket);
        $encoder->encode_uint32(0); // Reserved for future extension.
    }

    static public function unserialize(Decoder $decoder)
    {
        $res = new self(
            // cookie
            new \Clicky\Pssht\Random\Fixed($decoder->decode_bytes(16)),
            $decoder->decode_name_list(),   // keyAlgos
            $decoder->decode_name_list(),   // serverHostKeyAlgos
            $decoder->decode_name_list(),   // encAlgosC2S
            $decoder->decode_name_list(),   // encAlgosS2C
            $decoder->decode_name_list(),   // macAlgosC2S
            $decoder->decode_name_list(),   // macAlgosS2C
            $decoder->decode_name_list(),   // compAlgosC2S
            $decoder->decode_name_list(),   // compAlgosS2C
            $decoder->decode_name_list(),   // langC2S
            $decoder->decode_name_list(),   // langS2C
            $decoder->decode_boolean()      // firstKexPacket
        );
        $decoder->decode_uint32(); // Reserved for future extension.
        return $res;
    }

    public function getKEXAlgos()
    {
        return $this->_kexAlgos;
    }

    public function getServerHostKeyAlgos()
    {
        return $this->_serverHostKeyAlgos;
    }

    public function getC2SEncryptionAlgos()
    {
        return $this->_encAlgosC2S;
    }

    public function getC2SMACAlgos()
    {
        return $this->_macAlgosC2S;
    }

    public function getC2SCompressionAlgos()
    {
        return $this->_compAlgosC2S;
    }

    public function getS2CEncryptionAlgos()
    {
        return $this->_encAlgosS2C;
    }

    public function getS2CMACAlgos()
    {
        return $this->_macAlgosS2C;
    }

    public function getS2CCompressionAlgos()
    {
        return $this->_compAlgosS2C;
    }

    static public function sortAlgorithms($a, $b)
    {
        static $preferences = array(
            // KEX
            'ecdh-sha2-nistp256',
            'ecdh-sha2-nistp384',
            'ecdh-sha2-nistp521',
            'diffie-hellman-group-exchange-sha256',
            'diffie-hellman-group-exchange-sha1',
            'diffie-hellman-group14-sha1',
            'diffie-hellman-group1-sha1',

            // PublicKey
            'ssh-rsa-cert-v01@openssh.com',
            'ssh-rsa-cert-v00@openssh.com',
            'ssh-rsa',
            'ecdsa-sha2-nistp256-cert-v01@openssh.com',
            'ecdsa-sha2-nistp384-cert-v01@openssh.com',
            'ecdsa-sha2-nistp521-cert-v01@openssh.com',
            'ssh-dss-cert-v01@openssh.com',
            'ssh-dss-cert-v00@openssh.com',
            'ecdsa-sha2-nistp256',
            'ecdsa-sha2-nistp384',
            'ecdsa-sha2-nistp521',
            'ssh-dss',

            // Encryption
            'aes128-ctr',
            'aes192-ctr',
            'aes256-ctr',
            'arcfour256',
            'arcfour128',
            'aes128-gcm@openssh.com',
            'aes256-gcm@openssh.com',
            'aes128-cbc',
            '3des-cbc',
            'blowfish-cbc',
            'cast128-cbc',
            'aes192-cbc',
            'aes256-cbc',
            'arcfour',
            'rijndael-cbc@lysator.liu.se',

            // MAC
            'hmac-md5-etm@openssh.com',
            'hmac-sha1-etm@openssh.com',
            'umac-64-etm@openssh.com',
            'umac-128-etm@openssh.com',
            'hmac-sha2-256-etm@openssh.com',
            'hmac-sha2-512-etm@openssh.com',
            'hmac-ripemd160-etm@openssh.com',
            'hmac-sha1-96-etm@openssh.com',
            'hmac-md5-96-etm@openssh.com',
            'hmac-md5',
            'hmac-sha1',
            'umac-64@openssh.com',
            'umac-128@openssh.com',
            'hmac-sha2-256',
            'hmac-sha2-512',
            'hmac-ripemd160',
            'hmac-ripemd160@openssh.com',
            'hmac-sha1-96',
            'hmac-md5-96',

            // Compression
            'none',
            'zlib@openssh.com',
            'zlib',
        );

        $iA = array_search($a, $preferences, TRUE);
        $iB = array_search($b, $preferences, TRUE);
        if ($iA === FALSE)
            return ($iB === FALSE ? 0 : 1);
        if ($iB === FALSE)
            return -1;
        return ($iA - $iB);
    }
}

