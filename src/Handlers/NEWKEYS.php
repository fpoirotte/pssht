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

use fpoirotte\Pssht\CompressionInterface;

/**
 * Handler for SSH_MSG_NEWKEYS messages.
 */
class NEWKEYS implements \fpoirotte\Pssht\HandlerInterface
{
    // SSH_MSG_NEWKEYS = 21
    public function handle(
        $msgType,
        \fpoirotte\Pssht\Wire\Decoder $decoder,
        \fpoirotte\Pssht\Transport $transport,
        array &$context
    ) {
        unset($context['rekeying']);
        $response = new \fpoirotte\Pssht\Messages\NEWKEYS();
        $transport->writeMessage($response);
        $logging = \Plop\Plop::getInstance();

        // Reset the various keys.
        $kexAlgo    = $context['kexAlgo'];
        $kexAlgo    = new $kexAlgo();
        $encoder    = new \fpoirotte\Pssht\Wire\Encoder();
        $encoder->encodeMpint($context['DH']->getSharedSecret());
        $sharedSecret   = $encoder->getBuffer()->get(0);
        $exchangeHash   = $context['DH']->getExchangeHash();
        $sessionId      = $context['sessionIdentifier'];
        $limiters       = array(
            'A' => array($context['C2S']['Encryption'], 'getIVSize'),
            'B' => array($context['S2C']['Encryption'], 'getIVSize'),
            'C' => array($context['C2S']['Encryption'], 'getKeySize'),
            'D' => array($context['S2C']['Encryption'], 'getKeySize'),
            'E' => array($context['C2S']['MAC'], 'getKeySize'),
            'F' => array($context['C2S']['MAC'], 'getKeySize'),
        );

        $shared = gmp_strval($context['DH']->getSharedSecret(), 16);
        $shared = str_pad($shared, ((strlen($shared) + 1) >> 1) << 1, '0', STR_PAD_LEFT);
        $logging->debug(
            'Shared secret: %s',
            array(wordwrap($shared, 16, ' ', true))
        );
        $logging->debug(
            'Hash: %s',
            array(wordwrap(bin2hex($exchangeHash), 16, ' ', true))
        );

        foreach (array('A', 'B', 'C', 'D', 'E', 'F') as $keyIndex) {
            $key    = $kexAlgo->hash($sharedSecret . $exchangeHash . $keyIndex . $sessionId);
            $limit  = call_user_func($limiters[$keyIndex]);
            while (strlen($key) < $limit) {
                $key .= $kexAlgo->hash($sharedSecret . $exchangeHash . $key);
            }
            $key = (string) substr($key, 0, $limit);
            $context['keys'][$keyIndex] = $key;
            $logging->debug(
                'Key %(keyName)s: %(keyValue)s',
                array(
                    'keyName' => $keyIndex,
                    'keyValue' => wordwrap(bin2hex($key), 16, ' ', true),
                )
            );
        }

        // Encryption
        $cls = $context['C2S']['Encryption'];
        $transport->setDecryptor(
            new $cls($context['keys']['A'], $context['keys']['C'])
        );
        $logging->debug('C2S Encryption: %s', array($cls));

        $cls = $context['S2C']['Encryption'];
        $transport->setEncryptor(
            new $cls($context['keys']['B'], $context['keys']['D'])
        );
        $logging->debug('S2C Encryption: %s', array($cls));

        // MAC
        $cls            = $context['C2S']['MAC'];
        $transport->setInputMAC(new $cls($context['keys']['E']));
        $logging->debug('C2S MAC: %s', array($cls));

        $cls            = $context['S2C']['MAC'];
        $transport->setOutputMAC(new $cls($context['keys']['F']));
        $logging->debug('S2C MAC: %s', array($cls));

        // Compression
        $cls                = $context['C2S']['Compression'];
        $transport->setUncompressor(
            new $cls(CompressionInterface::MODE_UNCOMPRESS)
        );
        $logging->debug('C2S Compression: %s', array($cls));

        $cls                = $context['S2C']['Compression'];
        $transport->setCompressor(
            new $cls(CompressionInterface::MODE_COMPRESS)
        );
        $logging->debug('S2C Compression: %s', array($cls));

        return true;
    }
}
