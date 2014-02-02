<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Handlers;

class KEXINIT implements \Clicky\Pssht\HandlerInterface
{
    // SSH_MSG_KEXINIT = 20
    public function handle(
        $msgType,
        \Clicky\Pssht\Wire\Decoder $decoder,
        \Clicky\Pssht\Transport $transport,
        array &$context
    ) {
        $algos      = \Clicky\Pssht\Algorithms::factory();
        $kex        = \Clicky\Pssht\Messages\KEXINIT::unserialize($decoder);
        $context['kex']['client'] = $kex;

        // KEX method
        $context['kexAlgo'] = null;
        foreach ($kex->getKEXAlgos() as $algo) {
            if ($algos->getClass('KEX', $algo) !== null) {
                $context['kexAlgo'] = $algos->getClass('KEX', $algo);
                break;
            }
        }
        // No suitable KEX algorithm found.
        if (!isset($context['kexAlgo'])) {
            throw new \RuntimeException();
        }


        // C2S encryption
        $context['C2S']['Encryption'] = null;
        foreach ($kex->getC2SEncryptionAlgos() as $algo) {
            if ($algos->getClass('Encryption', $algo) !== null) {
                $context['C2S']['Encryption'] = $algos->getClass('Encryption', $algo);
                break;
            }
        }
        // No suitable C2S encryption cipher found.
        if (!isset($context['C2S']['Encryption'])) {
            throw new \RuntimeException();
        }

        // C2S compression
        $context['C2S']['Compression'] = null;
        foreach ($kex->getC2SCompressionAlgos() as $algo) {
            if ($algos->getClass('Compression', $algo) !== null) {
                $context['C2S']['Compression'] = $algos->getClass('Compression', $algo);
                break;
            }
        }
        // No suitable C2S compression found.
        if (!isset($context['C2S']['Compression'])) {
            throw new \RuntimeException();
        }

        // C2S MAC
        $context['C2S']['MAC'] = null;
        foreach ($kex->getC2SMACAlgos() as $algo) {
            if ($algos->getClass('MAC', $algo) !== null) {
                $context['C2S']['MAC'] = $algos->getClass('MAC', $algo);
                break;
            }
        }
        // No suitable C2S MAC found.
        if (!isset($context['C2S']['MAC'])) {
            throw new \RuntimeException();
        }

        // S2C encryption
        $context['S2C']['Encryption'] = null;
        foreach ($kex->getS2CEncryptionAlgos() as $algo) {
            if ($algos->getClass('Encryption', $algo) !== null) {
                $context['S2C']['Encryption'] = $algos->getClass('Encryption', $algo);
                break;
            }
        }
        // No suitable S2C encryption cipher found.
        if (!isset($context['S2C']['Encryption'])) {
            throw new \RuntimeException();
        }

        // S2C compression
        $context['S2C']['Compression'] = null;
        foreach ($kex->getS2CCompressionAlgos() as $algo) {
            if ($algos->getClass('Compression', $algo) !== null) {
                $context['S2C']['Compression'] = $algos->getClass('Compression', $algo);
                break;
            }
        }
        // No suitable S2C compression found.
        if (!isset($context['S2C']['Compression'])) {
            throw new \RuntimeException();
        }

        // S2C MAC
        $context['S2C']['MAC'] = null;
        foreach ($kex->getS2CMACAlgos() as $algo) {
            if ($algos->getClass('MAC', $algo) !== null) {
                $context['S2C']['MAC'] = $algos->getClass('MAC', $algo);
                break;
            }
        }
        // No suitable S2C MAC found.
        if (!isset($context['S2C']['MAC'])) {
            throw new \RuntimeException();
        }

        return true;
    }
}
