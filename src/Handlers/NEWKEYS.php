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

use Clicky\Pssht\CompressionInterface;

class NEWKEYS implements \Clicky\Pssht\HandlerInterface
{
    // SSH_MSG_NEWKEYS = 21
    public function handle(
        $msgType,
        \Clicky\Pssht\Wire\Decoder $decoder,
        \Clicky\Pssht\Transport $transport,
        array &$context
    ) {
        $response = new \Clicky\Pssht\Messages\NEWKEYS();
        $transport->writeMessage($response);

        // Reset the various keys.
        $kexAlgo    = $context['kexAlgo'];
        $kexAlgo    = new $kexAlgo();
        $encoder    = new \Clicky\Pssht\Wire\Encoder();
        $encoder->encodeMpint($context['DH']->getSharedSecret());
        $sharedSecret   = $encoder->getBuffer()->get(0);
        $exchangeHash   = $context['DH']->getExchangeHash();
        $sessionId      = $context['sessionIdentifier'];
        $limiters       = array(
            'A' => array($context['C2S']['Encryption'], 'getIVSize'),
            'B' => array($context['S2C']['Encryption'], 'getIVSize'),
            'C' => array($context['C2S']['Encryption'], 'getKeySize'),
            'D' => array($context['S2C']['Encryption'], 'getKeySize'),
            'E' => array($context['C2S']['MAC'], 'getSize'),
            'F' => array($context['C2S']['MAC'], 'getSize'),
        );
        foreach (array('A', 'B', 'C', 'D', 'E', 'F') as $keyIndex) {
            $key    = $kexAlgo->hash($sharedSecret . $exchangeHash . $keyIndex . $sessionId);
            $limit  = call_user_func($limiters[$keyIndex]);
            $keyReq = max(24, $limit);
            while (strlen($key) < $keyReq) {
                $key .= $kexAlgo->hash($sharedSecret . $exchangeHash . $key);
            }
            $key = (string) substr($key, 0, $limit);
            $context['keys'][$keyIndex] = $key;
        }

        // Encryption
        $cls = $context['C2S']['Encryption'];
        $transport->setDecryptor(
            new $cls($context['keys']['A'], $context['keys']['C'])
        );
        $cls = $context['S2C']['Encryption'];
        $transport->setEncryptor(
            new $cls($context['keys']['B'], $context['keys']['D'])
        );

        // MAC
        $cls            = $context['C2S']['MAC'];
        $transport->setInputMAC(new $cls($context['keys']['E']));
        $cls            = $context['S2C']['MAC'];
        $transport->setOutputMAC(new $cls($context['keys']['F']));

        // Compression
        $cls                = $context['C2S']['Compression'];
        $transport->setUncompressor(
            new $cls(CompressionInterface::MODE_UNCOMPRESS)
        );
        $cls                = $context['S2C']['Compression'];
        $transport->setCompressor(
            new $cls(CompressionInterface::MODE_COMPRESS)
        );

        return true;
    }
}
