<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Handlers;

/**
 * Handler for the SSH protocol's initial state.
 */
class InitialState implements \fpoirotte\Pssht\Handlers\HandlerInterface
{
    // Initial state
    public function handle(
        $msgType,
        \fpoirotte\Pssht\Wire\Decoder $decoder,
        \fpoirotte\Pssht\Transport $transport,
        array &$context
    ) {
        $ident = $decoder->getBuffer()->get("\r\n");
        if ($ident === null) {
            throw new \RuntimeException();
        }
        $context['identity']['client'] = (string) substr($ident, 0, -2);
        if (strncmp($ident, 'SSH-2.0-', 8) !== 0) {
            throw new \fpoirotte\Pssht\Messages\DISCONNECT();
        }

        $context['rekeying'] = 'server';
        return $this->handleKEXINIT($transport, $context);
    }

    public function handleKEXINIT(
        \fpoirotte\Pssht\Transport $transport,
        array &$context
    ) {
        $algos = \fpoirotte\Pssht\Algorithms::factory();

        // Cookie
        $random = new \fpoirotte\Pssht\Random\OpenSSL();

        // KEX
        $kexAlgos = $algos->getAlgorithms('KEX');
        if (!count($kexAlgos)) {
            throw new \RuntimeException();
        }

        // Server key
        $serverHostKeyAlgos = array_intersect(
            $algos->getAlgorithms('Key'),
            array_keys($context['serverKeys'])
        );
        if (!count($serverHostKeyAlgos)) {
            throw new \RuntimeException();
        }

        // Encryption
        $encAlgosC2S = array_diff(
            $algos->getAlgorithms('Encryption'),
            array('none')
        );
        $encAlgosS2C = $encAlgosC2S;
        if (!count($encAlgosC2S)) {
            throw new \RuntimeException();
        }

        // MAC
        $macAlgosC2S = array_diff($algos->getAlgorithms('MAC'), array('none'));
        $macAlgosS2C = $macAlgosC2S;
        if (!count($macAlgosC2S)) {
            throw new \RuntimeException();
        }

        // Compression
        $compAlgosC2S = $algos->getAlgorithms('Compression');
        $compAlgosS2C = $compAlgosC2S;
        if (!count($compAlgosC2S)) {
            throw new \RuntimeException();
        }

        $kex    = new \fpoirotte\Pssht\Messages\KEXINIT(
            $random,
            $kexAlgos,
            $serverHostKeyAlgos,
            $encAlgosC2S,
            $encAlgosS2C,
            $macAlgosC2S,
            $macAlgosS2C,
            $compAlgosC2S,
            $compAlgosS2C
        );
        $context['kex']['server'] = $kex;
        $transport->writeMessage($kex);

        return true;
    }
}
